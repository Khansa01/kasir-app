<?php
function getAllProduk($pdo) {
    return $pdo->query("
        SELECT p.*, k.nama as kat
        FROM produk p
        LEFT JOIN kategori k ON p.kategori_id = k.id
        ORDER BY p.nama
    ")->fetchAll();
}

function getProdukTersedia($pdo) {
    return $pdo->query("SELECT * FROM produk WHERE stok > 0 ORDER BY nama")->fetchAll();
}

function getProdukById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getTotalProduk($pdo) {
    return $pdo->query("SELECT COUNT(*) as jml FROM produk")->fetch()['jml'];
}

function getStokMinim($pdo) {
    return $pdo->query("SELECT COUNT(*) as jml FROM produk WHERE stok <= 5")->fetch()['jml'];
}

function createProduk($pdo, $nama, $kode, $harga, $stok, $satuan, $kategori_id) {
    $stmt = $pdo->prepare("INSERT INTO produk (nama,kode,harga,stok,satuan,kategori_id) VALUES (?,?,?,?,?,?)");
    return $stmt->execute([$nama, $kode, $harga, $stok, $satuan, $kategori_id ?: null]);
}

function updateProduk($pdo, $id, $nama, $kode, $harga, $stok, $satuan, $kategori_id) {
    $stmt = $pdo->prepare("UPDATE produk SET nama=?, kode=?, harga=?, stok=?, satuan=?, kategori_id=? WHERE id=?");
    return $stmt->execute([$nama, $kode, $harga, $stok, $satuan, $kategori_id ?: null, $id]);
}

function deleteProduk($pdo, $id) {
    return $pdo->prepare("DELETE FROM produk WHERE id=?")->execute([$id]);
}