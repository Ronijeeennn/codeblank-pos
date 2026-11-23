<?php /** Product image, or coloured initials when there is none. @var ?string $file @var string $name @var string $class */ ?>
<?php $class = $class ?? 'thumb'; ?>
<?php if (! empty($file)): ?>
    <img class="<?= esc($class, 'attr') ?>" src="<?= esc(upload_url('products', $file), 'attr') ?>" alt="<?= esc($name, 'attr') ?>">
<?php else: ?>
    <span class="<?= esc($class, 'attr') ?>" style="<?= tint($name) ?>" aria-hidden="true"><?= esc(initials($name)) ?></span>
<?php endif ?>
