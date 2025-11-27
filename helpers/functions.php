<?php
// FILE: helpers/functions.php

/**
 * Fungsi untuk mengarahkan (redirect) user.
 * @param string $path Path tujuan (misal: /dashboard.php)
 */
function redirect($path) {
  header("Location: " . BASE_URL . $path);
  exit();
}

/**
 * Mengatur flash message.
 * @param string $message Pesan yang akan ditampilkan.
 * @param string $type Tipe pesan (success, danger, warning, info)
 */
function flash($message, $type = 'success') {
  $_SESSION['flash'] = [
    'message' => $message,
    'type' => $type
  ];
}

/**
 * Menampilkan flash message dan menghapusnya dari session.
 */
function display_flash() {
  if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $type = htmlspecialchars($flash['type']);
    $message = htmlspecialchars($flash['message']);

    echo "<div id='flash-message' class='alert alert-$type alert-dismissible fade show' role='alert' style='position: fixed; top: 20px; right: 20px; z-index: 1050; min-width: 300px;'>";
    echo $message;
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";
  }
}

/**
 * Middleware: Cek apakah user sudah login.
 * Jika belum, redirect ke login page.
 */
function require_login() {
  if (!isset($_SESSION['user_id'])) {
    flash("Anda harus login untuk mengakses halaman ini.", "warning");
    redirect('login.php');
  }
}

/**
 * Middleware: Batasi akses berdasarkan role.
 * @param array $allowed_roles Array of roles yang diizinkan (misal: ['admin'])
 */
function require_role($allowed_roles) {
  require_login(); // Pastikan sudah login dulu

  if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    flash("Akses ditolak. Anda tidak memiliki izin untuk halaman ini.", "danger");
    redirect('dashboard.php'); // Atau halaman yang sesuai
  }
}

/**
 * Sanitize input (untuk mencegah XSS).
 * @param string $data Data input
 * @return string Data yang sudah disanitasi
 */
function sanitize_input($data) {
  return htmlspecialchars(strip_tags(trim($data)));
}
