<?php
// FILE: views/header.php
// Pastikan config/app.php sudah dipanggil
if (!defined('BASE_URL')) exit();

$current_page = basename($_SERVER['PHP_SELF']);
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Guest';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?> | <?= APP_NAME ?></title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>
  <nav class="navbar navbar-expand-lg sticky-top shadow-sm" style="background-color: var(--bs-body-bg);">
    <div class="container-fluid container-lg">
      <a class="navbar-brand fw-bold text-primary" href="<?= BASE_URL ?>dashboard.php">
        <i class="fas fa-clock"></i> <?= APP_NAME ?>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <?php if (isset($_SESSION['user_id'])): ?>
          <ul class="navbar-nav ms-auto">
            <?php if ($is_admin): ?>
              <li class="nav-item">
                <a class="nav-link <?= $current_page === 'dashboard.php' && basename(dirname($_SERVER['PHP_SELF'])) === 'admin' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/dashboard.php">Admin Panel</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $current_page === 'kelola_user.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/kelola_user.php">Kelola User</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $current_page === 'laporan.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/laporan.php">Laporan</a>
              </li>
            <?php else: ?>
              <li class="nav-item">
                <a class="nav-link <?= $current_page === 'dashboard.php' && basename(dirname($_SERVER['PHP_SELF'])) !== 'admin' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard.php">Dashboard</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $current_page === 'riwayat.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>riwayat.php">Riwayat</a>
              </li>
            <?php endif; ?>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle"></i> <?= $user_name ?> (<?= ucfirst($_SESSION['user_role']) ?>)
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><span class="dropdown-item-text text-muted small">Halo, <?= $user_name ?>!</span></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="#" id="dark-mode-toggle"><i class="fas fa-moon"></i> Dark Mode</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
              </ul>
            </li>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main class="container-lg my-4">
    <?php display_flash(); ?>
    <h1 class="mb-4 text-primary"><?= htmlspecialchars($page_title) ?></h1>