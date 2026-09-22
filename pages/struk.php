<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
require_once '../config/db.php';
require_once '../models/TransaksiModel.php';

$id  = intval($_GET['id'] ?? 0);
$trx = getTransaksiById($pdo, $id);
if (!$trx) die('Transaksi tidak ditemukan');

$items = getDetailTransaksi($pdo, $id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk <?= $trx['kode_transaksi'] ?></title>
    <style>
        body { font-family: monospace; font-size: 12px; max-width: 300px; margin: 0 auto; padding: 1rem; }
        .center { text-align: center; }
        hr { border: none; border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; }
        .bold { font-weight: bold; }
        @media screen {
            body { background: #f5f5f5; }
            .struk { background: #fff; padding: 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,.1); border-radius: 8px; }
            .btn-print {
                display: block; text-align: center;
                margin: 1rem auto; padding: .5rem 2rem;
                cursor: pointer; background: #6c63ff;
                color: #fff; border: none; border-radius: 8px;
                font-size: 14px; font-family: monospace;
            }
        }
        @media print { .btn-print { display: none; } }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ Print Struk</button>
    <div class="struk">
        <div class="center bold" style="font-size:14px">KasirKu</div>
        <div class="center">Sistem Kasir Digital</div>
        <hr>
        <div class="row"><span>Kode</span><span><?= $trx['kode_transaksi'] ?></span></div>
        <div class="row"><span>Kasir</span><span><?= clean($trx['kasir']) ?></span></div>
        <div class="row"><span>Tanggal</span><span><?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?></span></div>
        <div class="row"><span>Metode</span><span><?= strtoupper($trx['metode_bayar']) ?></span></div>
        <hr>
        <?php foreach ($items as $item): ?>
        <div><?= clean($item['nama_produk']) ?></div>
        <div class="row">
            <span><?= $item['qty'] ?> x <?= rupiah($item['harga']) ?></span>
            <span><?= rupiah($item['subtotal']) ?></span>
        </div>
        <?php endforeach; ?>
        <hr>
        <div class="row bold"><span>TOTAL</span><span><?= rupiah($trx['total']) ?></span></div>
        <div class="row"><span>BAYAR</span><span><?= rupiah($trx['bayar']) ?></span></div>
        <div class="row"><span>KEMBALI</span><span><?= rupiah($trx['kembalian']) ?></span></div>
        <hr>
        <div class="center">Terima kasih sudah berbelanja! 🙏</div>
    </div>
</body>
</html>