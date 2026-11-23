<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?><?= $member ? 'Edit staff member' : 'Add staff member' ?><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1><?= $member ? 'Edit staff member' : 'Add staff member' ?></h1><p><?= $member ? esc($member['full_name']) : 'They can sign in right away.' ?></p></div>
</div>

<form class="panel form-card" method="post" enctype="multipart/form-data"
      action="<?= $member ? site_url("staff/{$member['id']}/update") : site_url('staff') ?>">
    <?= csrf_field() ?>
    <div class="fields">
        <div class="row">
            <div>
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" maxlength="100" required value="<?= old_value('full_name', $member['full_name'] ?? '') ?>">
                <?= validation_show_error('full_name') ?>
            </div>
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" maxlength="50" required autocomplete="off" value="<?= old_value('username', $member['username'] ?? '') ?>">
                <?= validation_show_error('username') ?>
            </div>
        </div>
        <div class="row">
            <div>
                <label for="password"><?= $member ? 'New password' : 'Password' ?></label>
                <input type="password" id="password" name="password" autocomplete="new-password" <?= $member ? '' : 'required' ?>>
                <p class="hint"><?= $member ? 'Leave blank to keep the current password.' : 'At least 8 characters.' ?></p>
                <?= validation_show_error('password') ?>
            </div>
            <div>
                <label for="password_confirm">Confirm password</label>
                <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password">
                <?= validation_show_error('password_confirm') ?>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn" type="submit"><?= $member ? 'Save changes' : 'Create account' ?></button>
            <a class="btn ghost" href="<?= site_url('staff') ?>">Cancel</a>
        </div>
    </div>

    <div class="form-side">
        <label for="avatar">Avatar</label>
        <div class="preview round" id="avatar-preview">
            <?php if ($member && $member['avatar']): ?>
                <img src="<?= esc(upload_url('avatars', $member['avatar']), 'attr') ?>" alt="Current avatar">
            <?php else: ?>
                <span><?= icon('image', 28) ?><br>No photo</span>
            <?php endif ?>
        </div>
        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp" data-preview="#avatar-preview">
        <p class="hint">JPG, PNG or WebP, up to 2 MB. Cropped to a square.</p>
        <?= validation_show_error('avatar') ?>
    </div>
</form>
<?= $this->endSection() ?>
