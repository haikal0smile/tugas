<?php
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) redirect('/index.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'Nama, email, dan password wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    if (empty($errors)) {
        $db = getDB();
        $check = $db->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'Email sudah terdaftar. Silakan masuk.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, "customer")');
            $stmt->execute([$name, $email, $hash, $phone]);

            $_SESSION['user_id'] = $db->lastInsertId();
            $_SESSION['role'] = 'customer';
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Akun berhasil dibuat. Selamat datang, ' . $name . '!'];
            redirect('/index.php');
        }
    }
}

$pageTitle = 'Daftar Akun';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-panel">
  <h2>Buat Akun Baru</h2>
  <p class="form-sub">Daftar untuk mulai berbelanja produk UMKM kami.</p>

  <?php foreach ($errors as $err): ?>
    <div class="flash flash-error"><?= sanitize($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="<?= BASE_URL ?>/register.php">
    <div class="field">
      <label for="name">Nama Lengkap</label>
      <input type="text" id="name" name="name" value="<?= sanitize($_POST['name'] ?? '') ?>" required>
    </div>
    <div class="field">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
    </div>
    <div class="field">
      <label for="phone">Nomor HP</label>
      <input type="text" id="phone" name="phone" value="<?= sanitize($_POST['phone'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
    </div>
    <div class="field">
      <label for="confirm_password">Konfirmasi Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Daftar</button>
  </form>

  <p class="form-foot">Sudah punya akun? <a href="<?= BASE_URL ?>/login.php">Masuk di sini</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
