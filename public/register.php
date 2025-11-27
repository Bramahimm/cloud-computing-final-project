<?php
// FILE: public/register.php

require_once __DIR__ . '/../config/app.php';

$page_title = 'Registrasi Akun Baru';
$userModel = new User($pdo);
$logModel = new Log($pdo);

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
  redirect('dashboard.php');
}

// Handle POST Registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = sanitize_input($_POST['nama']);
  $email = sanitize_input($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
    flash("Semua kolom wajib diisi.", "danger");
    redirect('register.php');
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash("Format email tidak valid.", "danger");
    redirect('register.php');
  }

  if ($password !== $confirm_password) {
    flash("Konfirmasi password tidak cocok.", "danger");
    redirect('register.php');
  }

  // Cek apakah email sudah terdaftar
  if ($userModel->findByEmail($email)) {
    flash("Email sudah terdaftar. Silakan login atau gunakan email lain.", "danger");
    redirect('register.php');
  }

  // Role default untuk registrasi publik adalah 'mahasiswa'
  $default_role = 'mahasiswa';

  try {
    if ($userModel->create($nama, $email, $password, $default_role)) {
      // Log aktivitas registrasi
      $logModel->createLog("Akun baru diregistrasi: {$nama} ({$email}) sebagai {$default_role}.");

      // Auto-login setelah registrasi (optional, tapi praktis)
      $new_user = $userModel->findByEmail($email);
      $_SESSION['user_id'] = $new_user['id'];
      $_SESSION['user_name'] = $new_user['nama'];
      $_SESSION['user_role'] = $new_user['role'];

      flash("Registrasi berhasil! Selamat datang, {$nama}.", "success");
      redirect('dashboard.php');
    } else {
      flash("Registrasi gagal. Coba lagi.", "danger");
      redirect('register.php');
    }
  } catch (Exception $e) {
    flash("Terjadi error database. Coba lagi: " . $e->getMessage(), "danger");
    redirect('register.php');
  }
}

// Tampilan HTML
require_once __DIR__ . '/../views/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-7 col-lg-5">
    <div class="card p-4 shadow-lg border-0">
      <h2 class="card-title text-center mb-4 text-success">Daftar Akun</h2>
      <form action="register.php" method="POST">
        <div class="mb-3">
          <label for="nama" class="form-label">Nama Lengkap</label>
          <input type="text" class="form-control" id="nama" name="nama" required>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
          <label for="confirm_password" class="form-label">Konfirmasi Password</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-success w-100 mt-2"><i class="fas fa-user-plus me-2"></i> Daftar Sekarang</button>
      </form>
      <hr>
      <p class="text-center mb-0">Sudah punya akun? <a href="login.php" class="text-primary">Masuk di sini</a></p>
    </div>
  </div>
</div>
<?php
require_once __DIR__ . '/../views/footer.php';
?>