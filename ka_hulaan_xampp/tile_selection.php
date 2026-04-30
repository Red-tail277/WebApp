<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$pageTitle = 'Tile Selection | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<section class="game-shell" data-game-mode="tile_selection">
  <header class="game-topbar">
    <div>
      <h1>Tile Selection</h1>
      <p>Read the scenario and choose the correct solution from four tiles.</p>
    </div>
    <aside class="score-card"><strong id="scoreDisplay">0</strong><span>Total Score</span></aside>
  </header>
  <section class="game-content" id="gameRoot"></section>
</section>
<script src="<?= BASE_URL ?>/assets/js/game.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
