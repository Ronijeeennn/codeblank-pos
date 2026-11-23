<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Products<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Products</h1><p>What's on the shelf, and how much is left.</p></div>
    <a class="btn" href="<?= site_url('products/new') ?>"><?= icon('plus') ?>Add product</a>
</div>

<div class="toolbar">
    <form class="search" method="get" action="<?= site_url('products') ?>" role="search">
        <?php if ($filter): ?><input type="hidden" name="stock" value="<?= esc($filter, 'attr') ?>"><?php endif ?>
        <?= icon('search', 16) ?>
        <input type="search" name="q" value="<?= esc($search, 'attr') ?>" placeholder="Search products" aria-label="Search products">
    </form>
    <nav class="chips" aria-label="Filter by stock">
        <?php $q = $search !== '' ? '&q=' . rawurlencode($search) : ''; ?>
        <a href="<?= site_url('products') . ($q ? '?' . ltrim($q, '&') : '') ?>"<?= $filter === '' ? ' aria-current="true"' : '' ?>>All</a>
        <a href="<?= site_url('products') . '?stock=low' . $q ?>"<?= $filter === 'low' ? ' aria-current="true"' : '' ?>>Running low</a>
        <a href="<?= site_url('products') . '?stock=out' . $q ?>"<?= $filter === 'out' ? ' aria-current="true"' : '' ?>>Out of stock</a>
    </nav>
</div>

<?php if (! $products): ?>
    <div class="panel empty">
        <?= icon('box', 40) ?>
        <h3><?= ($search !== '' || $filter) ? 'No products match' : 'No products yet' ?></h3>
        <p><?= ($search !== '' || $filter) ? 'Try a different search or filter.' : 'Add your first product to start selling.' ?></p>
        <?php if ($search === '' && ! $filter): ?><a class="btn" href="<?= site_url('products/new') ?>"><?= icon('plus') ?>Add product</a><?php endif ?>
    </div>
<?php else: ?>
    <div class="pgrid">
    <?php foreach ($products as $p): ?>
        <?php
        $stock = (int) $p['stock_quantity'];
        $state = $stock === 0 ? 'out' : ($stock <= 5 ? 'low' : '');
        ?>
        <article class="pcard">
            <div class="pic" style="<?= empty($p['image']) ? tint($p['name']) : '' ?>">
                <?php if (! empty($p['image'])): ?>
                    <img src="<?= esc(upload_url('products', $p['image']), 'attr') ?>" alt="<?= esc($p['name'], 'attr') ?>" loading="lazy">
                <?php else: ?>
                    <span aria-hidden="true"><?= esc(initials($p['name'])) ?></span>
                <?php endif ?>
                <?php if ($state): ?><span class="badge flag <?= $state ?>"><?= $state === 'out' ? 'Out of stock' : 'Running low' ?></span><?php endif ?>
            </div>
            <div class="body">
                <h3 title="<?= esc($p['name'], 'attr') ?>"><?= esc($p['name']) ?></h3>
                <p class="price"><?= money($p['price']) ?></p>
                <div class="meter <?= $state ?>"><i style="width: <?= min(100, $stock * 2) ?>%"></i></div>
                <p class="stock"><?= $stock ?> in stock</p>
            </div>
            <div class="foot">
                <a class="btn ghost sm" href="<?= site_url("products/{$p['id']}/edit") ?>"><?= icon('edit', 15) ?>Edit</a>
                <form method="post" action="<?= site_url("products/{$p['id']}/delete") ?>" data-confirm="Delete <?= esc($p['name'], 'attr') ?>? This can't be undone.">
                    <?= csrf_field() ?>
                    <button class="btn danger sm" type="submit" aria-label="Delete <?= esc($p['name'], 'attr') ?>"><?= icon('trash', 15) ?>Delete</button>
                </form>
            </div>
        </article>
    <?php endforeach ?>
    </div>
<?php endif ?>
<?php if ($pager->getPageCount() > 1) echo $pager->only(['q', 'stock'])->links() ?>
<?= $this->endSection() ?>
