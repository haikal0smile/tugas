<?php
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) redirect('/index.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'admin') {
            redirect('/admin/dashboard.php');
        }
        redirect('/index.php');
    } else {
        $errors[] = 'Email atau password salah.';
    }
}

$pageTitle = 'Masuk';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-panel">
  <h2>Masuk</h2>
  <p class="form-sub">Masuk untuk melanjutkan belanja atau mengelola toko.</p>

  <?php foreach ($errors as $err): ?>
    <div class="flash flash-error"><?= sanitize($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="<?= BASE_URL ?>/login.php">
    <div class="field">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Masuk</button>
  </form>

  <p class="form-foot">Belum punya akun? <a href="<?= BASE_URL ?>/register.php">Daftar di sini</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
