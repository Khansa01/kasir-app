<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../pages/dashboard.php');
    exit;
}
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: ../pages/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Semua field wajib diisi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — KasirKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #0f1117;
            --surface: #1a1d27;
            --border: #2a2d3e;
            --accent: #6c63ff;
            --accent-hover: #574fd6;
            --text: #e8e9f0;
            --muted: #7c7f94;
            --error: #ff5c5c;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-wrap { width: 100%; max-width: 380px; padding: 1.5rem; }
        .brand { text-align: center; margin-bottom: 2rem; }
        .brand-icon {
            width: 52px; height: 52px;
            background: var(--accent);
            border-radius: 14px;
            display: inline-flex;
            align-items: center; justify-content: center;
            font-size: 1.5rem; margin-bottom: 0.75rem;
        }
        .brand h1 { font-size: 1.4rem; font-weight: 700; letter-spacing: -0.02em; }
        .brand p { color: var(--muted); font-size: 0.85rem; margin-top: 0.25rem; }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
        }
        .form-group { margin-bottom: 1.25rem; }
        label {
            display: block; font-size: 0.8rem; font-weight: 500;
            color: var(--muted); margin-bottom: 0.5rem;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        input {
            width: 100%; padding: 0.75rem 1rem;
            background: var(--bg); border: 1px solid var(--border);
            border-radius: 10px; color: var(--text);
            font-size: 0.95rem; font-family: inherit;
            transition: border-color 0.2s;
        }
        input:focus { outline: none; border-color: var(--accent); }
        .btn {
            width: 100%; padding: 0.85rem;
            background: var(--accent); color: #fff;
            border: none; border-radius: 10px;
            font-size: 0.95rem; font-weight: 600;
            font-family: inherit; cursor: pointer;
            transition: background 0.2s; margin-top: 0.5rem;
        }
        .btn:hover { background: var(--accent-hover); }
        .alert-error {
            background: rgba(255,92,92,0.1);
            border: 1px solid rgba(255,92,92,0.3);
            color: var(--error); border-radius: 10px;
            padding: 0.75rem 1rem; font-size: 0.875rem;
            margin-bottom: 1.25rem;
        }
        .hint { text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: var(--muted); }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="brand">
            <div class="brand-icon">🧾</div>
            <h1>KasirKu</h1>
            <p>Sistem Kasir Digital</p>
        </div>
        <div class="card">
            <?php if ($error): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Masukkan username"
                           value="<?= clean($_POST['username'] ?? '') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn">Masuk</button>
            </form>
        </div>
        <p class="hint">Default: admin / password</p>
    </div>
</body>
</html>