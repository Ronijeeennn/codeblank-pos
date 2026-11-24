<?php
$nav = [
    ['dashboard', 'Dashboard', 'dashboard', 'dashboard'],
    ['sales', 'Sales history', 'history', 'sales'],
    'Manage',
    ['products', 'Products', 'box', 'products*'],
    ['customers', 'Customers', 'users', 'customers*'],
    ['staff', 'Staff', 'badge', 'staff*'],
];
$firstName = explode(' ', trim((string) session('full_name')))[0];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#121b3f">
    <title><?= $this->renderSection('title') ?> - Code Blank POS</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23ffb400'/%3E%3Cpath d='M6 12l2-6h16l2 6a3.5 3.5 0 0 1-7 0 3.5 3.5 0 0 1-7 0 3.5 3.5 0 0 1-7 0z M8 16h16v10H8z' fill='%23121b3f'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=IBM+Plex+Mono:wght@400;500&family=Instrument+Sans:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=<?= (int) @filemtime(FCPATH . 'assets/css/app.css') ?>"><!-- ?v= forces browsers to reload the stylesheet after each update -->
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="awning" aria-hidden="true"></div>
        <a class="brand" href="<?= site_url('dashboard') ?>"><span class="logo"><?= icon('store', 20) ?></span>Code Blank</a>
        <a class="btn accent cta" href="<?= site_url('sales/new') ?>"<?= url_is('sales/new') ? ' aria-current="page"' : '' ?>><?= icon('plus', 18) ?>Record sale</a>
        <nav class="nav" aria-label="Main">
            <?php foreach ($nav as $item): ?>
                <?php if (is_string($item)): ?>
                    <div class="group"><?= esc($item) ?></div>
                <?php else: ?>
                    <?php $active = $item[3] === 'sales' ? (url_is('sales') || url_is('sales/')) : url_is($item[3]); ?>
                    <a href="<?= site_url($item[0]) ?>"<?= $active ? ' aria-current="page"' : '' ?>><?= icon($item[2]) ?><?= esc($item[1]) ?></a>
                <?php endif ?>
            <?php endforeach ?>
        </nav>
        <div class="me">
            <?= view('partials/avatar', ['file' => session('avatar'), 'name' => session('full_name') ?? '?']) ?>
            <div class="name"><?= esc(session('full_name')) ?><small>@<?= esc(session('username')) ?></small></div>
            <form method="post" action="<?= site_url('logout') ?>">
                <?= csrf_field() ?>
                <button class="icon-btn" type="submit" title="Sign out" aria-label="Sign out"><?= icon('logout', 18) ?></button>
            </form>
        </div>
    </aside>
    <main class="main">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="flash success" role="status"><?= icon('check') ?><span><?= esc(session()->getFlashdata('success')) ?></span></div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="flash error" role="alert"><?= icon('alert') ?><span><?= esc(session()->getFlashdata('error')) ?></span></div>
        <?php endif ?>
        <?= $this->renderSection('content') ?>
    </main>
</div>
<script>
// Delete buttons: any form with data-confirm asks first.
document.addEventListener('submit', function (e) {
    var msg = e.target.dataset.confirm;
    if (msg && !confirm(msg)) e.preventDefault();
});
// File inputs with data-preview show the chosen image straight away.
document.addEventListener('change', function (e) {
    var input = e.target;
    if (input.type !== 'file' || !input.dataset.preview) return;
    var file = input.files[0], box = document.querySelector(input.dataset.preview);
    if (!file || !box || file.type.indexOf('image/') !== 0) return;
    var img = document.createElement('img');
    img.src = URL.createObjectURL(file);
    img.alt = 'Preview of the selected image';
    box.replaceChildren(img);
});
</script>
</body>
</html>
