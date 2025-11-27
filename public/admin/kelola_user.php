<?php
// FILE: public/admin/kelola_user.php

require_once __DIR__ . '/../../config/app.php';
require_role(['admin']);

$userModel = new User($pdo);
$logModel = new Log($pdo);
$adminId = $_SESSION['user_id'];
$page_title = 'Kelola User';
$allUsers = $userModel->getAllUsers();

$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = sanitize_input($_POST['nama']);
  $email = sanitize_input($_POST['email']);
  $role = sanitize_input($_POST['role']);
  $password = $_POST['password'] ?? DEFAULT_PASSWORD; // Default jika tambah

  // Tambah User
  if ($action === 'create') {
    if ($userModel->create($nama, $email, $password, $role)) {
      $logModel->createLog("Admin menambah user baru: {$nama} ({$email}).", $adminId);
      flash("User baru berhasil ditambahkan.", "success");
    } else {
      flash("Gagal menambahkan user. Email mungkin sudah terdaftar.", "danger");
    }
  }
  // Edit User
  elseif ($action === 'edit' && $id) {
    if ($userModel->update($id, $nama, $email, $role)) {
      $logModel->createLog("Admin mengupdate user ID {$id}: {$nama} ({$email}).", $adminId);
      flash("User berhasil diupdate.", "success");
    } else {
      flash("Gagal mengupdate user.", "danger");
    }
  }
  // Reset Password
  elseif ($action === 'reset' && $id) {
    if ($userModel->resetPassword($id, DEFAULT_PASSWORD)) {
      $logModel->createLog("Admin mereset password user ID {$id}.", $adminId);
      flash("Password user ID {$id} berhasil direset menjadi " . DEFAULT_PASSWORD, "info");
    } else {
      flash("Gagal mereset password.", "danger");
    }
  }
  redirect('admin/kelola_user.php');
}

// Handle Delete (GET request for simplicity in native PHP)
if ($action === 'delete' && $id) {
  if ($userModel->delete($id)) {
    $logModel->createLog("Admin menghapus user ID {$id}.", $adminId);
    flash("User berhasil dihapus.", "success");
  } else {
    flash("Gagal menghapus user.", "danger");
  }
  redirect('admin/kelola_user.php');
}

// Data untuk form edit
$userToEdit = null;
if ($action === 'edit' && $id) {
  $userToEdit = $userModel->find($id);
  if (!$userToEdit) {
    flash("User tidak ditemukan.", "danger");
    redirect('admin/kelola_user.php');
  }
}


require_once __DIR__ . '/../../views/header.php';
?>

<div class="row g-4">
  <!-- Sidebar Admin -->
  <div class="col-lg-3">
    <?php include __DIR__ . '/../admin_sidebar.php'; // Sidebar sederhana 
    ?>
    <div class="admin-sidebar shadow-sm">
      <h5 class="text-primary mb-3">Navigasi Cepat</h5>
      <div class="list-group">
        <a href="dashboard.php" class="list-group-item list-group-item-action"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Utama</a>
        <a href="kelola_user.php" class="list-group-item list-group-item-action active"><i class="fas fa-users-cog me-2"></i> Kelola User</a>
        <a href="laporan.php" class="list-group-item list-group-item-action"><i class="fas fa-chart-line me-2"></i> Laporan Absensi</a>
      </div>
    </div>
  </div>

  <!-- Konten Kelola User -->
  <div class="col-lg-9">
    <!-- Form Tambah/Edit User -->
    <div class="card shadow-lg mb-4 border-0">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-<?= $userToEdit ? 'edit' : 'plus' ?>"></i> <?= $userToEdit ? 'Edit User: ' . htmlspecialchars($userToEdit['nama']) : 'Tambah User Baru' ?></h5>
      </div>
      <div class="card-body">
        <form action="kelola_user.php?action=<?= $userToEdit ? 'edit&id=' . $userToEdit['id'] : 'create' ?>" method="POST">
          <div class="mb-3">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="nama" name="nama" value="<?= $userToEdit ? htmlspecialchars($userToEdit['nama']) : '' ?>" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email (Login)</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= $userToEdit ? htmlspecialchars($userToEdit['email']) : '' ?>" required>
          </div>
          <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role" required>
              <option value="mahasiswa" <?= $userToEdit && $userToEdit['role'] === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
              <option value="dosen" <?= $userToEdit && $userToEdit['role'] === 'dosen' ? 'selected' : '' ?>>Dosen</option>
              <option value="admin" <?= $userToEdit && $userToEdit['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
          </div>
          <?php if (!$userToEdit): ?>
            <div class="mb-3">
              <label for="password" class="form-label">Password Awal</label>
              <input type="text" class="form-control" id="password" name="password" value="<?= DEFAULT_PASSWORD ?>" required>
              <small class="form-text text-muted">Password akan di-hash saat disimpan.</small>
            </div>
          <?php endif; ?>
          <button type="submit" class="btn btn-<?= $userToEdit ? 'warning' : 'primary' ?>"><i class="fas fa-save me-2"></i> Simpan User</button>
          <?php if ($userToEdit): ?>
            <a href="kelola_user.php" class="btn btn-secondary">Batal Edit</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <!-- Tabel Daftar User -->
    <div class="card shadow-lg border-0">
      <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i> Daftar Semua User</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead>
              <tr class="table-secondary">
                <th>#</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($allUsers) > 0): ?>
                <?php $no = 1;
                foreach ($allUsers as $user): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($user['nama']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'dosen' ? 'info' : 'primary') ?>"><?= ucfirst($user['role']) ?></span></td>
                    <td class="text-nowrap">
                      <a href="kelola_user.php?action=edit&id=<?= $user['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                      <form method="POST" action="kelola_user.php?action=reset&id=<?= $user['id'] ?>" class="d-inline" onsubmit="return confirm('Yakin ingin mereset password <?= htmlspecialchars($user['nama']) ?>? Password akan menjadi <?= DEFAULT_PASSWORD ?>.')">
                        <button type="submit" class="btn btn-sm btn-info text-white" title="Reset Password"><i class="fas fa-sync-alt"></i></button>
                      </form>
                      <form method="GET" action="kelola_user.php" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus <?= htmlspecialchars($user['nama']) ?>? Tindakan ini tidak bisa dibatalkan.')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center">Tidak ada user yang terdaftar.</td>
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