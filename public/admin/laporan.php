<?php
// FILE: public/admin/laporan.php

require_once __DIR__ . '/../../config/app.php';
require_role(['admin']);

$absensiModel = new Absensi($pdo);
$userModel = new User($pdo);

$page_title = 'Laporan Absensi';
$users = $userModel->getAllUsers();
$reports = [];
$filter_user_id = sanitize_input($_GET['user_id'] ?? null);
$filter_start_date = sanitize_input($_GET['start_date'] ?? date('Y-m-01'));
$filter_end_date = sanitize_input($_GET['end_date'] ?? date('Y-m-d'));
$action = $_GET['action'] ?? null;

// Ambil Laporan
$reports = $absensiModel->getReport($filter_user_id, $filter_start_date, $filter_end_date);

function calculate_duration_report($time_in, $time_out) {
  if (!$time_in || !$time_out) return 'N/A';
  $time_in = strtotime($time_in);
  $time_out = strtotime($time_out);
  $diff = abs($time_out - $time_in);
  return gmdate("H:i", $diff); // Format HH:MM
}

// Handle Export CSV
if ($action === 'export') {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=laporan_absensi_' . date('Ymd_His') . '.csv');
  $output = fopen('php://output', 'w');

  // Header CSV
  fputcsv($output, ['ID Absen', 'Nama User', 'Email', 'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Durasi (HH:MM)', 'IP Address', 'Lokasi Masuk', 'Lokasi Pulang']);

  // Data Rows
  foreach ($reports as $r) {
    $duration = calculate_duration_report($r['jam_masuk'], $r['jam_pulang']);
    $row = [
      $r['id'],
      $r['nama'],
      $r['email'],
      $r['tanggal'],
      $r['jam_masuk'] ?? 'N/A',
      $r['jam_pulang'] ?? 'N/A',
      $duration,
      $r['ip_address'],
      $r['lokasi_masuk'] ?? 'N/A',
      $r['lokasi_pulang'] ?? 'N/A'
    ];
    fputcsv($output, $row);
  }
  fclose($output);
  exit();
}

require_once __DIR__ . '/../../views/header.php';
?>

<div class="row g-4">
  <!-- Sidebar Admin -->
  <div class="col-lg-3">
    <div class="admin-sidebar shadow-sm">
      <h5 class="text-primary mb-3">Navigasi Cepat</h5>
      <div class="list-group">
        <a href="dashboard.php" class="list-group-item list-group-item-action"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Utama</a>
        <a href="kelola_user.php" class="list-group-item list-group-item-action"><i class="fas fa-users-cog me-2"></i> Kelola User</a>
        <a href="laporan.php" class="list-group-item list-group-item-action active"><i class="fas fa-chart-line me-2"></i> Laporan Absensi</a>
      </div>
    </div>
  </div>

  <!-- Konten Laporan -->
  <div class="col-lg-9">
    <div class="card shadow-lg mb-4 border-0">
      <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Filter Laporan</h5>
      </div>
      <div class="card-body">
        <form method="GET" action="laporan.php" class="row g-3 align-items-end">
          <div class="col-md-4">
            <label for="user_id" class="form-label">User</label>
            <select id="user_id" name="user_id" class="form-select">
              <option value="">-- Semua User --</option>
              <?php foreach ($users as $user): ?>
                <option value="<?= $user['id'] ?>" <?= $filter_user_id == $user['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($user['nama']) ?> (<?= ucfirst($user['role']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="start_date" class="form-label">Dari Tanggal</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $filter_start_date ?>" required>
          </div>
          <div class="col-md-3">
            <label for="end_date" class="form-label">Sampai Tanggal</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $filter_end_date ?>" required>
          </div>
          <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Cari</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Hasil Laporan -->
    <div class="card shadow-lg border-0">
      <div class="card-header d-flex justify-content-between align-items-center bg-light">
        <h5 class="mb-0"><i class="fas fa-table me-2"></i> Hasil Laporan Absensi</h5>
        <a href="laporan.php?action=export&user_id=<?= $filter_user_id ?>&start_date=<?= $filter_start_date ?>&end_date=<?= $filter_end_date ?>" class="btn btn-success btn-sm">
          <i class="fas fa-file-excel me-1"></i> Export CSV
        </a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead>
              <tr class="table-primary">
                <th>Tanggal</th>
                <th>Nama User</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($reports) > 0): ?>
                <?php foreach ($reports as $r): ?>
                  <tr>
                    <td class="fw-bold"><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($r['nama']) ?></td>
                    <td><?= $r['jam_masuk'] ? '<span class="badge bg-success">' . $r['jam_masuk'] . '</span>' : '-' ?></td>
                    <td><?= $r['jam_pulang'] ? '<span class="badge bg-danger">' . $r['jam_pulang'] . '</span>' : '-' ?></td>
                    <td><?= calculate_duration_report($r['jam_masuk'], $r['jam_pulang']) ?></td>
                    <td>
                      <?php
                      $statusBadge = $r['jam_masuk'] && $r['jam_pulang'] ? 'success' : ($r['jam_masuk'] ? 'warning' : 'secondary');
                      $statusText = $r['jam_masuk'] && $r['jam_pulang'] ? 'Lengkap' : ($r['jam_masuk'] ? 'Belum Pulang' : 'Tidak Absen');
                      ?>
                      <span class="badge bg-<?= $statusBadge ?>"><?= $statusText ?></span>
                    </td>
                    <td><small class="text-muted"><?= htmlspecialchars($r['ip_address']) ?></small></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center">Tidak ada data laporan untuk filter ini.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/../../views/footer.php';
?>