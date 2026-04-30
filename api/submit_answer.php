<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
header('Content-Type: application/json');

function response(array $data): void
{
    echo json_encode($data);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$scenarioId = (int) ($payload['scenario_id'] ?? 0);
$userAnswer = trim((string) ($payload['answer'] ?? ''));
$userId = (int) $_SESSION['user_id'];

if ($scenarioId <= 0 || $userAnswer === '') {
    response(['success' => false, 'message' => 'Missing answer.']);
}

$pdo = db();
$stmt = $pdo->prepare(
    'SELECT s.*, gm.mode_key, gm.mode_name
     FROM scenarios s
     INNER JOIN game_modes gm ON gm.mode_id = s.mode_id
     WHERE s.scenario_id = ? LIMIT 1'
);
$stmt->execute([$scenarioId]);
$scenario = $stmt->fetch();

if (!$scenario) {
    response(['success' => false, 'message' => 'Scenario not found.']);
}

$correct = false;
if ($scenario['mode_key'] === 'tile_selection') {
    $correct = strtolower(trim($userAnswer)) === strtolower(trim($scenario['answer']));
} else {
    $correct = normalize_answer($userAnswer) === normalize_answer($scenario['answer']);
}

$existingStmt = $pdo->prepare('SELECT * FROM user_progress WHERE user_id = ? AND scenario_id = ? LIMIT 1');
$existingStmt->execute([$userId, $scenarioId]);
$existing = $existingStmt->fetch();
$scoreAwarded = 0;

if ($existing) {
    $attempts = (int) $existing['attempts'] + 1;
    if ($correct && (int) $existing['is_correct'] === 0) {
        $scoreAwarded = POINTS_PER_CORRECT;
        $update = $pdo->prepare(
            'UPDATE user_progress
             SET is_correct = 1, attempts = ?, score_awarded = ?, completed_at = NOW()
             WHERE progress_id = ?'
        );
        $update->execute([$attempts, $scoreAwarded, $existing['progress_id']]);
    } else {
        $update = $pdo->prepare('UPDATE user_progress SET attempts = ? WHERE progress_id = ?');
        $update->execute([$attempts, $existing['progress_id']]);
    }
} else {
    $scoreAwarded = $correct ? POINTS_PER_CORRECT : 0;
    $insert = $pdo->prepare(
        'INSERT INTO user_progress (user_id, scenario_id, mode_id, is_correct, attempts, score_awarded, completed_at)
         VALUES (?, ?, ?, ?, 1, ?, ?)'
    );
    $insert->execute([
        $userId,
        $scenarioId,
        $scenario['mode_id'],
        $correct ? 1 : 0,
        $scoreAwarded,
        $correct ? date('Y-m-d H:i:s') : null,
    ]);
}

$newRewards = $correct ? award_rewards($userId) : [];

response([
    'success' => true,
    'correct' => $correct,
    'correct_answer' => $scenario['answer'],
    'learning_tip' => $scenario['learning_tip'],
    'score_awarded' => $scoreAwarded,
    'total_score' => get_user_total_score($userId),
    'new_rewards' => $newRewards,
]);
