<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = db();
    $pdo->prepare('DELETE FROM user_rewards WHERE user_id = ?')->execute([$userId]);
    $pdo->prepare('DELETE FROM user_progress WHERE user_id = ?')->execute([$userId]);
    header('Location: ' . BASE_URL . '/profile.php');
    exit;
}

$pageTitle = 'Reset Progress | ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<section class="app-card">
  <div class="hero">
    <h1>Reset Progress</h1>
    <p>This will remove your score, completed scenarios, attempts, and rewards.</p>
  </div>
  <div class="card-body">
    <div class="notice notice-error">This action cannot be undone.</div>
    <form method="post" class="btn-row">
      <button class="btn btn-red" type="submit">Yes, Reset My Progress</button>
      <a class="btn btn-gray" href="<?= BASE_URL ?>/profile.php">Cancel</a>
    </form>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
