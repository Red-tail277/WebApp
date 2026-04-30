<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$pageTitle = 'Crossword | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<section class="game-shell" data-game-mode="crossword">
  <header class="game-topbar">
    <div>
      <h1>Mini Crossword</h1>
      <p>Use the clue and letter bank to complete the crossword-style digital word.</p>
    </div>
    <aside class="score-card"><strong id="scoreDisplay">0</strong><span>Total Score</span></aside>
  </header>
  <section class="game-content" id="gameRoot"></section>
</section>
<script src="<?= BASE_URL ?>/assets/js/game.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
