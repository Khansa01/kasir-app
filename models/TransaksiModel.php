<?php
function getTodayStats($pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as jml, COALESCE(SUM(total),0) as total
        FROM transaksi WHERE DATE(created_at) = ?
    ");
    $stmt->execute([date('Y-m-d')]);
    return $stmt->fetch();
}

function getWeeklyStats($pdo) {
    return $pdo->query("
        SELECT DATE(created_at) as tgl, COUNT(*) as jml, SUM(total) as total
        FROM transaksi
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY tgl ASC
    ")->fetchAll();
}

function getRecentTransaksi($pdo, $limit = 8) {
    return $pdo->query("
        SELECT t.*, u.nama as kasir
        FROM transaksi t
        JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC
        LIMIT $limit
    ")->fetchAll();
}

function getTransaksiByPeriod($pdo, $dari, $sampai) {
    $stmt = $pdo->prepare("
        SELECT t.*, u.nama as kasir
        FROM transaksi t
        JOIN users u ON t.user_id = u.id
        WHERE DATE(t.created_at) BETWEEN ? AND ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$dari, $sampai]);
    return $stmt->fetchAll();
}

function getTransaksiById($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT t.*, u.nama as kasir
        FROM transaksi t
        JOIN users u ON t.user_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getDetailTransaksi($pdo, $transaksi_id) {
    $stmt = $pdo->prepare("SELECT * FROM transaksi_detail WHERE transaksi_id = ?");
    $stmt->execute([$transaksi_id]);
    return $stmt->fetchAll();
}

function createTransaksi($pdo, $items, $bayar, $metode, $user_id) {
    $total = 0;
    foreach ($items as $item) $total += $item['harga'] * $item['qty'];

    $kode      = generateKode();
    $kembalian = $bayar - $total;

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO transaksi (kode_transaksi,user_id,total,bayar,kembalian,metode_bayar) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$kode, $user_id, $total, $bayar, $kembalian, $metode]);
    $trx_id = $pdo->lastInsertId();

    $stmtD = $pdo->prepare("INSERT INTO transaksi_detail (transaksi_id,produk_id,nama_produk,harga,qty,subtotal) VALUES (?,?,?,?,?,?)");
    $stmtS = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");

    foreach ($items as $item) {
        $subtotal = $item['harga'] * $item['qty'];
        $stmtD->execute([$trx_id, $item['id'], $item['nama'], $item['harga'], $item['qty'], $subtotal]);
        $stmtS->execute([$item['qty'], $item['id']]);
    }

    $pdo->commit();

    return ['kode' => $kode, 'total' => $total, 'bayar' => $bayar, 'kembalian' => $kembalian, 'trx_id' => $trx_id];
}