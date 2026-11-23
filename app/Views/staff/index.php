<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Staff<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Staff</h1><p>Everyone who can sign in and record sales.</p></div>
    <a class="btn" href="<?= site_url('staff/new') ?>"><?= icon('plus') ?>Add staff member</a>
</div>

<div class="sgrid">
<?php foreach ($staff as $m): ?>
    <?php $isMe = (int) $m['id'] === (int) session('user_id'); ?>
    <article class="scard">
        <?= view('partials/avatar', ['file' => $m['avatar'], 'name' => $m['full_name'], 'class' => 'avatar lg']) ?>
        <h3><?= esc($m['full_name']) ?></h3>
        <p class="muted mono">@<?= esc($m['username']) ?></p>
        <p><?= $isMe ? '<span class="badge ok">You</span>' : '<span class="muted" style="font-size:.88rem">Joined ' . esc(date('M j, Y', strtotime($m['created_at']))) . '</span>' ?></p>
        <div class="foot">
            <a class="btn ghost sm" href="<?= site_url("staff/{$m['id']}/edit") ?>"><?= icon('edit', 15) ?>Edit</a>
            <?php if (! $isMe): ?>
                <form method="post" action="<?= site_url("staff/{$m['id']}/delete") ?>" data-confirm="Delete the account for <?= esc($m['full_name'], 'attr') ?>?">
                    <?= csrf_field() ?>
                    <button class="btn danger sm" type="submit" aria-label="Delete <?= esc($m['full_name'], 'attr') ?>"><?= icon('trash', 15) ?>Delete</button>
                </form>
            <?php endif ?>
        </div>
    </article>
<?php endforeach ?>
</div>
<?php if ($pager->getPageCount() > 1) echo $pager->links() ?>
<?= $this->endSection() ?>
