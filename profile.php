<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$stats = get_mode_stats((int) $user['user_id']);
$rewards = get_user_rewards((int) $user['user_id']);
$totalScore = get_user_total_score((int) $user['user_id']);
$totalCompleted = get_completed_count((int) $user['user_id']);

$pageTitle = 'Profile | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<section class="app-card">
  <div class="hero">
    <h1>Your Learning Profile</h1>
    <p>View your score, completed scenarios, game-mode progress, and earned badges.</p>
  </div>
  <div class="card-body">
    <section class="profile-grid">
      <aside class="profile-box">
        <h2><?= e($user['full_name']) ?></h2>
        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
        <p><strong>Joined:</strong> <?= e(date('F j, Y', strtotime($user['created_at']))) ?></p>
        <div class="btn-row" style="margin-bottom: 16px;"><a class="btn btn-red" href="<?= BASE_URL ?>/reset_progress.php">Reset Progress</a></div>
        <div class="dashboard-stats" style="grid-template-columns: 1fr;">
          <div class="stat-card"><strong><?= $totalScore ?></strong><span>Total Score</span></div>
          <div class="stat-card"><strong><?= $totalCompleted ?></strong><span>Completed Scenarios</span></div>
          <div class="stat-card"><strong><?= count($rewards) ?></strong><span>Badges</span></div>
        </div>
      </aside>

      <section class="profile-box">
        <h2>Progress per Game Mode</h2>
        <div class="grid">
          <?php foreach ($stats as $mode):
            $completed = (int) $mode['completed'];
            $total = (int) $mode['total_scenarios'];
            $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
          ?>
            <div>
              <div class="progress-label"><span><?= e($mode['mode_name']) ?> — <?= $completed ?> / <?= $total ?></span><span><?= $percent ?>%</span></div>
              <div class="progress-bar"><div class="progress-fill" style="width: <?= $percent ?>%"></div></div>
            </div>
          <?php endforeach; ?>
        </div>

        <h2 style="margin-top: 28px;">Reward System</h2>
        <div class="reward-list">
          <?php if (!$rewards): ?>
            <p>No badges yet. Complete your first correct scenario to unlock one.</p>
          <?php else: ?>
            <?php foreach ($rewards as $reward): ?>
              <div class="reward-card">
                <div class="reward-icon"><?= e($reward['icon']) ?></div>
                <div>
                  <strong><?= e($reward['badge_name']) ?></strong><br>
                  <small><?= e($reward['description']) ?></small><br>
                  <small>Earned: <?= e(date('F j, Y', strtotime($reward['awarded_at']))) ?></small>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>
    </section>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
