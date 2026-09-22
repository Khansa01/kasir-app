<?php
session_start();
$base_url = '../';
$page_title = 'Transaksi';
require_once '../config/db.php';
require_once '../models/ProdukModel.php';
require_once '../models/KategoriModel.php';
require_once '../models/TransaksiModel.php';

// Handle AJAX dulu SEBELUM include header
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bayar') {
    header('Content-Type: application/json');
    
    // Cek session manual karena ga lewat header.php
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['ok' => false, 'msg' => 'Session expired, silakan login ulang']);
        exit;
    }

    $items  = json_decode($_POST['items'], true);
    $bayar  = floatval($_POST['bayar']);
    $metode = clean($_POST['metode']);

    if (empty($items)) { echo json_encode(['ok'=>false,'msg'=>'Keranjang kosong']); exit; }

    $total = 0;
    foreach ($items as $item) $total += $item['harga'] * $item['qty'];

    if ($metode === 'tunai' && $bayar < $total) {
        echo json_encode(['ok'=>false,'msg'=>'Uang bayar kurang']); exit;
    }

    try {
        $result = createTransaksi($pdo, $items, $bayar, $metode, $_SESSION['user_id']);
        echo json_encode(['ok'=>true] + $result);
    } catch (Exception $e) {
        echo json_encode(['ok'=>false,'msg'=>'Error: '.$e->getMessage()]);
    }
    exit;
}

require_once '../includes/header.php';

$produk   = getProdukTersedia($pdo);
$kategori = getAllKategori($pdo);
?>

<div class="kasir-layout">
    <!-- Kiri: Produk -->
    <div>
        <div class="toolbar">
            <div class="search-box" style="flex:1">
                <span class="search-icon">🔍</span>
                <input class="form-control" id="cariProduk" placeholder="Cari produk...">
            </div>
            <select class="form-control" id="filterKat" style="width:150px">
                <option value="">Semua</option>
                <?php foreach ($kategori as $k): ?>
                <option value="<?= $k['id'] ?>"><?= clean($k['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="product-grid" id="produkGrid">
            <?php foreach ($produk as $p): ?>
            <div class="product-card"
                 data-id="<?= $p['id'] ?>"
                 data-nama="<?= clean($p['nama']) ?>"
                 data-harga="<?= $p['harga'] ?>"
                 data-stok="<?= $p['stok'] ?>"
                 data-kat="<?= $p['kategori_id'] ?>"
                 onclick="tambahItem(this)">
                <div class="product-card-icon">📦</div>
                <div class="product-card-name"><?= clean($p['nama']) ?></div>
                <div class="product-card-price"><?= rupiah($p['harga']) ?></div>
                <div class="product-card-stock">Stok: <?= $p['stok'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Kanan: Keranjang -->
    <div class="cart-panel">
        <div class="cart-header">
            <span>🛒 Keranjang</span>
            <button class="btn btn-ghost btn-sm" onclick="clearCart()">Kosongkan</button>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="cart-empty" id="cartEmpty">Pilih produk di sebelah kiri</div>
        </div>

        <div class="cart-footer">
            <div class="total-row"><span>Subtotal</span><span id="subtotalTxt">Rp 0</span></div>
            <div class="total-row grand"><span>TOTAL</span><span id="totalTxt">Rp 0</span></div>

            <div class="form-group" style="margin:0.75rem 0">
                <label class="form-label">Metode Bayar</label>
                <select class="form-control" id="metodeBayar" onchange="toggleBayar()">
                    <option value="tunai">💵 Tunai</option>
                    <option value="transfer">🏦 Transfer</option>
                    <option value="qris">📱 QRIS</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:0.75rem" id="bayarGroup">
                <label class="form-label">Uang Bayar (Rp)</label>
                <input class="form-control" type="number" id="inputBayar" placeholder="0" oninput="hitungKembalian()">
            </div>

            <div class="total-row" id="kembalianRow" style="display:none">
                <span>Kembalian</span>
                <span id="kembalianTxt" style="color:var(--green);font-weight:700">Rp 0</span>
            </div>

            <button class="btn btn-success" style="width:100%;margin-top:0.75rem;font-size:0.95rem" onclick="prosesBayar()">
                Bayar Sekarang
            </button>
        </div>
    </div>
</div>

<!-- Modal Struk -->
<div class="modal-overlay" id="modalStruk">
    <div class="modal" style="max-width:360px">
        <div class="modal-header">
            <span class="modal-title">✅ Transaksi Berhasil</span>
        </div>
        <div id="strukContent"></div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="printStruk()">🖨️ Print</button>
            <button class="btn btn-primary" onclick="selesai()">Transaksi Baru</button>
        </div>
    </div>
</div>

<script>
let cart = {};
const fmt = n => 'Rp ' + Number(n).toLocaleString('id-ID');

function tambahItem(el) {
    const id    = el.dataset.id;
    const nama  = el.dataset.nama;
    const harga = parseFloat(el.dataset.harga);
    const stok  = parseInt(el.dataset.stok);
    if (!cart[id]) {
        if (stok < 1) return alert('Stok habis!');
        cart[id] = { id, nama, harga, qty: 0, stok };
    }
    if (cart[id].qty >= cart[id].stok) return alert('Stok tidak cukup!');
    cart[id].qty++;
    renderCart();
}

function ubahQty(id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) delete cart[id];
    renderCart();
}

function clearCart() { cart = {}; renderCart(); }

function renderCart() {
    const items   = Object.values(cart);
    const isEmpty = items.length === 0;
    const el      = document.getElementById('cartItems');
    const empty   = document.getElementById('cartEmpty');

    empty.style.display = isEmpty ? 'block' : 'none';
    el.querySelectorAll('.cart-item').forEach(e => e.remove());

    let total = 0;
    items.forEach(item => {
        total += item.harga * item.qty;
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <div style="flex:1">
                <div class="cart-item-name">${item.nama}</div>
                <div class="cart-item-price">${fmt(item.harga)} × ${item.qty} = ${fmt(item.harga * item.qty)}</div>
            </div>
            <div class="qty-ctrl">
                <button class="qty-btn" onclick="ubahQty('${item.id}',-1)">−</button>
                <span class="qty-num">${item.qty}</span>
                <button class="qty-btn" onclick="ubahQty('${item.id}',1)">+</button>
            </div>`;
        el.appendChild(div);
    });

    document.getElementById('subtotalTxt').textContent = fmt(total);
    document.getElementById('totalTxt').textContent    = fmt(total);
    hitungKembalian();
}

function toggleBayar() {
    const metode = document.getElementById('metodeBayar').value;
    document.getElementById('bayarGroup').style.display = metode === 'tunai' ? '' : 'none';
    document.getElementById('kembalianRow').style.display = 'none';
}

function hitungKembalian() {
    const items = Object.values(cart);
    const total = items.reduce((s,i) => s + i.harga * i.qty, 0);
    const bayar = parseFloat(document.getElementById('inputBayar').value) || 0;
    const kembalian = bayar - total;
    const row = document.getElementById('kembalianRow');
    if (bayar > 0) {
        row.style.display = 'flex';
        document.getElementById('kembalianTxt').textContent = fmt(kembalian >= 0 ? kembalian : 0);
        document.getElementById('kembalianTxt').style.color = kembalian >= 0 ? 'var(--green)' : 'var(--red)';
    } else {
        row.style.display = 'none';
    }
}

async function prosesBayar() {
    const items = Object.values(cart);
    if (!items.length) return alert('Keranjang masih kosong!');

    const total  = items.reduce((s,i) => s + i.harga * i.qty, 0);
    const metode = document.getElementById('metodeBayar').value;
    const bayar  = metode === 'tunai'
        ? parseFloat(document.getElementById('inputBayar').value) || 0
        : total;

    if (metode === 'tunai' && bayar < total) return alert('Uang bayar kurang!');

    const fd = new FormData();
    fd.append('action', 'bayar');
    fd.append('items', JSON.stringify(items));
    fd.append('bayar', bayar);
    fd.append('metode', metode);

    const res  = await fetch('', { method: 'POST', body: fd });
    const data = await res.json();

    if (!data.ok) return alert('Gagal: ' + data.msg);
    tampilStruk(data, items);
}

function tampilStruk(data, items) {
    const tgl  = new Date().toLocaleString('id-ID');
    let rows   = items.map(i =>
        `${i.nama.substring(0,18).padEnd(18)}  ${i.qty} x ${fmt(i.harga)} = ${fmt(i.harga*i.qty)}`
    ).join('\n');

    document.getElementById('strukContent').innerHTML = `
<pre style="border:1px dashed var(--border);padding:1rem;border-radius:8px;font-size:0.75rem;white-space:pre-wrap;overflow-x:auto">
================================
          KasirKu
================================
${tgl}
Kode : ${data.kode}
--------------------------------
${rows}
--------------------------------
TOTAL     : ${fmt(data.total)}
BAYAR     : ${fmt(data.bayar)}
KEMBALIAN : ${fmt(data.kembalian)}
================================
      Terima kasih! 🙏
================================</pre>`;

    document.getElementById('modalStruk').classList.add('show');
}

function printStruk() { window.print(); }

function selesai() {
    cart = {};
    renderCart();
    document.getElementById('inputBayar').value = '';
    document.getElementById('modalStruk').classList.remove('show');
}

document.getElementById('cariProduk').addEventListener('input', filterProduk);
document.getElementById('filterKat').addEventListener('change', filterProduk);

function filterProduk() {
    const q   = document.getElementById('cariProduk').value.toLowerCase();
    const kat = document.getElementById('filterKat').value;
    document.querySelectorAll('.product-card').forEach(el => {
        const cocokNama = el.dataset.nama.toLowerCase().includes(q);
        const cocokKat  = !kat || el.dataset.kat === kat;
        el.style.display = cocokNama && cocokKat ? '' : 'none';
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>