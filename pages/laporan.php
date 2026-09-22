<?php
$base_url = '../';
$page_title = 'Laporan';
require_once '../config/db.php';
require_once '../models/TransaksiModel.php';
require_once '../includes/header.php';

if ($role !== 'admin') { header('Location: dashboard.php'); exit; }

$dari   = clean($_GET['dari']   ?? date('Y-m-01'));
$sampai = clean($_GET['sampai'] ?? date('Y-m-d'));

$data             = getTransaksiByPeriod($pdo, $dari, $sampai);
$total_pendapatan = array_sum(array_column($data, 'total'));
$total_trx        = count($data);
?>

<div class="toolbar">
    <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;flex:1">
        <div class="form-group" style="margin:0">
            <label class="form-label">Dari</label>
            <input class="form-control" type="date" name="dari" value="<?= $dari ?>">
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Sampai</label>
            <input class="form-control" type="date" name="sampai" value="<?= $sampai ?>">
        </div>
        <div class="form-group" style="margin:0;justify-content:flex-end">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>
    <div style="display:flex;gap:.75rem;margin-top:auto">
        <div class="stat-card" style="padding:.75rem 1.25rem;white-space:nowrap">
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value"><?= $total_trx ?></div>
        </div>
        <div class="stat-card" style="padding:.75rem 1.25rem;white-space:nowrap">
            <div class="stat-label">Total Pendapatan</div>
            <div class="stat-value" style="font-size:1rem"><?= rupiah($total_pendapatan) ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode</th><th>Tanggal</th><th>Kasir</th>
                    <th>Metode</th><th>Total</th><th>Bayar</th>
                    <th>Kembalian</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($data)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;color:var(--muted);padding:2rem">
                        Tidak ada transaksi di periode ini
                    </td>
                </tr>
            <?php else: ?>
            <?php foreach ($data as $d): ?>
            <tr>
                <td><code style="font-size:.75rem"><?= $d['kode_transaksi'] ?></code></td>
                <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                <td><?= clean($d['kasir']) ?></td>
                <td><span class="badge badge-purple"><?= strtoupper($d['metode_bayar']) ?></span></td>
                <td><?= rupiah($d['total']) ?></td>
                <td><?= rupiah($d['bayar']) ?></td>
                <td><?= rupiah($d['kembalian']) ?></td>
                <td>
                    <a href="struk.php?id=<?= $d['id'] ?>" target="_blank" class="btn btn-ghost btn-sm">
                        🖨️ Struk
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>