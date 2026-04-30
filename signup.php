<?php
require_once __DIR__ . '/includes/auth.php';
redirect_if_logged_in();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please complete all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO users (full_name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $success = 'Account created. You can now log in.';
        } catch (PDOException $ex) {
            $error = 'Email is already registered.';
        }
    }
}

$pageTitle = 'Sign Up | ' . APP_NAME;
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
      <h1>Create Account</h1>
      <p>Start learning safe digital actions through Ka-Hulaan games.</p>
    </div>
    <div class="card-body">
      <?php if ($error): ?><div class="notice notice-error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="notice notice-success"><?= e($success) ?></div><?php endif; ?>
      <form class="form" method="post">
        <label>Full Name
          <input type="text" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>" required />
        </label>
        <label>Email
          <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required />
        </label>
        <label>Password
          <input type="password" name="password" minlength="6" required />
        </label>
        <label>Confirm Password
          <input type="password" name="confirm_password" minlength="6" required />
        </label>
        <button class="btn btn-primary" type="submit">Sign Up</button>
        <p>Already have an account? <a class="muted-link" href="<?= BASE_URL ?>/login.php">Login here</a></p>
      </form>
    </div>
  </section>
</body>
</html>
