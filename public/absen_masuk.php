<?php
// FILE: public/absen_masuk.php

require_once __DIR__ . '/../config/app.php';
require_login();

$absensiModel = new Absensi($pdo);
$logModel = new Log($pdo);

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');
$todayStatus = $absensiModel->getTodayStatus($userId, $today);

// 1. Validasi: Sudah absen masuk?
if ($todayStatus && $todayStatus['jam_masuk']) {
  flash("Anda sudah absen masuk hari ini pada pukul " . $todayStatus['jam_masuk'] . ".", "warning");
  redirect('dashboard.php');
}

$page_title = 'Absen Masuk';

// Handle POST untuk konfirmasi absen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['latitude']) && isset($_POST['longitude'])) {
  $jamMasuk = date('H:i:s');
  $ipAddress = $_SERVER['REMOTE_ADDR'];
  $lokasi = sanitize_input($_POST['latitude']) . ',' . sanitize_input($_POST['longitude']);

  try {
    if ($absensiModel->checkIn($userId, $today, $jamMasuk, $ipAddress, $lokasi)) {
      $logModel->createLog("Absen Masuk berhasil pada {$jamMasuk} dari IP {$ipAddress}.", $userId);
      flash("Absen Masuk berhasil dicatat pada pukul {$jamMasuk}!", "success");
      redirect('dashboard.php');
    } else {
      flash("Gagal mencatat Absen Masuk. Coba lagi.", "danger");
      redirect('dashboard.php');
    }
  } catch (Exception $e) {
    flash("Terjadi error: " . $e->getMessage(), "danger");
    redirect('dashboard.php');
  }
}

// Tampilan Konfirmasi (dengan Geolocation API)
require_once __DIR__ . '/../views/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card p-4 shadow-lg border-0 text-center">
      <h2 class="card-title mb-4">Konfirmasi Absen Masuk</h2>
      <p class="lead">Anda akan mencatat waktu masuk Anda sekarang. Pastikan lokasi Anda sudah benar.</p>

      <p class="mb-2 text-muted">Waktu Server Saat Ini: <span class="fw-bold text-primary" id="server-clock-now"></span></p>
      <p class="mb-4 text-muted">Lokasi GPS Anda: <span class="fw-bold text-danger" id="lokasi-status">Mencari lokasi...</span></p>

      <form action="absen_masuk.php" method="POST" id="absen-form" style="display:none;">
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        <button type="submit" id="submit-absen" class="btn btn-success btn-absen mt-3" disabled>
          <i class="fas fa-check-circle me-2"></i> Konfirmasi Absen Masuk
        </button>
        <small class="d-block mt-2 text-warning">Anda hanya bisa Absen Masuk sekali sehari.</small>
      </form>

      <button id="retry-location" class="btn btn-outline-primary mt-3" style="display:none;">Coba Lagi Cari Lokasi</button>
      <small class="text-danger mt-3" id="error-message-location"></small>

    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const lokasiStatus = document.getElementById('lokasi-status');
    const submitButton = document.getElementById('submit-absen');
    const absenForm = document.getElementById('absen-form');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const retryButton = document.getElementById('retry-location');
    const errorMessage = document.getElementById('error-message-location');

    function updateClockNow() {
      const now = new Date();
      const timeString = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
      document.getElementById('server-clock-now').textContent = timeString;
    }
    setInterval(updateClockNow, 1000);
    updateClockNow();

    function getLocation() {
      lokasiStatus.textContent = 'Mencari lokasi...';
      lokasiStatus.classList.remove('text-success', 'text-danger');
      lokasiStatus.classList.add('text-primary');
      submitButton.disabled = true;
      absenForm.style.display = 'none';
      retryButton.style.display = 'none';
      errorMessage.textContent = '';


      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError, {
          enableHighAccuracy: true,
          timeout: 5000,
          maximumAge: 0
        });
      } else {
        showError({
          code: 0,
          message: "Geolocation tidak didukung oleh browser ini."
        });
      }
    }

    function showPosition(position) {
      const lat = position.coords.latitude;
      const lon = position.coords.longitude;

      // Contoh validasi jarak (opsional, ganti dengan koordinat kantor/kampus Anda)
      // const targetLat = -6.2000; // Contoh: Jakarta
      // const targetLon = 106.8166;

      // const distance = getDistance(lat, lon, targetLat, targetLon); // Jarak dalam KM

      lokasiStatus.textContent = `(${lat.toFixed(6)}, ${lon.toFixed(6)})`;
      lokasiStatus.classList.remove('text-primary');
      lokasiStatus.classList.add('text-success');

      latitudeInput.value = lat;
      longitudeInput.value = lon;

      // Jika validasi jarak diterapkan, atur tombol di sini
      // if (distance <= 0.5) { // Misalnya 500 meter
      submitButton.disabled = false;
      absenForm.style.display = 'block';
      // } else {
      //     showError({ code: 4, message: "Anda berada di luar jangkauan area absen yang diizinkan." });
      // }
    }

    function showError(error) {
      let msg = "";
      switch (error.code) {
        case error.PERMISSION_DENIED:
          msg = "Akses lokasi ditolak oleh pengguna. Izinkan akses untuk absen.";
          break;
        case error.POSITION_UNAVAILABLE:
          msg = "Informasi lokasi tidak tersedia.";
          break;
        case error.TIMEOUT:
          msg = "Waktu tunggu permintaan lokasi habis. Coba lagi.";
          break;
        case error.UNKNOWN_ERROR:
          msg = "Terjadi kesalahan yang tidak diketahui.";
          break;
        default:
          msg = error.message;
          break;
      }

      lokasiStatus.textContent = "Gagal!";
      lokasiStatus.classList.remove('text-primary');
      lokasiStatus.classList.add('text-danger');
      errorMessage.textContent = 'Error: ' + msg;
      submitButton.disabled = true;
      retryButton.style.display = 'block';
      absenForm.style.display = 'none';
    }

    // Haversine formula (opsional, jika ingin menghitung jarak)
    function getDistance(lat1, lon1, lat2, lon2) {
      const R = 6371; // Radius bumi dalam km
      const dLat = (lat2 - lat1) * (Math.PI / 180);
      const dLon = (lon2 - lon1) * (Math.PI / 180);
      const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      return R * c; // Jarak dalam km
    }

    // Initial call and retry listener
    getLocation();
    retryButton.addEventListener('click', getLocation);
  });
</script>
<?php
require_once __DIR__ . '/../views/footer.php';
?>