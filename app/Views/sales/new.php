<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Record sale<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-head">
    <div><h1>Record sale</h1><p>Tap products to add them to the sale, then adjust the quantities.</p></div>
</div>

<form class="sale-layout" id="sale-form" method="post" action="<?= site_url('sales') ?>">
    <?= csrf_field() ?>

    <div>
        <fieldset class="step">
            <legend>1. Choose products</legend>
            <div class="picker-tools">
                <div class="search" style="max-width:none">
                    <?= icon('search', 16) ?>
                    <input type="search" id="pick-search" placeholder="Filter products" aria-label="Filter products">
                </div>
            </div>
            <div class="picker" id="picker">
                <?php foreach ($products as $p): ?>
                    <?php $stock = (int) $p['stock_quantity']; ?>
                    <div class="pick<?= $stock === 0 ? ' off' : '' ?>" data-name="<?= esc(mb_strtolower($p['name']), 'attr') ?>"
                         data-label="<?= esc($p['name'], 'attr') ?>" data-price="<?= esc($p['price'], 'attr') ?>" data-stock="<?= $stock ?>">
                        <button type="button" class="card" data-inc <?= $stock === 0 ? 'disabled' : '' ?> aria-label="Add <?= esc($p['name'], 'attr') ?>">
                            <span class="pic" style="<?= empty($p['image']) ? tint($p['name']) : '' ?>">
                                <?php if (! empty($p['image'])): ?>
                                    <img src="<?= esc(upload_url('products', $p['image']), 'attr') ?>" alt="" loading="lazy">
                                <?php else: ?>
                                    <?= esc(initials($p['name'])) ?>
                                <?php endif ?>
                                <span class="count" aria-hidden="true">0</span>
                            </span>
                            <span class="meta">
                                <span class="nm"><?= esc($p['name']) ?></span>
                                <span class="pr"><?= money($p['price']) ?></span>
                                <span class="st"><?= $stock === 0 ? 'Out of stock' : $stock . ' in stock' ?></span>
                            </span>
                        </button>
                        <div class="mini" role="group" aria-label="Quantity of <?= esc($p['name'], 'attr') ?>">
                            <button type="button" data-dec aria-label="Decrease" <?= $stock === 0 ? 'disabled' : '' ?>><?= icon('minus', 16) ?></button>
                            <input type="number" name="qty[<?= (int) $p['id'] ?>]" min="0" step="1" inputmode="numeric"
                                   value="<?= old_value('qty.' . $p['id'], '0') ?>" aria-label="Quantity of <?= esc($p['name'], 'attr') ?>" <?= $stock === 0 ? 'disabled' : '' ?>>
                            <button type="button" data-inc aria-label="Increase" <?= $stock === 0 ? 'disabled' : '' ?>><?= icon('plus', 16) ?></button>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
            <p class="muted" id="pick-none" hidden style="margin-top:.8rem">No products match that filter.</p>
            <?php if (! $products): ?><p class="muted">No products yet. <a href="<?= site_url('products/new') ?>">Add one first.</a></p><?php endif ?>
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
    </div>

    <aside class="receipt" aria-live="polite">
        <div class="head"><b>Code Blank</b><span>Sale preview</span></div>
        <p id="r-empty" class="idle">Tap a product to start the sale.</p>
        <div id="r-body" hidden>
            <div id="r-lines"></div>
            <dl>
                <dt>Customer</dt><dd id="r-customer"></dd>
                <dt>Cashier</dt><dd><?= esc(session('full_name')) ?></dd>
            </dl>
            <div class="total"><span>Total</span><b id="r-total"></b></div>
            <p class="note" id="r-note"></p>
        </div>
        <button class="btn accent lg block" type="submit" id="r-submit" style="margin-top:1rem"><?= icon('check') ?>Record sale</button>
    </aside>
</form>

<script>
(function () {
    var form = document.getElementById('sale-form'), customer = document.getElementById('customer_id'),
        body = document.getElementById('r-body'), empty = document.getElementById('r-empty'), lines = document.getElementById('r-lines'),
        submit = document.getElementById('r-submit'), tiles = [].slice.call(form.querySelectorAll('.pick'));
    var peso = function (n) { return '\u20B1' + n.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };
    var qtyInput = function (tile) { return tile.querySelector('input[type=number]'); };
    var qtyOf = function (tile) { var n = parseInt(qtyInput(tile).value, 10); return isNaN(n) || n < 0 ? 0 : n; };
    var el = function (tag, cls, text) { var e = document.createElement(tag); if (cls) e.className = cls; if (text !== undefined) e.textContent = text; return e; };

    function render() {
        var total = 0, count = 0, over = 0;
        lines.replaceChildren();

        tiles.forEach(function (tile) {
            var n = qtyOf(tile), price = parseFloat(tile.dataset.price), stock = parseInt(tile.dataset.stock, 10);
            tile.classList.toggle('on', n > 0);
            tile.querySelector('.count').textContent = n;
            if (!n) return;

            count++; total += price * n;
            var bad = n > stock; if (bad) over++;
            var row = el('div', 'line' + (bad ? ' bad' : ''));
            var left = el('div');
            left.appendChild(el('span', 'ln', tile.dataset.label));
            left.appendChild(el('small', '', n + ' \u00D7 ' + peso(price) + (bad ? ' (only ' + stock + ' in stock)' : '')));
            row.appendChild(left);
            row.appendChild(el('span', 'amt', peso(price * n)));
            lines.appendChild(row);
        });

        empty.hidden = count > 0; body.hidden = count === 0; submit.disabled = count === 0;
        document.getElementById('r-customer').textContent = customer.value ? customer.options[customer.selectedIndex].dataset.name : 'Walk-in';
        document.getElementById('r-total').textContent = peso(total);
        var note = document.getElementById('r-note');
        note.textContent = over ? 'Not enough stock for ' + over + (over === 1 ? ' item' : ' items') + '. The sale will be rejected.' : (count > 1 ? count + ' products in this sale.' : '');
        note.className = 'note' + (over ? ' bad' : '');
    }

    form.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-inc], [data-dec]');
        if (!btn || btn.disabled) return;
        var tile = btn.closest('.pick'), input = qtyInput(tile);
        input.value = Math.max(0, qtyOf(tile) + (btn.hasAttribute('data-inc') ? 1 : -1));
        render();
    });
    form.addEventListener('input', render);
    customer.addEventListener('change', render);

    document.getElementById('pick-search').addEventListener('input', function (e) {
        var term = e.target.value.trim().toLowerCase(), shown = 0;
        tiles.forEach(function (t) { var hit = t.dataset.name.indexOf(term) !== -1; t.hidden = !hit; if (hit) shown++; });
        document.getElementById('pick-none').hidden = shown > 0;
    });
    render();
})();
</script>
<?= $this->endSection() ?>
