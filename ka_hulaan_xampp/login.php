<?php
require_once __DIR__ . '/includes/auth.php';
redirect_if_logged_in();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['user_id'];
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Login | ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css" />
</head>
<body class="auth-body">
  <section class="auth-card">
    <div class="hero">
      <h1>Ka-Hulaan</h1>
      <p>Login and continue your digital confidence journey.</p>
    </div>
    <div class="card-body">
      <?php if ($error): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?>
      <form class="form" method="post">
        <label>Email
          <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required />
        </label>
        <label>Password
          <input type="password" name="password" required />
        </label>
        <button class="btn btn-primary" type="submit">Login</button>
        <p>No account yet? <a class="muted-link" href="<?= BASE_URL ?>/signup.php">Create one</a></p>
      </form>
    </div>
  </section>
</body>
</html>
