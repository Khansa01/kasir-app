<?php
function getAllKategori($pdo) {
    return $pdo->query("SELECT * FROM kategori ORDER BY nama")->fetchAll();
}