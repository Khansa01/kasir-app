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
        <div class="toolbar" style="margin-bottom:0.75rem">
            <div class="search-box" style="flex:1">
                <i data-lucide="search" class="search-icon" style="width:16px;height:16px"></i>
                <input class="form-control" id="cariProduk" placeholder="Cari produk...">
            </div>
        </div>

        <div class="cat-tabs" id="catTabs">
            <button class="cat-tab active" data-kat="" onclick="filterKategori(this)">Semua</button>
            <?php foreach ($kategori as $k): ?>
            <button class="cat-tab" data-kat="<?= $k['id'] ?>" onclick="filterKategori(this)">
                <?= clean($k['nama']) ?>
            </button>
            <?php endforeach; ?>
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
                <div class="product-card-icon">
                    <i data-lucide="package"></i>
                </div>
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
            <div style="display:flex;align-items:center;gap:0.5rem">
                <i data-lucide="shopping-cart" style="width:18px;height:18px"></i>
                <span>Pesanan</span>
            </div>
            <button class="btn btn-ghost" onclick="clearCart()">
                <i data-lucide="trash-2" style="width:16px;height:16px"></i>
                Kosongkan
            </button>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="cart-empty" id="cartEmpty">
                <i data-lucide="shopping-bag" style="width:36px;height:36px;color:var(--border);margin-bottom:0.75rem"></i>
                <p style="font-weight:500;margin-bottom:0.25rem">Keranjang kosong</p>
                <p style="font-size:0.78rem">Pilih produk di sebelah kiri</p>
            </div>
        </div>

        <div class="cart-footer">
            <div class="total-row" style="font-size:0.8rem">
                <span style="color:var(--muted)">Subtotal</span>
                <span id="subtotalTxt">Rp 0</span>
            </div>
            <div class="total-row" style="font-size:0.8rem;margin-bottom:0.75rem">
                <span style="color:var(--muted)">Pajak (0%)</span>
                <span>Rp 0</span>
            </div>
            <div class="total-row grand">
                <span>Total</span>
                <span id="totalTxt">Rp 0</span>
            </div>

            <div class="payment-methods">
                <button class="payment-method active" id="pm-tunai" onclick="setPM('tunai',this)">
                    <i data-lucide="banknote" style="width:16px;height:16px"></i>
                    Tunai
                </button>
                <button class="payment-method" id="pm-transfer" onclick="setPM('transfer',this)">
                    <i data-lucide="landmark" style="width:16px;height:16px"></i>
                    Transfer
                </button>
                <button class="payment-method" id="pm-qris" onclick="setPM('qris',this)">
                    <i data-lucide="qr-code" style="width:16px;height:16px"></i>
                    QRIS
                </button>
            </div>

            <div id="bayarGroup" style="margin-bottom:0.75rem">
                <label class="form-label">Uang Bayar (Rp)</label>
                <input class="form-control" type="number" id="inputBayar" placeholder="0" oninput="hitungKembalian()">
            </div>

            <div class="total-row" id="kembalianRow" style="display:none;margin-bottom:0.75rem">
                <span style="color:var(--muted)">Kembalian</span>
                <span id="kembalianTxt" style="color:var(--accent);font-weight:700">Rp 0</span>
            </div>

            <button class="btn btn-success" style="width:100%;font-size:0.95rem;padding:0.85rem;justify-content:center" onclick="prosesBayar()">
                <i data-lucide="credit-card" style="width:16px;height:16px"></i>
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
    const metode = selectedMetode;
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

let lastTrxId = 0;

function tampilStruk(data, items) {
    lastTrxId = data.trx_id;
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

function printStruk() {
    window.open('../pages/struk.php?id=' + lastTrxId, '_blank');
}

function selesai() {
    cart = {};
    renderCart();
    document.getElementById('inputBayar').value = '';
    document.getElementById('modalStruk').classList.remove('show');
}

document.getElementById('cariProduk').addEventListener('input', function() {
    const aktifKat = document.querySelector('.cat-tab.active')?.dataset.kat || '';
    filterProdukAll(aktifKat, this.value.toLowerCase());
});

function filterKategori(el) {
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    const kat = el.dataset.kat;
    const q   = document.getElementById('cariProduk').value.toLowerCase();
    filterProdukAll(kat, q);
}

function filterProdukAll(kat = '', q = '') {
    document.querySelectorAll('.product-card').forEach(el => {
        const cocokNama = el.dataset.nama.toLowerCase().includes(q);
        const cocokKat  = !kat || el.dataset.kat === kat;
        el.style.display = cocokNama && cocokKat ? '' : 'none';
    });
}

let selectedMetode = 'tunai';

function setPM(metode, el) {
    selectedMetode = metode;
    document.querySelectorAll('.payment-method').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('bayarGroup').style.display = metode === 'tunai' ? '' : 'none';
    document.getElementById('kembalianRow').style.display = 'none';
    document.getElementById('inputBayar').value = '';
}

</script>

<?php require_once '../includes/footer.php'; ?>