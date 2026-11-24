<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Sales history<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Sales history</h1><p>Every transaction, newest first.</p></div>
    <a class="btn accent" href="<?= site_url('sales/new') ?>"><?= icon('plus') ?>Record sale</a>
</div>

<div class="tiles">
    <div class="tile-link"><span class="ico"><?= icon('trend', 22) ?></span><span><b><?= money($revenue) ?></b><span class="l">Total sales</span></span></div>
    <div class="tile-link"><span class="ico"><?= icon('receipt', 22) ?></span><span><b><?= (int) $count ?></b><span class="l"><?= $count === 1 ? 'Transaction' : 'Transactions' ?></span></span></div>
    <div class="tile-link"><span class="ico"><?= icon('history', 22) ?></span><span><b><?= money($average) ?></b><span class="l">Average sale</span></span></div>
</div>

<?php if (! $sales): ?>
    <div class="panel empty">
        <?= icon('receipt', 40) ?>
        <h3>No sales yet</h3>
        <p>Sales you record will show up here.</p>
        <a class="btn accent" href="<?= site_url('sales/new') ?>"><?= icon('plus') ?>Record the first sale</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Sale</th><th>Date</th><th>Product</th><th>Customer</th><th>Sold by</th><th class="num">Qty</th><th class="num">Total</th></tr></thead>
            <tbody>
            <?php $prev = null; foreach ($sales as $s): ?>
                <?php $cont = $prev && $prev['created_at'] === $s['created_at'] && $prev['sold_by'] === $s['sold_by'] && $prev['customer_id'] === $s['customer_id']; $prev = $s; ?>
                <tr<?= $cont ? ' class="cont"' : '' ?>>
                    <td class="mono muted">#<?= (int) $s['id'] ?></td>
                    <td><?php if ($cont): ?><small class="muted">same sale</small><?php else: ?><?= esc(date('M j, Y', strtotime($s['created_at']))) ?><small class="muted" style="display:block"><?= esc(date('g:i A', strtotime($s['created_at']))) ?></small><?php endif ?></td>
                    <td><div class="cell"><?= view('partials/thumb', ['file' => $s['product_image'], 'name' => $s['product_name']]) ?><?= esc($s['product_name']) ?></div></td>
                    <td><?= $s['customer_name'] ? esc($s['customer_name']) : '<span class="muted">Walk-in</span>' ?></td>
                    <td><div class="cell"><?= view('partials/avatar', ['file' => $s['staff_avatar'], 'name' => $s['staff_name']]) ?><?= esc($s['staff_name']) ?></div></td>
                    <td class="num"><?= (int) $s['quantity'] ?></td>
                    <td class="num money"><?= money($s['total_price']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?php if ($pager->getPageCount() > 1) echo $pager->links() ?>
<?= $this->endSection() ?>
