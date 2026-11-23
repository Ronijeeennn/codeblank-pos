<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Customers<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Customers</h1><p>The regulars you can attach to a sale.</p></div>
    <a class="btn" href="<?= site_url('customers/new') ?>"><?= icon('plus') ?>Add customer</a>
</div>

<div class="toolbar">
    <form class="search" method="get" action="<?= site_url('customers') ?>" role="search">
        <?= icon('search', 16) ?>
        <input type="search" name="q" value="<?= esc($search, 'attr') ?>" placeholder="Search name or email" aria-label="Search customers">
    </form>
</div>

<?php if (! $customers): ?>
    <div class="panel empty">
        <?= icon('users', 40) ?>
        <h3><?= $search !== '' ? 'No customers match' : 'No customers yet' ?></h3>
        <p><?= $search !== '' ? 'Try a different search.' : 'Add a customer to link them to a sale.' ?></p>
        <?php if ($search === ''): ?><a class="btn" href="<?= site_url('customers/new') ?>"><?= icon('plus') ?>Add customer</a><?php endif ?>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Customer</th><th>Phone</th><th>Added</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($customers as $c): ?>
                <tr>
                    <td><div class="cell"><?= view('partials/avatar', ['file' => null, 'name' => $c['full_name']]) ?><span><?= esc($c['full_name']) ?><small><?= esc($c['email']) ?></small></span></div></td>
                    <td><?= esc($c['phone'] ?? '') ?: '<span class="muted">-</span>' ?></td>
                    <td class="muted"><?= esc(date('M j, Y', strtotime($c['created_at']))) ?></td>
                    <td>
                        <div class="actions end">
                            <a class="btn ghost sm" href="<?= site_url("customers/{$c['id']}/edit") ?>"><?= icon('edit', 15) ?>Edit</a>
                            <form method="post" action="<?= site_url("customers/{$c['id']}/delete") ?>" data-confirm="Delete <?= esc($c['full_name'], 'attr') ?>? Their past purchases stay in the sales history.">
                                <?= csrf_field() ?>
                                <button class="btn danger sm" type="submit" aria-label="Delete <?= esc($c['full_name'], 'attr') ?>"><?= icon('trash', 15) ?>Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?php if ($pager->getPageCount() > 1) echo $pager->only(['q'])->links() ?>
<?= $this->endSection() ?>
