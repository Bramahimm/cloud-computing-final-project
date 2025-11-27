<?php
// FILE: public/riwayat.php

require_once __DIR__ . '/../config/app.php';
require_login();

$absensiModel = new Absensi($pdo);

$page_title = 'Riwayat Absensi Saya';
$userId = $_SESSION['user_id'];
$riwayat = $absensiModel->getHistory($userId, 1000); // Ambil semua riwayat

function calculate_duration($time_in, $time_out) {
  if (!$time_in || !$time_out) return 'N/A';
  $time_in = strtotime($time_in);
  $time_out = strtotime($time_out);
  $diff = abs($time_out - $time_in);
  return gmdate("H jam i menit", $diff);
}

require_once __DIR__ . '/../views/header.php';
?>

<div class="card shadow-lg border-0">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr class="table-primary">
            <th>#</th>
            <th>Tanggal</th>
            <th>Jam Masuk</th>
            <th>Jam Pulang</th>
            <th>Durasi</th>
            <th>Status</th>
            <th>IP & Lokasi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($riwayat) > 0): ?>
            <?php $no = 1;
            foreach ($riwayat as $r): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td class="fw-bold"><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                <td><?= $r['jam_masuk'] ? '<span class="badge bg-success">' . $r['jam_masuk'] . '</span>' : '-' ?></td>
                <td><?= $r['jam_pulang'] ? '<span class="badge bg-danger">' . $r['jam_pulang'] . '</span>' : '-' ?></td>
                <td><?= calculate_duration($r['jam_masuk'], $r['jam_pulang']) ?></td>
                <td>
                  <?php
                  $statusBadge = $r['jam_masuk'] && $r['jam_pulang'] ? 'success' : ($r['jam_masuk'] ? 'warning' : 'secondary');
                  $statusText = $r['jam_masuk'] && $r['jam_pulang'] ? 'Lengkap' : ($r['jam_masuk'] ? 'Belum Pulang' : 'Tidak Absen');
                  ?>
                  <span class="badge bg-<?= $statusBadge ?>"><?= $statusText ?></span>
                </td>
                <td>
                  <small class="d-block text-muted" title="IP Address"><?= htmlspecialchars($r['ip_address']) ?></small>
                  <small class="d-block text-info" title="Lokasi Masuk"><i class="fas fa-map-marker-alt"></i> Masuk: <?= htmlspecialchars($r['lokasi_masuk']) ?: 'N/A' ?></small>
                  <small class="d-block text-info" title="Lokasi Pulang"><i class="fas fa-map-marker-alt"></i> Pulang: <?= htmlspecialchars($r['lokasi_pulang']) ?: 'N/A' ?></small>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center">Belum ada riwayat absensi.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/../views/footer.php';
?>