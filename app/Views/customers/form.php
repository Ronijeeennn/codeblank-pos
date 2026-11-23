<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?><?= $customer ? 'Edit customer' : 'Add customer' ?><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1><?= $customer ? 'Edit customer' : 'Add customer' ?></h1><p><?= $customer ? esc($customer['full_name']) : 'Only name and email are required.' ?></p></div>
</div>

<form class="panel form-card single" method="post" action="<?= $customer ? site_url("customers/{$customer['id']}/update") : site_url('customers') ?>">
    <?= csrf_field() ?>
    <div class="fields">
        <div>
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name" maxlength="100" required value="<?= old_value('full_name', $customer['full_name'] ?? '') ?>">
            <?= validation_show_error('full_name') ?>
        </div>
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="100" required value="<?= old_value('email', $customer['email'] ?? '') ?>">
            <?= validation_show_error('email') ?>
        </div>
        <div>
            <label for="phone">Phone <span class="muted" style="font-weight:400">(optional)</span></label>
            <input type="text" id="phone" name="phone" maxlength="20" inputmode="tel" value="<?= old_value('phone', $customer['phone'] ?? '') ?>">
            <?= validation_show_error('phone') ?>
        </div>
        <div class="form-actions">
            <button class="btn" type="submit"><?= $customer ? 'Save changes' : 'Add customer' ?></button>
            <a class="btn ghost" href="<?= site_url('customers') ?>">Cancel</a>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
