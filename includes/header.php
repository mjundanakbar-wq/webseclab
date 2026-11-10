<?php
if (!defined('WEBSECLAB_LOADED')) {
    require_once __DIR__ . '/../config/database.php';
}

$level  = security_level();
$active = $active ?? '';
$page_title = $page_title ?? 'WebSecLab';

$levelBadge = [
    'low'        => 'bg-red-500/20 text-red-300 ring-red-500/40',
    'medium'     => 'bg-orange-500/20 text-orange-300 ring-orange-500/40',
    'high'       => 'bg-yellow-500/20 text-yellow-200 ring-yellow-500/40',
    'impossible' => 'bg-green-500/20 text-green-300 ring-green-500/40',
];

$modules = [
    'brute_force'       => '1. Brute Force',
    'command_injection' => '2. Command Injection',
    'csrf'              => '3. CSRF',
    'file_upload'       => '4. File Upload',
    'lfi'               => '5. File Inclusion (LFI)',
    'sql_injection'     => '6. SQL Injection',
    'xss_reflected'     => '7. XSS (Reflected)',
    'xss_stored'        => '8. XSS (Stored)',
];

function nav_link(string $href, string $label, bool $isActive): void
{
    $cls = $isActive
        ? 'bg-indigo-600 text-white'
        : 'text-slate-300 hover:bg-slate-800 hover:text-white';
    echo '<a href="' . e($href) . '" class="block rounded-lg px-3 py-2 text-sm font-medium ' . $cls . '">' . e($label) . '</a>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title) ?> · WebSecLab</title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/app.css">
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

<div id="overlay" class="fixed inset-0 z-30 hidden bg-black/40 lg:hidden" onclick="toggleSidebar()"></div>

<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-slate-900 text-slate-200 transition-transform lg:translate-x-0">
    <div class="px-5 py-5">
        <a href="<?= e(BASE_URL) ?>/index.php" class="text-xl font-bold tracking-tight text-white">
            Web<span class="text-indigo-400">SecLab</span>
        </a>
        <p class="mt-0.5 text-xs text-slate-500">Vulnerable Web Application Lab</p>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-4">
        <?php nav_link(BASE_URL . '/index.php', 'Dashboard', $active === 'dashboard'); ?>

        <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">📚 Edukasi</p>
        <?php foreach ($modules as $key => $label): ?>
            <?php nav_link(BASE_URL . '/edukasi.php?m=' . $key, $label, $active === 'edukasi_' . $key); ?>
        <?php endforeach; ?>

        <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Modul Kerentanan (Praktik)</p>
        <?php foreach ($modules as $key => $label): ?>
            <?php nav_link(BASE_URL . '/modules/' . $key . '.php', $label, $active === $key); ?>
        <?php endforeach; ?>

        <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Pengaturan</p>
        <?php nav_link(BASE_URL . '/security.php', 'Security Level', $active === 'security'); ?>
    </nav>

    <div class="border-t border-slate-800 p-4">
        <div class="mb-3 flex items-center justify-between">
            <span class="text-xs text-slate-400">Security Level</span>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase ring-1 ring-inset <?= e($levelBadge[$level]) ?>">
                <?= e($level) ?>
            </span>
        </div>
        <div class="flex items-center justify-between">
            <span class="truncate text-sm text-slate-300"><?= e($_SESSION['username'] ?? 'guest') ?></span>
            <a href="<?= e(BASE_URL) ?>/logout.php" class="text-xs font-medium text-indigo-300 hover:text-white">Logout</a>
        </div>
    </div>
</aside>

<div class="flex min-h-screen flex-col lg:ml-64">
    <div class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:hidden">
        <button type="button" onclick="toggleSidebar()" class="rounded-md p-1.5 text-slate-600 hover:bg-slate-100" aria-label="Menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <span class="font-semibold text-slate-900">Web<span class="text-indigo-600">SecLab</span></span>
        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold uppercase text-slate-600"><?= e($level) ?></span>
    </div>

    <main class="mx-auto w-full max-w-5xl flex-1 p-4 sm:p-8">
