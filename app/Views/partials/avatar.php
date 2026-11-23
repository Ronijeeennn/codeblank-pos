<?php /** Round avatar image, or coloured initials when the person has none. @var ?string $file @var string $name @var string $class */ ?>
<?php $class = $class ?? 'avatar'; ?>
<?php if (! empty($file)): ?>
    <img class="<?= esc($class, 'attr') ?>" src="<?= esc(upload_url('avatars', $file), 'attr') ?>" alt="">
<?php else: ?>
    <span class="<?= esc($class, 'attr') ?>" style="<?= tint($name) ?>" aria-hidden="true"><?= esc(initials($name)) ?></span>
<?php endif ?>
