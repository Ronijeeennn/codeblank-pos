<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Record sale<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Record sale</h1><p>Tap a product, set the quantity, and record it.</p></div>
</div>

<div class="sale-layout">
    <form id="sale-form" method="post" action="<?= site_url('sales') ?>">
        <?= csrf_field() ?>

        <fieldset class="step">
            <legend>1. Choose a product</legend>
            <div class="picker-tools">
                <div class="search" style="max-width:none">
                    <?= icon('search', 16) ?>
                    <input type="search" id="pick-search" placeholder="Filter products" aria-label="Filter products">
                </div>
            </div>
            <div class="picker" id="picker">
                <?php foreach ($products as $p): ?>
                    <?php $stock = (int) $p['stock_quantity']; ?>
                    <label class="pick" data-name="<?= esc(mb_strtolower($p['name']), 'attr') ?>">
                        <input type="radio" name="product_id" value="<?= (int) $p['id'] ?>" required
                               data-name="<?= esc($p['name'], 'attr') ?>" data-price="<?= esc($p['price'], 'attr') ?>" data-stock="<?= $stock ?>"
                               <?= (string) old('product_id') === (string) $p['id'] ? 'checked' : '' ?>
                               <?= $stock === 0 ? 'disabled' : '' ?>>
                        <span class="card">
                            <span class="pic" style="<?= empty($p['image']) ? tint($p['name']) : '' ?>">
                                <?php if (! empty($p['image'])): ?>
                                    <img src="<?= esc(upload_url('products', $p['image']), 'attr') ?>" alt="" loading="lazy">
                                <?php else: ?>
                                    <?= esc(initials($p['name'])) ?>
                                <?php endif ?>
                                <span class="tick"><?= icon('check', 16) ?></span>
                            </span>
                            <span class="meta">
                                <span class="nm"><?= esc($p['name']) ?></span>
                                <span class="pr"><?= money($p['price']) ?></span>
                                <span class="st"><?= $stock === 0 ? 'Out of stock' : $stock . ' in stock' ?></span>
                            </span>
                        </span>
                    </label>
                <?php endforeach ?>
            </div>
            <p class="muted" id="pick-none" hidden style="margin-top:.8rem">No products match that filter.</p>
            <?php if (! $products): ?><p class="muted">No products yet. <a href="<?= site_url('products/new') ?>">Add one first.</a></p><?php endif ?>
            <?= validation_show_error('product_id') ?>
        </fieldset>

        <div class="step">
            <label for="customer_id">2. Customer <span class="muted" style="font-family:var(--sans);font-size:.95rem;font-weight:400">(optional)</span></label>
            <select id="customer_id" name="customer_id" style="max-width:420px">
                <option value="">Walk-in, no customer</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" data-name="<?= esc($c['full_name'], 'attr') ?>" <?= (string) old('customer_id') === (string) $c['id'] ? 'selected' : '' ?>>
                        <?= esc($c['full_name']) ?> (<?= esc($c['email']) ?>)
                    </option>
                <?php endforeach ?>
            </select>
            <?= validation_show_error('customer_id') ?>
        </div>

        <div class="step">
            <label for="quantity">3. Quantity</label>
            <div class="stepper">
                <button type="button" data-step="-1" aria-label="Decrease quantity"><?= icon('minus', 18) ?></button>
                <input type="number" id="quantity" name="quantity" min="1" step="1" required value="<?= old_value('quantity', '1') ?>">
                <button type="button" data-step="1" aria-label="Increase quantity"><?= icon('plus', 18) ?></button>
            </div>
            <?= validation_show_error('quantity') ?>
        </div>

        <button class="btn accent lg" type="submit"><?= icon('check') ?>Record sale</button>
    </form>

    <aside class="receipt" aria-live="polite">
        <div class="head"><b>Code Blank</b><span>Sale preview</span></div>
        <p id="r-empty" class="idle">Choose a product to start the receipt.</p>
        <div id="r-body" hidden>
            <dl>
                <dt>Item</dt><dd id="r-item"></dd>
                <dt>Price</dt><dd id="r-price"></dd>
                <dt>Qty</dt><dd id="r-qty"></dd>
                <dt>Customer</dt><dd id="r-customer"></dd>
                <dt>Cashier</dt><dd><?= esc(session('full_name')) ?></dd>
            </dl>
            <div class="total"><span>Total</span><b id="r-total"></b></div>
            <p class="note" id="r-note"></p>
        </div>
    </aside>
</div>

<script>
(function () {
    var form = document.getElementById('sale-form'), customer = document.getElementById('customer_id'), qty = document.getElementById('quantity'),
        body = document.getElementById('r-body'), empty = document.getElementById('r-empty');
    var peso = function (n) { return '\u20B1' + n.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };
    var set = function (id, text) { document.getElementById(id).textContent = text; };

    function render() {
        var picked = form.querySelector('input[name=product_id]:checked');
        if (!picked) { body.hidden = true; empty.hidden = false; return; }
        var price = parseFloat(picked.dataset.price), stock = parseInt(picked.dataset.stock, 10), n = parseInt(qty.value, 10) || 0;
        body.hidden = false; empty.hidden = true;
        set('r-item', picked.dataset.name); set('r-price', peso(price)); set('r-qty', n);
        set('r-customer', customer.value ? customer.options[customer.selectedIndex].dataset.name : 'Walk-in');
        set('r-total', peso(price * Math.max(n, 0)));
        var note = document.getElementById('r-note');
        if (n > stock) { note.textContent = 'Only ' + stock + ' in stock. The sale will be rejected.'; note.className = 'note bad'; }
        else { note.textContent = n > 0 ? (stock - n) + ' will be left in stock.' : ''; note.className = 'note'; }
    }

    form.addEventListener('input', render);
    form.addEventListener('change', render);
    form.querySelectorAll('[data-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            qty.value = Math.max(1, (parseInt(qty.value, 10) || 0) + parseInt(btn.dataset.step, 10));
            render();
        });
    });

    document.getElementById('pick-search').addEventListener('input', function (e) {
        var term = e.target.value.trim().toLowerCase(), shown = 0;
        document.querySelectorAll('#picker .pick').forEach(function (el) {
            var hit = el.dataset.name.indexOf(term) !== -1;
            el.hidden = !hit; if (hit) shown++;
        });
        document.getElementById('pick-none').hidden = shown > 0;
    });
    render();
})();
</script>
<?= $this->endSection() ?>
