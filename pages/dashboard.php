<?php
$base_url = '../';
$page_title = 'Dashboard';
require_once '../config/db.php';
require_once '../models/TransaksiModel.php';
require_once '../models/ProdukModel.php';
require_once '../includes/header.php';

$today_stats  = getTodayStats($pdo);
$total_produk = getTotalProduk($pdo);
$stok_minim   = getStokMinim($pdo);
$weekly       = getWeeklyStats($pdo);
$recent       = getRecentTransaksi($pdo);
?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Transaksi Hari Ini</div>
        <div class="stat-value"><?= $today_stats['jml'] ?></div>
        <div class="stat-sub">Total transaksi</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pendapatan Hari Ini</div>
        <div class="stat-value" style="font-size:1.1rem"><?= rupiah($today_stats['total']) ?></div>
        <div class="stat-sub"><?= date('Y-m-d') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Produk</div>
        <div class="stat-value"><?= $total_produk ?></div>
        <div class="stat-sub">Produk aktif</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Stok Hampir Habis</div>
        <div class="stat-value" style="color: <?= $stok_minim > 0 ? 'var(--yellow)' : 'var(--green)' ?>">
            <?= $stok_minim ?>
        </div>
        <div class="stat-sub">Stok ≤ 5 unit</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <strong>Transaksi Terbaru</strong>
            <a href="laporan.php" class="btn btn-ghost btn-sm">Lihat semua</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Kode</th><th>Kasir</th><th>Total</th><th>Waktu</th></tr>
                </thead>
                <tbody>
                <?php if (empty($recent)): ?>
                    <tr><td colspan="4" style="text-align:center; color:var(--muted)">Belum ada transaksi</td></tr>
                <?php else: ?>
                <?php foreach ($recent as $r): ?>
                    <tr>
                        <td><code style="font-size:0.75rem"><?= $r['kode_transaksi'] ?></code></td>
                        <td><?= clean($r['kasir']) ?></td>
                        <td><?= rupiah($r['total']) ?></td>
                        <td style="color:var(--muted); font-size:0.78rem"><?= date('H:i', strtotime($r['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <strong style="display:block; margin-bottom:1rem">Penjualan 7 Hari Terakhir</strong>
        <?php if (empty($weekly)): ?>
            <p style="color:var(--muted); text-align:center; padding:2rem">Belum ada data</p>
        <?php else: ?>
            <?php
            $maxTotal = max(array_column($weekly, 'total'));
            foreach ($weekly as $w):
                $pct = $maxTotal > 0 ? ($w['total'] / $maxTotal * 100) : 0;
            ?>
            <div style="margin-bottom:0.75rem">
                <div style="display:flex; justify-content:space-between; font-size:0.78rem; margin-bottom:0.25rem">
                    <span><?= date('d M', strtotime($w['tgl'])) ?></span>
                    <span style="color:var(--accent)"><?= rupiah($w['total']) ?> (<?= $w['jml'] ?> trx)</span>
                </div>
                <div style="background:var(--bg); border-radius:4px; height:6px; overflow:hidden">
                    <div style="background:var(--accent); height:100%; width:<?= $pct ?>%; border-radius:4px"></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>