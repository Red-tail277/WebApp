<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$pageTitle = '4 Clues 1 Word | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<section class="game-shell" data-game-mode="one_word">
  <header class="game-topbar">
    <div>
      <h1>4 Clues 1 Word</h1>
      <p>Study the four clues and guess the one digital word.</p>
    </div>
    <aside class="score-card"><strong id="scoreDisplay">0</strong><span>Total Score</span></aside>
  </header>
  <section class="game-content" id="gameRoot"></section>
</section>
<script src="<?= BASE_URL ?>/assets/js/game.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
