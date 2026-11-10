<?php
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Login aplikasi itu sendiri dibuat aman (prepared statement) agar lab tetap stabil.
    $row = find_user($conn, $username);
    if ($row && verify_password($password, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = (int) $row['user_id'];
        $_SESSION['username'] = $row['username'];
        header('Location: index.php');
        exit;
    }
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · WebSecLab</title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/app.css">
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-900 px-4">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-white">Web<span class="text-indigo-400">SecLab</span></h1>
            <p class="mt-1 text-sm text-slate-400">Modern Vulnerable Web Application Platform</p>
        </div>

        <form method="post" class="rounded-2xl bg-white p-6 shadow-xl">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Masuk</h2>

            <?php if ($error): ?>
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"><?= e($error) ?></div>
            <?php endif; ?>

            <label class="mb-1 block text-sm font-medium text-slate-700" for="username">Username</label>
            <input id="username" name="username" type="text" required autofocus
                   class="mb-4 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">

            <label class="mb-1 block text-sm font-medium text-slate-700" for="password">Password</label>
            <input id="password" name="password" type="password" required
                   class="mb-5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">

            <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Login</button>

            <p class="mt-4 text-center text-xs text-slate-500">Akun default: <code class="rounded bg-slate-100 px-1">admin</code> / <code class="rounded bg-slate-100 px-1">password</code></p>
        </form>
    </div>
</body>
</html>
