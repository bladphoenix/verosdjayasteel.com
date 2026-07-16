<?php
// admin/login.php
require_once __DIR__ . '/includes/session_boot.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../inc/functions.php';

if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$settings = get_settings($pdo);
$company  = $settings['company_name'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password harus diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin — <?php echo e($company); ?></title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%230d2137'/><text x='50' y='70' font-family='Arial' font-weight='900' font-size='54' fill='%23f59e0b' text-anchor='middle'>V</text></svg>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css?v=6">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="logo">V</div>
    <h1><?php echo e($company); ?></h1>
    <p class="sub">Silakan login untuk mengelola produk</p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo icon('shield'); ?> <?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="field">
        <label for="username">Username</label>
        <input class="input" type="text" id="username" name="username" placeholder="admin" autofocus required value="<?php echo e($_POST['username'] ?? ''); ?>">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input class="input" type="password" id="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="padding:12px;margin-top:6px;">Masuk</button>
    </form>
  </div>
</div>
</body>
</html>
