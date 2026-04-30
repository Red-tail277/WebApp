<?php
require_once __DIR__ . '/../config/config.php';

function get_user_total_score(int $userId): int
{
    $stmt = db()->prepare('SELECT COALESCE(SUM(score_awarded), 0) AS total_score FROM user_progress WHERE user_id = ?');
    $stmt->execute([$userId]);
    return (int) $stmt->fetch()['total_score'];
}

function get_completed_count(int $userId): int
{
    $stmt = db()->prepare('SELECT COUNT(*) AS total FROM user_progress WHERE user_id = ? AND is_correct = 1');
    $stmt->execute([$userId]);
    return (int) $stmt->fetch()['total'];
}

function get_mode_stats(int $userId): array
{
    $stmt = db()->prepare(
        'SELECT gm.mode_key, gm.mode_name, COUNT(s.scenario_id) AS total_scenarios,
            SUM(CASE WHEN up.is_correct = 1 THEN 1 ELSE 0 END) AS completed,
            COALESCE(SUM(up.score_awarded), 0) AS score
         FROM game_modes gm
         LEFT JOIN scenarios s ON s.mode_id = gm.mode_id
         LEFT JOIN user_progress up ON up.scenario_id = s.scenario_id AND up.user_id = ?
         GROUP BY gm.mode_id, gm.mode_key, gm.mode_name
         ORDER BY gm.mode_id'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function award_rewards(int $userId): array
{
    $pdo = db();
    $awarded = [];
    $totalCompleted = get_completed_count($userId);
    $totalScore = get_user_total_score($userId);

    $modeCompletedStmt = $pdo->prepare(
        'SELECT gm.mode_key, COUNT(*) AS completed
         FROM user_progress up
         INNER JOIN game_modes gm ON gm.mode_id = up.mode_id
         WHERE up.user_id = ? AND up.is_correct = 1
         GROUP BY gm.mode_key'
    );
    $modeCompletedStmt->execute([$userId]);
    $modeCompleted = [];
    foreach ($modeCompletedStmt->fetchAll() as $row) {
        $modeCompleted[$row['mode_key']] = (int) $row['completed'];
    }

    $rewards = $pdo->query('SELECT * FROM rewards ORDER BY reward_id')->fetchAll();
    foreach ($rewards as $reward) {
        $type = $reward['requirement_type'];
        $value = (int) $reward['requirement_value'];
        $qualified = false;

        if ($type === 'completed_total' && $totalCompleted >= $value) {
            $qualified = true;
        }
        if ($type === 'score_total' && $totalScore >= $value) {
            $qualified = true;
        }
        if (strpos($type, 'mode_') === 0) {
            $modeKey = substr($type, 5);
            $qualified = ($modeCompleted[$modeKey] ?? 0) >= $value;
        }

        if ($qualified) {
            $insert = $pdo->prepare(
                'INSERT IGNORE INTO user_rewards (user_id, reward_id, awarded_at) VALUES (?, ?, NOW())'
            );
            $insert->execute([$userId, $reward['reward_id']]);
            if ($insert->rowCount() > 0) {
                $awarded[] = $reward['badge_name'];
            }
        }
    }

    return $awarded;
}

function get_user_rewards(int $userId): array
{
    $stmt = db()->prepare(
        'SELECT r.badge_name, r.description, r.icon, ur.awarded_at
         FROM user_rewards ur
         INNER JOIN rewards r ON r.reward_id = ur.reward_id
         WHERE ur.user_id = ?
         ORDER BY ur.awarded_at DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function get_leaderboard(int $limit = 10): array
{
    $stmt = db()->prepare(
        'SELECT u.full_name, COALESCE(SUM(up.score_awarded), 0) AS total_score,
            SUM(CASE WHEN up.is_correct = 1 THEN 1 ELSE 0 END) AS completed
         FROM users u
         LEFT JOIN user_progress up ON up.user_id = u.user_id
         GROUP BY u.user_id, u.full_name
         ORDER BY total_score DESC, completed DESC, u.full_name ASC
         LIMIT ?'
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
