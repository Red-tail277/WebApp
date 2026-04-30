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

$modeKey = $_GET['mode'] ?? '';
$pdo = db();
$userId = (int) $_SESSION['user_id'];

$modeStmt = $pdo->prepare('SELECT * FROM game_modes WHERE mode_key = ? LIMIT 1');
$modeStmt->execute([$modeKey]);
$mode = $modeStmt->fetch();

if (!$mode) {
    response(['success' => false, 'message' => 'Invalid game mode.']);
}

$stmt = $pdo->prepare(
    'SELECT s.*, gm.mode_name
     FROM scenarios s
     INNER JOIN game_modes gm ON gm.mode_id = s.mode_id
     LEFT JOIN user_progress up ON up.scenario_id = s.scenario_id AND up.user_id = ? AND up.is_correct = 1
     WHERE s.mode_id = ? AND up.progress_id IS NULL
     ORDER BY s.scenario_id ASC
     LIMIT 1'
);
$stmt->execute([$userId, $mode['mode_id']]);
$scenario = $stmt->fetch();

if (!$scenario) {
    response([
        'success' => false,
        'message' => 'You completed all 50 scenarios in this game mode. View your profile or choose another mode.'
    ]);
}

$clueStmt = $pdo->prepare('SELECT clue_order, clue_title, clue_text FROM scenario_clues WHERE scenario_id = ? ORDER BY clue_order');
$clueStmt->execute([$scenario['scenario_id']]);
$clues = $clueStmt->fetchAll();

$options = [];
if ($modeKey === 'tile_selection') {
    $optionStmt = $pdo->prepare('SELECT option_text FROM tile_options WHERE scenario_id = ? ORDER BY option_id');
    $optionStmt->execute([$scenario['scenario_id']]);
    $options = array_column($optionStmt->fetchAll(), 'option_text');
    shuffle($options);
}

$answerNormalized = normalize_answer($scenario['answer']);
$letters = [];
if ($modeKey !== 'tile_selection') {
    $extra = str_split('TECHSAFEONLINEWIFIAPPDATA');
    shuffle($extra);
    $letters = array_merge(str_split($answerNormalized), array_slice($extra, 0, max(5, 12 - strlen($answerNormalized))));
    shuffle($letters);
}

$totalStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM scenarios WHERE mode_id = ?');
$totalStmt->execute([$mode['mode_id']]);
$total = (int) $totalStmt->fetch()['total'];

$completedStmt = $pdo->prepare(
    'SELECT COUNT(*) AS completed FROM user_progress WHERE user_id = ? AND mode_id = ? AND is_correct = 1'
);
$completedStmt->execute([$userId, $mode['mode_id']]);
$completed = (int) $completedStmt->fetch()['completed'];

$scenario['clues'] = $clues;
$scenario['options'] = $options;
$scenario['answer_length'] = strlen($answerNormalized);
$scenario['letter_bank'] = $letters;
$scenario['progress'] = [
    'completed' => $completed,
    'total' => $total,
    'percent' => $total > 0 ? round(($completed / $total) * 100) : 0,
];

unset($scenario['answer']);

response([
    'success' => true,
    'scenario' => $scenario,
    'progress' => $scenario['progress'],
    'total_score' => get_user_total_score($userId),
]);
