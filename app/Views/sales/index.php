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

<?php if (! $checkouts): ?>
    <div class="panel empty">
        <?= icon('receipt', 40) ?>
        <h3>No sales yet</h3>
        <p>Sales you record will show up here.</p>
        <a class="btn accent" href="<?= site_url('sales/new') ?>"><?= icon('plus') ?>Record the first sale</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Sale</th><th>Date</th><th>Products</th><th>Customer</th><th>Sold by</th><th class="num">Qty</th><th class="num">Total</th></tr></thead>
            <tbody>
            <?php foreach ($checkouts as $c): ?>
                <?php $multi = (int) $c['line_count'] > 1; ?>
                <tr>
                    <td class="mono muted">
                        #<?= (int) $c['first_id'] ?><?= $multi ? '&ndash;' . (int) $c['last_id'] : '' ?>
                        <?php if ($multi): ?><small style="display:block;font-family:var(--sans)"><?= (int) $c['line_count'] ?> products</small><?php endif ?>
                    </td>
                    <td><?= esc(date('M j, Y', strtotime($c['created_at']))) ?><small class="muted" style="display:block"><?= esc(date('g:i A', strtotime($c['created_at']))) ?></small></td>
                    <td>
                        <div class="plist">
                            <?php foreach ($c['lines'] as $l): ?>
                                <div class="cell">
                                    <?= view('partials/thumb', ['file' => $l['product_image'], 'name' => $l['product_name'], 'class' => $multi ? 'thumb sm' : 'thumb']) ?>
                                    <span><?= esc($l['product_name']) ?> <span class="muted">&times; <?= (int) $l['quantity'] ?></span></span>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </td>
                    <td><?= $c['customer_name'] ? esc($c['customer_name']) : '<span class="muted">Walk-in</span>' ?></td>
                    <td><div class="cell"><?= view('partials/avatar', ['file' => $c['staff_avatar'], 'name' => $c['staff_name']]) ?><?= esc($c['staff_name']) ?></div></td>
                    <td class="num"><?= (int) $c['units'] ?></td>
                    <td class="num money"><?= money($c['total']) ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?php if ($pager->getPageCount() > 1) echo $pager->links() ?>
<?= $this->endSection() ?>
