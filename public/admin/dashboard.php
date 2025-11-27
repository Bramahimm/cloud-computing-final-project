<?php
// FILE: public/admin/dashboard.php

require_once __DIR__ . '/../../config/app.php';
require_role(['admin']); // Hanya Admin yang bisa akses

$logModel = new Log($pdo);
$latestLogs = $logModel->getLatestLogs(10);

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../../views/header.php';
?>

<div class="row g-4">
  <!-- Sidebar Admin -->
  <div class="col-lg-3">
    <div class="admin-sidebar shadow-sm">
      <h5 class="text-primary mb-3">Navigasi Cepat</h5>
      <div class="list-group">
        <a href="dashboard.php" class="list-group-item list-group-item-action active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Utama</a>
        <a href="kelola_user.php" class="list-group-item list-group-item-action"><i class="fas fa-users-cog me-2"></i> Kelola User</a>
        <a href="laporan.php" class="list-group-item list-group-item-action"><i class="fas fa-chart-line me-2"></i> Laporan Absensi</a>
      </div>
    </div>
  </div>

  <!-- Konten Dashboard -->
  <div class="col-lg-9">
    <div class="row g-4">
      <!-- Card Statistik Sederhana -->
      <div class="col-md-4">
        <div class="card text-white bg-info shadow-lg border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="fas fa-user-check"></i> Total User</h5>
            <p class="card-text fs-3 fw-bold"><?= (new User($pdo))->getAllUsers() ? count((new User($pdo))->getAllUsers()) : 0 ?></p>
            <small>Termasuk admin, dosen, dan mahasiswa.</small>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-white bg-success shadow-lg border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="fas fa-calendar-alt"></i> Total Absen (Bulan Ini)</h5>
            <!-- Logika ini memerlukan query tambahan di AbsensiModel, tapi untuk demo, kita beri placeholder -->
            <p class="card-text fs-3 fw-bold">N/A</p>
            <small>Fitur ini butuh pengembangan lebih lanjut.</small>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-white bg-danger shadow-lg border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Pelanggaran Hari Ini</h5>
            <p class="card-text fs-3 fw-bold">0</p>
            <small>Pelanggaran jarak/waktu (Placeholder).</small>
          </div>
        </div>
      </div>

      <!-- Log Aktivitas Admin -->
      <div class="col-12">
        <div class="card shadow-lg border-0">
          <div class="card-body">
            <h5 class="card-title mb-3"><i class="fas fa-scroll"></i> Log Aktivitas Terbaru (Audit Log)</h5>
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead>
                  <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Aktivitas</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($latestLogs) > 0): ?>
                    <?php foreach ($latestLogs as $log): ?>
                      <tr>
                        <td><?= date('d/m H:i:s', strtotime($log['waktu'])) ?></td>
                        <td><?= htmlspecialchars($log['nama']) ?? 'Sistem' ?></td>
                        <td><?= htmlspecialchars($log['aktivitas']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="3" class="text-center">Belum ada log aktivitas.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/../../views/footer.php';
?>