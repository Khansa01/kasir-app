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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk <?= $trx['kode_transaksi'] ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: #f5f5f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
            gap: 1rem;
        }

        .btn-print {
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-size: 14px;
            cursor: pointer;
            font-family: sans-serif;
        }

        .btn-print:hover { background: #15803d; }

        .struk {
            background: #fff;
            width: 80mm;
            padding: 6mm;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .struk-center { text-align: center; }
        .struk-bold { font-weight: bold; }
        .struk-lg { font-size: 14px; }
        .struk-sm { font-size: 10px; }

        .divider {
            border: none;
            border-top: 1px dashed #000;
            margin: 4mm 0;
        }

        .struk-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .struk-row.bold { font-weight: bold; font-size: 13px; }

        .item-row { margin-bottom: 3mm; }
        .item-name { font-weight: bold; }
        .item-detail {
            display: flex;
            justify-content: space-between;
            color: #444;
        }

        .struk-footer {
            text-align: center;
            margin-top: 4mm;
            font-size: 10px;
            color: #555;
        }

        @media print {
            * { -webkit-print-color-adjust: exact; }

            body {
                background: none;
                padding: 0;
                margin: 0;
            }

            .btn-print { display: none; }

            .struk {
                box-shadow: none;
                width: 80mm;
                padding: 4mm;
            }

            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ Print Struk</button>

    <div class="struk">
        <div class="struk-center struk-bold struk-lg">KasirKu</div>
        <div class="struk-center struk-sm">Sistem Kasir Digital</div>
        <div class="struk-center struk-sm">Terima kasih telah berbelanja</div>

        <hr class="divider">

        <div class="struk-row">
            <span>No</span>
            <span><?= $trx['kode_transaksi'] ?></span>
        </div>
        <div class="struk-row">
            <span>Tanggal</span>
            <span><?= date('d/m/Y', strtotime($trx['created_at'])) ?></span>
        </div>
        <div class="struk-row">
            <span>Waktu</span>
            <span><?= date('H:i', strtotime($trx['created_at'])) ?></span>
        </div>
        <div class="struk-row">
            <span>Kasir</span>
            <span><?= clean($trx['kasir']) ?></span>
        </div>
        <div class="struk-row">
            <span>Metode</span>
            <span><?= strtoupper($trx['metode_bayar']) ?></span>
        </div>

        <hr class="divider">

        <?php foreach ($items as $item): ?>
        <div class="item-row">
            <div class="item-name"><?= clean($item['nama_produk']) ?></div>
            <div class="item-detail">
                <span><?= $item['qty'] ?> x <?= rupiah($item['harga']) ?></span>
                <span><?= rupiah($item['subtotal']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>

        <hr class="divider">

        <div class="struk-row">
            <span>Subtotal</span>
            <span><?= rupiah($trx['total']) ?></span>
        </div>
        <div class="struk-row">
            <span>Bayar</span>
            <span><?= rupiah($trx['bayar']) ?></span>
        </div>
        <div class="struk-row bold">
            <span>Kembalian</span>
            <span><?= rupiah($trx['kembalian']) ?></span>
        </div>

        <hr class="divider">

        <div class="struk-footer">
            <p>— Terima kasih! —</p>
            <p>Simpan struk ini sebagai bukti pembayaran</p>
        </div>
    </div>
</body>
</html>