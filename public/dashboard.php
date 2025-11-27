<?php
// FILE: public/dashboard.php

require_once __DIR__ . '/../config/app.php';
require_login();

$absensiModel = new Absensi($pdo);
$logModel = new Log($pdo);

$page_title = 'Dashboard Pengguna';
$userId = $_SESSION['user_id'];
$today = date('Y-m-d');
$todayStatus = $absensiModel->getTodayStatus($userId, $today);
$riwayatSingkat = $absensiModel->getHistory($userId, 5); // Riwayat singkat 5 hari

// Tentukan status absen hari ini
$absen_status = 'Belum Absen';
$jam_masuk = 'N/A';
$jam_pulang = 'N/A';

if ($todayStatus) {
  $jam_masuk = $todayStatus['jam_masuk'] ?? 'N/A';
  if ($todayStatus['jam_pulang']) {
    $jam_pulang = $todayStatus['jam_pulang'];
    $absen_status = 'Lengkap';
  } else {
    $absen_status = 'Sudah Masuk';
  }
}

// Hitung Durasi Kerja (hanya jika sudah pulang)
$durasi = 'N/A';
if ($absen_status === 'Lengkap') {
  $time_in = strtotime($todayStatus['jam_masuk']);
  $time_out = strtotime($todayStatus['jam_pulang']);
  $diff = abs($time_out - $time_in);
  $durasi = gmdate("H jam i menit", $diff);
}

require_once __DIR__ . '/../views/header.php';
?>
<div class="row g-4">
  <!-- Status & Jam Server -->
  <div class="col-md-4">
    <div class="card text-center text-white bg-primary shadow-lg border-0" id="server-clock-card">
      <div class="card-body">
        <h5 class="card-title mb-1">Jam Server</h5>
        <p class="card-text fw-bold" id="server-clock" style="font-size: 2rem;"></p>
      </div>
    </div>
  </div>

  <!-- Status Absen Hari Ini -->
  <div class="col-md-8">
    <div class="card h-100 shadow-lg border-0">
      <div class="card-body">
        <h5 class="card-title mb-3"><i class="fas fa-calendar-check"></i> Status Absensi Hari Ini (<?= date('d M Y') ?>)</h5>
        <div class="row">
          <div class="col-6">
            <p class="text-muted mb-1">Jam Masuk:</p>
            <h4 class="fw-bold text-success"><?= $jam_masuk ?></h4>
            <?php if ($todayStatus && $todayStatus['lokasi_masuk']): ?>
              <small class="text-secondary" title="Koordinat GPS Saat Absen Masuk"><i class="fas fa-map-marker-alt"></i> Lokasi Masuk Tercatat</small>
            <?php endif; ?>
          </div>
          <div class="col-6">
            <p class="text-muted mb-1">Jam Pulang:</p>
            <h4 class="fw-bold text-danger"><?= $jam_pulang ?></h4>
            <?php if ($todayStatus && $todayStatus['lokasi_pulang']): ?>
              <small class="text-secondary" title="Koordinat GPS Saat Absen Pulang"><i class="fas fa-map-marker-alt"></i> Lokasi Pulang Tercatat</small>
            <?php endif; ?>
          </div>
        </div>
        <hr>
        <p class="mb-1">Durasi Kerja: <span class="fw-bold text-info"><?= $durasi ?></span></p>
        <p class="mb-0">Status:
          <span class="badge rounded-pill bg-<?= $absen_status === 'Lengkap' ? 'success' : ($absen_status === 'Sudah Masuk' ? 'warning' : 'secondary') ?>">
            <?= $absen_status ?>
          </span>
        </p>
      </div>
    </div>
  </div>

  <!-- Tombol Absensi -->
  <div class="col-12">
    <div class="card p-4 text-center shadow-lg border-0">
      <?php if ($absen_status === 'Belum Absen'): ?>
        <p class="lead">Saatnya absen masuk!</p>
        <a href="<?= BASE_URL ?>absen_masuk.php" class="btn btn-success btn-absen">
          <i class="fas fa-sign-in-alt me-2"></i> Absen Masuk
        </a>
      <?php elseif ($absen_status === 'Sudah Masuk'): ?>
        <p class="lead">Anda sudah absen masuk. Jangan lupa absen pulang!</p>
        <a href="<?= BASE_URL ?>absen_pulang.php" class="btn btn-warning btn-absen">
          <i class="fas fa-sign-out-alt me-2"></i> Absen Pulang
        </a>
      <?php else: ?>
        <p class="lead text-success">Absensi Anda hari ini sudah lengkap. Terima kasih!</p>
        <a href="<?= BASE_URL ?>riwayat.php" class="btn btn-outline-primary btn-absen disabled">
          <i class="fas fa-check-double me-2"></i> Absensi Selesai
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Riwayat Singkat -->
  <div class="col-12">
    <div class="card shadow-lg border-0">
      <div class="card-body">
        <h5 class="card-title mb-3"><i class="fas fa-history"></i> Riwayat Singkat Absensi</h5>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($riwayatSingkat) > 0): ?>
                <?php foreach ($riwayatSingkat as $r): ?>
                  <tr>
                    <td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                    <td><span class="badge bg-success"><?= $r['jam_masuk'] ?? '-' ?></span></td>
                    <td><span class="badge bg-danger"><?= $r['jam_pulang'] ?? '-' ?></span></td>
                    <td>
                      <?php
                      $statusBadge = $r['jam_masuk'] && $r['jam_pulang'] ? 'success' : ($r['jam_masuk'] ? 'warning' : 'secondary');
                      $statusText = $r['jam_masuk'] && $r['jam_pulang'] ? 'Lengkap' : ($r['jam_masuk'] ? 'Sudah Masuk' : 'Belum Absen');
                      ?>
                      <span class="badge bg-<?= $statusBadge ?>"><?= $statusText ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-center">Belum ada riwayat absensi.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="text-end">
          <a href="<?= BASE_URL ?>riwayat.php" class="btn btn-sm btn-outline-primary">Lihat Semua Riwayat <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
require_once __DIR__ . '/../views/footer.php';
?>