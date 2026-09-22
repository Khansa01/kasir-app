<?php
$base_url = '../';
$page_title = 'Pengguna';
require_once '../config/db.php';
require_once '../models/UserModel.php';
require_once '../includes/header.php';

if ($role !== 'admin') { header('Location: dashboard.php'); exit; }

$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = intval($_POST['id'] ?? 0);
    $nama     = clean($_POST['nama'] ?? '');
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_u   = in_array($_POST['role'], ['admin','kasir']) ? $_POST['role'] : 'kasir';

    if (!$nama || !$username) {
        $err = 'Nama dan username wajib diisi.';
    } else {
        if ($id) {
            updateUser($pdo, $id, $nama, $username, $role_u, $password ?: null);
            $msg = 'Pengguna diperbarui.';
        } else {
            if (!$password) { $err = 'Password wajib untuk pengguna baru.'; }
            else {
                createUser($pdo, $nama, $username, $password, $role_u);
                $msg = 'Pengguna ditambahkan.';
            }
        }
    }
}

if (isset($_GET['hapus']) && intval($_GET['hapus']) !== intval($_SESSION['user_id'])) {
    deleteUser($pdo, intval($_GET['hapus']));
    $msg = 'Pengguna dihapus.';
}

$edit  = isset($_GET['edit']) ? getUserById($pdo, intval($_GET['edit'])) : null;
$users = getAllUsers($pdo);
?>

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= $err ?></div><?php endif; ?>

<div class="card" style="margin-bottom:1.25rem">
    <strong style="display:block;margin-bottom:1rem"><?= $edit ? 'Edit Pengguna' : 'Tambah Pengguna' ?></strong>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <div class="form-row" style="grid-template-columns:2fr 1fr 1fr 1fr auto">
            <div class="form-group">
                <label class="form-label">Nama</label>
                <input class="form-control" name="nama" required value="<?= clean($edit['nama'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Username</label>
                <input class="form-control" name="username" required value="<?= clean($edit['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Password <?= $edit ? '(kosongi = tidak ganti)' : '' ?></label>
                <input class="form-control" name="password" type="password" <?= $edit ? '' : 'required' ?>>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select class="form-control" name="role">
                    <option value="kasir" <?= ($edit['role'] ?? '') === 'kasir' ? 'selected' : '' ?>>Kasir</option>
                    <option value="admin" <?= ($edit['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="form-group" style="justify-content:flex-end">
                <label class="form-label">&nbsp;</label>
                <button class="btn btn-primary"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nama</th><th>Username</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= clean($u['nama']) ?></td>
                <td><code><?= clean($u['username']) ?></code></td>
                <td>
                    <span class="badge <?= $u['role']==='admin' ? 'badge-purple' : 'badge-green' ?>">
                        <?= $u['role'] ?>
                    </span>
                </td>
                <td style="color:var(--muted);font-size:.78rem"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <a href="?edit=<?= $u['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                    <?php if ($u['id'] !== intval($_SESSION['user_id'])): ?>
                    <a href="?hapus=<?= $u['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Hapus pengguna ini?')">Hapus</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>