<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?><?= $product ? 'Edit product' : 'Add product' ?><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1><?= $product ? 'Edit product' : 'Add product' ?></h1><p><?= $product ? esc($product['name']) : 'Name it, price it, stock it.' ?></p></div>
</div>

<form class="panel form-card" method="post" enctype="multipart/form-data"
      action="<?= $product ? site_url("products/{$product['id']}/update") : site_url('products') ?>">
    <?= csrf_field() ?>
    <div class="fields">
        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" maxlength="100" required value="<?= old_value('name', $product['name'] ?? '') ?>">
            <?= validation_show_error('name') ?>
        </div>
        <div class="row">
            <div>
                <label for="price">Price (PHP)</label>
                <input type="text" inputmode="decimal" id="price" name="price" required placeholder="0.00" value="<?= old_value('price', isset($product) ? number_format((float) $product['price'], 2, '.', '') : '') ?>">
                <?= validation_show_error('price') ?>
            </div>
            <div>
                <label for="stock_quantity">Stock on hand</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" step="1" required value="<?= old_value('stock_quantity', $product['stock_quantity'] ?? '0') ?>">
                <?= validation_show_error('stock_quantity') ?>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn" type="submit"><?= $product ? 'Save changes' : 'Add product' ?></button>
            <a class="btn ghost" href="<?= site_url('products') ?>">Cancel</a>
        </div>
    </div>

    <div class="form-side">
        <label for="image">Product image</label>
        <div class="preview" id="image-preview">
            <?php if ($product && $product['image']): ?>
                <img src="<?= esc(upload_url('products', $product['image']), 'attr') ?>" alt="Current product image">
            <?php else: ?>
                <span><?= icon('image', 32) ?><br>No image yet</span>
            <?php endif ?>
        </div>
        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-preview="#image-preview">
        <p class="hint">JPG, PNG or WebP, up to 2 MB. It's cropped to a square.</p>
        <?= validation_show_error('image') ?>
    </div>
</form>
<?= $this->endSection() ?>
