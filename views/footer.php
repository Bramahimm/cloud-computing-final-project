<?php
// FILE: views/footer.php
// Pastikan config/app.php sudah dipanggil
if (!defined('BASE_URL')) exit();
?>
</main>

<footer class="text-center py-3 mt-5 border-top">
  <div class="container">
    <p class="mb-0 text-muted">&copy; <?= date('Y') ?> <?= APP_NAME ?>. Built with PHP Native & Bootstrap 5.</p>
  </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Script Real-time Clock
  function updateClock() {
    const now = new Date();
    // Format waktu (misal: 14:30:59)
    const timeString = now.toLocaleTimeString('id-ID', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
    // Format tanggal (misal: Senin, 27 November 2025)
    const dateString = now.toLocaleDateString('id-ID', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });

    const clockElement = document.getElementById('server-clock');
    if (clockElement) {
      clockElement.textContent = `${timeString} | ${dateString}`;
    }
  }
  setInterval(updateClock, 1000);
  updateClock(); // Initial call

  // Dark Mode Toggle
  const darkModeToggle = document.getElementById('dark-mode-toggle');
  const htmlElement = document.documentElement;

  function setDarkMode(isDark) {
    htmlElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
    localStorage.setItem('darkMode', isDark ? 'dark' : 'light');
    if (darkModeToggle) {
      darkModeToggle.innerHTML = isDark ?
        '<i class="fas fa-sun"></i> Light Mode' :
        '<i class="fas fa-moon"></i> Dark Mode';
    }
  }

  // Load saved preference
  const savedTheme = localStorage.getItem('darkMode') ||
    (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  setDarkMode(savedTheme === 'dark');

  // Attach toggle listener
  if (darkModeToggle) {
    darkModeToggle.addEventListener('click', (e) => {
      e.preventDefault();
      const currentTheme = htmlElement.getAttribute('data-bs-theme');
      setDarkMode(currentTheme === 'light');
    });
  }

  // Auto-hide flash message
  const flashMessage = document.getElementById('flash-message');
  if (flashMessage) {
    setTimeout(() => {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(flashMessage);
      bsAlert.close();
    }, 5000); // Sembunyikan setelah 5 detik
  }
</script>
</body>

</html>