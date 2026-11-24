<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$hour      = (int) date('G');
$greeting  = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$firstName = explode(' ', trim((string) session('full_name')))[0];
$max       = max(1, ...array_column($daily, 'total'));
$topMax    = max(1, ...(array_column($topProducts, 'units') ?: [1]));
?>
<section class="hero">
    <div class="awning" aria-hidden="true"></div>
    <div>
        <p class="hello"><?= esc($greeting) ?>, <?= esc($firstName) ?> &middot; <?= esc(date('l, F j')) ?></p>
        <p class="big"><?= money($today['total']) ?></p>
        <p class="sub">sold today across <?= (int) $today['count'] ?> <?= $today['count'] === 1 ? 'sale' : 'sales' ?></p>
        <a class="btn accent lg" href="<?= site_url('sales/new') ?>"><?= icon('plus') ?>Record a sale</a>
    </div>
    <dl>
        <div><dt>Last 7 days</dt><dd><?= money($weekTotal) ?></dd></div>
        <div><dt>All time</dt><dd><?= money($revenue) ?></dd></div>
        <div><dt>Transactions</dt><dd><?= (int) $salesCount ?></dd></div>
    </dl>
</section>

<div class="tiles">
    <a class="tile-link" href="<?= site_url('products') ?>"><span class="ico"><?= icon('box', 22) ?></span><span><b><?= (int) $productCount ?></b><span class="l">Products</span></span></a>
    <a class="tile-link" href="<?= site_url('customers') ?>"><span class="ico"><?= icon('users', 22) ?></span><span><b><?= (int) $customerCount ?></b><span class="l">Customers</span></span></a>
    <a class="tile-link" href="<?= site_url('staff') ?>"><span class="ico"><?= icon('badge', 22) ?></span><span><b><?= (int) $staffCount ?></b><span class="l">Staff accounts</span></span></a>
</div>

<div class="grid-2">
    <section class="panel chart">
        <h2><?= icon('trend') ?>Sales, last 7 days</h2>
        <?php
        $summary = implode(', ', array_map(static fn ($d) => date('D', strtotime($d['date'])) . ' ' . money($d['total']), $daily));
        ?>
        <svg viewBox="0 0 560 210" role="img" aria-label="Daily sales for the last 7 days: <?= esc($summary, 'attr') ?>">
            <line x1="10" y1="168" x2="550" y2="168" stroke="#e0e5f1" stroke-width="2"/>
            <?php foreach ($daily as $i => $d): ?>
                <?php
                $h       = $d['total'] > 0 ? max(6, $d['total'] / $max * 128) : 0;
                $x       = 18 + $i * 76;
                $isToday = $i === count($daily) - 1;
                ?>
                <g>
                    <title><?= esc(date('D, M j', strtotime($d['date'])) . ': ' . money($d['total'])) ?></title>
                    <rect x="<?= $x ?>" y="<?= 168 - $h ?>" width="56" height="<?= $h ?>" rx="8" fill="<?= $isToday ? '#ffb400' : '#c5cffa' ?>"/>
                    <?php if ($d['total'] > 0): ?>
                        <text class="val" x="<?= $x + 28 ?>" y="<?= 168 - $h - 8 ?>" text-anchor="middle"><?= esc('₱' . number_format($d['total'], 0)) ?></text>
                    <?php endif ?>
                    <text x="<?= $x + 28 ?>" y="192" text-anchor="middle"><?= $isToday ? 'Today' : esc(date('D', strtotime($d['date']))) ?></text>
                </g>
            <?php endforeach ?>
        </svg>
    </section>

    <section class="panel">
        <h2><?= icon('box') ?>Top sellers</h2>
        <?php if (! $topProducts): ?>
            <p class="muted">Top sellers appear after your first sale.</p>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($topProducts as $p): ?>
                    <li>
                        <?= view('partials/thumb', ['file' => $p['image'], 'name' => $p['name']]) ?>
                        <div class="grow">
                            <div class="t"><?= esc($p['name']) ?></div>
                            <div class="bar"><i style="width: <?= round($p['units'] / $topMax * 100) ?>%"></i></div>
                        </div>
                        <span class="muted"><?= (int) $p['units'] ?> sold</span>
                    </li>
                <?php endforeach ?>
            </ul>
        <?php endif ?>
    </section>
</div>

<div class="grid-2 even">
    <section class="panel">
        <h2><?= icon('alert') ?>Running low</h2>
        <?php if (! $lowStock): ?>
            <p class="muted">Every product has more than 5 in stock.</p>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($lowStock as $p): ?>
                    <?php $n = (int) $p['stock_quantity']; ?>
                    <li>
                        <?= view('partials/thumb', ['file' => $p['image'], 'name' => $p['name']]) ?>
                        <div class="grow"><a class="t" style="display:block;color:inherit;text-decoration:none" href="<?= site_url("products/{$p['id']}/edit") ?>"><?= esc($p['name']) ?></a></div>
                        <span class="badge <?= $n === 0 ? 'out' : 'low' ?>"><?= $n === 0 ? 'Out of stock' : $n . ' left' ?></span>
                    </li>
                <?php endforeach ?>
            </ul>
        <?php endif ?>
    </section>

    <section class="panel">
        <h2><?= icon('history') ?>Latest sales</h2>
        <?php if (! $recentSales): ?>
            <p class="muted">No sales yet. <a href="<?= site_url('sales/new') ?>">Record the first one.</a></p>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($recentSales as $s): ?>
                    <?php $first = $s['lines'][0]; $more = count($s['lines']) - 1; ?>
                    <li>
                        <?= view('partials/thumb', ['file' => $first['product_image'], 'name' => $first['product_name']]) ?>
                        <div class="grow">
                            <div class="t"><?= (int) $first['quantity'] ?> &times; <?= esc($first['product_name']) ?><?= $more > 0 ? ' <span class="muted">+ ' . $more . ' more</span>' : '' ?></div>
                            <small class="muted"><?= esc(fmt_date($s['created_at'])) ?></small>
                        </div>
                        <span class="money"><?= money($s['total']) ?></span>
                    </li>
                <?php endforeach ?>
            </ul>
        <?php endif ?>
    </section>
</div>
<?= $this->endSection() ?>
