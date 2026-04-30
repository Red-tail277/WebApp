<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$stats = get_mode_stats((int) $user['user_id']);
$totalScore = get_user_total_score((int) $user['user_id']);
$totalCompleted = get_completed_count((int) $user['user_id']);
$totalScenarios = 150;
$rewards = get_user_rewards((int) $user['user_id']);
$leaderboard = get_leaderboard();

$modeMeta = [
    'one_word' => ['icon' => '1W', 'url' => BASE_URL . '/one_word.php', 'label' => 'Play 4 Clues 1 Word'],
    'tile_selection' => ['icon' => '4T', 'url' => BASE_URL . '/tile_selection.php', 'label' => 'Play Tile Selection'],
    'crossword' => ['icon' => 'CW', 'url' => BASE_URL . '/crossword.php', 'label' => 'Play Crossword'],
];

$pageTitle = 'Dashboard | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<section class="app-card">
  <div class="hero">
    <h1>Welcome, <?= e($user['full_name']) ?></h1>
    <p>Choose a game mode and practice safe digital decisions through scenario-based learning.</p>
  </div>
  <div class="card-body">
    <section class="dashboard-stats">
      <div class="stat-card"><strong><?= $totalScore ?></strong><span>Total Score</span></div>
      <div class="stat-card"><strong><?= $totalCompleted ?></strong><span>Completed Scenarios</span></div>
      <div class="stat-card"><strong><?= count($rewards) ?></strong><span>Badges Earned</span></div>
      <div class="stat-card"><strong><?= $totalScenarios ?></strong><span>Total Scenarios</span></div>
    </section>

    <section class="grid grid-3">
      <?php foreach ($stats as $mode):
          $key = $mode['mode_key'];
          $completed = (int) $mode['completed'];
          $total = (int) $mode['total_scenarios'];
          $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
          $meta = $modeMeta[$key];
      ?>
        <article class="mode-card">
          <div class="mode-icon"><?= e($meta['icon']) ?></div>
          <h3><?= e($mode['mode_name']) ?></h3>
          <p>
            <?= $key === 'one_word' ? 'Guess one digital word using four everyday technology clues.' : '' ?>
            <?= $key === 'tile_selection' ? 'Read the digital problem and choose the correct solution from four large tiles.' : '' ?>
            <?= $key === 'crossword' ? 'Use the clue and letter bank to complete a mini crossword-style answer.' : '' ?>
          </p>
          <div class="progress-wrap">
            <div class="progress-label"><span><?= $completed ?> of <?= $total ?> done</span><span><?= $percent ?>%</span></div>
            <div class="progress-bar"><div class="progress-fill" style="width: <?= $percent ?>%"></div></div>
          </div>
          <a class="btn btn-primary" href="<?= e($meta['url']) ?>"><?= e($meta['label']) ?></a>
        </article>
      <?php endforeach; ?>
    </section>

    <section class="grid grid-3" style="margin-top: 18px;">
      <div class="profile-box" style="grid-column: span 2;">
        <h2>Leaderboard</h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Player</th><th>Score</th><th>Completed</th></tr></thead>
            <tbody>
              <?php foreach ($leaderboard as $row): ?>
                <tr><td><?= e($row['full_name']) ?></td><td><?= (int) $row['total_score'] ?></td><td><?= (int) $row['completed'] ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="profile-box">
        <h2>Latest Rewards</h2>
        <div class="reward-list">
          <?php if (!$rewards): ?>
            <p>Complete scenarios to earn your first badge.</p>
          <?php else: ?>
            <?php foreach (array_slice($rewards, 0, 3) as $reward): ?>
              <div class="reward-card">
                <div class="reward-icon"><?= e($reward['icon']) ?></div>
                <div><strong><?= e($reward['badge_name']) ?></strong><br><small><?= e($reward['description']) ?></small></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
