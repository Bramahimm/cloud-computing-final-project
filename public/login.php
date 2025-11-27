<?php
// FILE: public/login.php

require_once __DIR__ . '/../config/app.php';

$page_title = 'Login Sistem';
$userModel = new User($pdo);
$logModel = new Log($pdo);

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
  $userId = $_SESSION['user_id'] ?? null;
  $userName = $_SESSION['user_name'] ?? 'User tidak dikenal';
  $logModel->createLog("User '{$userName}' ({$userId}) telah logout.", $userId);

  session_unset();
  session_destroy();
  session_start();
  flash("Anda telah berhasil logout.", "info");
  redirect('login.php');
}

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
  redirect('dashboard.php');
}

// Handle POST Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = sanitize_input($_POST['email']);
  $password = $_POST['password']; // Password tidak perlu sanitize karena akan di-hash

  if (empty($email) || empty($password)) {
    flash("Email dan Password wajib diisi.", "danger");
    redirect('login.php');
  }

  $user = $userModel->findByEmail($email);

  if ($user && password_verify($password, $user['password'])) {
    // Login berhasil, set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nama'];
    $_SESSION['user_role'] = $user['role'];

    $logModel->createLog("User '{$user['nama']}' ({$user['id']}) berhasil login.", $user['id']);

    flash("Selamat datang, " . htmlspecialchars($user['nama']) . "!", "success");
    redirect('dashboard.php');
  } else {
    flash("Email atau Password salah.", "danger");
    redirect('login.php');
  }
}

// Tampilan HTML
require_once __DIR__ . '/../views/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-4">
    <div class="card p-4 shadow-lg border-0">
      <h2 class="card-title text-center mb-4 text-primary">Login</h2>
      <form action="login.php" method="POST">
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 mt-2">Masuk</button>
        <!-- Bagian di public/login.php yang diubah -->
        <hr>
        <p class="text-center mb-0">Belum punya akun? <a href="register.php" class="text-success">Daftar di sini</a></p>
      </form>
    </div>
  </div>
</div>
<?php
require_once __DIR__ . '/../views/footer.php';
?>