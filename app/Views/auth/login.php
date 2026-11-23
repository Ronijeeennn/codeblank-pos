<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in - Tindahan POS</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23ffb400'/%3E%3Cpath d='M6 12l2-6h16l2 6a3.5 3.5 0 0 1-7 0 3.5 3.5 0 0 1-7 0 3.5 3.5 0 0 1-7 0z M8 16h16v10H8z' fill='%23121b3f'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=IBM+Plex+Mono:wght@400;500&family=Instrument+Sans:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
<div class="auth">
    <section class="auth-brand">
        <div class="awning" aria-hidden="true"></div>
        <div class="inner">
            <div class="logo"><?= icon('store', 28) ?></div>
            <h1>Ring up the sale. Keep the shelf honest.</h1>
            <p>Tindahan POS tracks every sale against your stock, so what's on screen is what's on the shelf.</p>
            <ul>
                <li><?= icon('receipt') ?>Record a sale in a few taps</li>
                <li><?= icon('box') ?>Stock drops automatically, and never below zero</li>
                <li><?= icon('trend') ?>See the week's sales at a glance</li>
            </ul>
        </div>
    </section>

    <section class="auth-form">
        <div class="box">
            <h2>Welcome back</h2>
            <p class="muted">Sign in with your staff account.</p>

            <?php if (service('request')->getGet('signed_out')): ?>
                <div class="flash success" role="status" style="margin:1.2rem 0 0"><?= icon('check') ?><span>You've been signed out.</span></div>
            <?php endif ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="flash error" role="alert" style="margin:1.2rem 0 0"><?= icon('alert') ?><span><?= esc(session()->getFlashdata('error')) ?></span></div>
            <?php endif ?>

            <form method="post" action="<?= site_url('login') ?>">
                <?= csrf_field() ?>
                <div>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" autocomplete="username" required autofocus
                           value="<?= esc((string) (session()->getFlashdata('last_username') ?? old('username', null, false) ?? ''), 'attr') ?>">
                    <?= validation_show_error('username') ?>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                    <?= validation_show_error('password') ?>
                </div>
                <button class="btn lg block" type="submit">Sign in</button>
            </form>
        </div>
    </section>
</div>
</body>
</html>
