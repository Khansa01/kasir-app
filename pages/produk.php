<?php
$base_url = '../';
$page_title = 'Produk';
require_once '../config/db.php';
require_once '../models/ProdukModel.php';
require_once '../models/KategoriModel.php';
require_once '../includes/header.php';

if ($role !== 'admin') { header('Location: dashboard.php'); exit; }

$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = intval($_POST['id'] ?? 0);
    $nama       = clean($_POST['nama'] ?? '');
    $kode       = clean($_POST['kode'] ?? '');
    $harga      = floatval($_POST['harga'] ?? 0);
    $stok       = intval($_POST['stok'] ?? 0);
    $satuan     = clean($_POST['satuan'] ?? 'pcs');
    $kategori_id = intval($_POST['kategori_id'] ?? 0);

    if (!$nama || !$harga) {
        $err = 'Nama dan harga wajib diisi.';
    } else {
        if ($id) {
            updateProduk($pdo, $id, $nama, $kode, $harga, $stok, $satuan, $kategori_id);
            $msg = 'Produk berhasil diperbarui.';
        } else {
            createProduk($pdo, $nama, $kode, $harga, $stok, $satuan, $kategori_id);
            $msg = 'Produk berhasil ditambahkan.';
        }
    }
}

if (isset($_GET['hapus'])) {
    deleteProduk($pdo, intval($_GET['hapus']));
    $msg = 'Produk dihapus.';
}

$edit     = isset($_GET['edit']) ? getProdukById($pdo, intval($_GET['edit'])) : null;
$produk   = getAllProduk($pdo);
$kategori = getAllKategori($pdo);
?>

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= $err ?></div><?php endif; ?>

<div class="card" style="margin-bottom:1.25rem">
    <strong style="display:block;margin-bottom:1rem"><?= $edit ? 'Edit Produk' : 'Tambah Produk' ?></strong>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
        <div class="form-row" style="grid-template-columns:2fr 1fr 1fr 1fr 1fr auto">
            <div class="form-group">
                <label class="form-label">Nama Produk</label>
                <input class="form-control" name="nama" required value="<?= clean($edit['nama'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Kode</label>
                <input class="form-control" name="kode" value="<?= clean($edit['kode'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Harga (Rp)</label>
                <input class="form-control" name="harga" type="number" required value="<?= $edit['harga'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Stok</label>
                <input class="form-control" name="stok" type="number" value="<?= $edit['stok'] ?? 0 ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Kategori</label>
                <select class="form-control" name="kategori_id">
                    <option value="">—</option>
                    <?php foreach ($kategori as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= ($edit['kategori_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                        <?= clean($k['nama']) ?>
                    </option>
                    <?php endforeach; ?>
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
            <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($produk as $p): ?>
            <tr>
                <td><code style="font-size:.75rem"><?= clean($p['kode']) ?></code></td>
                <td><?= clean($p['nama']) ?></td>
                <td><span class="badge badge-purple"><?= clean($p['kat'] ?? '—') ?></span></td>
                <td><?= rupiah($p['harga']) ?></td>
                <td><span class="badge <?= $p['stok'] <= 5 ? 'badge-red' : 'badge-green' ?>"><?= $p['stok'] ?></span></td>
                <td>
                    <a href="?edit=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                    <a href="?hapus=<?= $p['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Hapus produk ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>