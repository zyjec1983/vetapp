<?php
/**
 * Location: vetapp/app/views/sales/create.php
 */
$title = 'Nueva Venta | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-cart-plus me-2"></i>Nueva Venta</h2>
                <a href="<?= BASE_URL ?>sales.php" class="btn btn-secondary">Volver</a>
            </div>

            <div class="row">
                <form id="saleForm" method="POST" action="<?= BASE_URL ?>sales.php?action=store">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-4">

                                <div class="card-header bg-primary text-white">Información General</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="mb-3">
                                            <label class="form-label">Cliente / Mascota</label>

                                            <div class="input-group">
                                                <input type="text" id="clientSearch" class="form-control"
                                                    placeholder="Buscar cliente o mascota...">
                                                <button class="btn btn-primary" type="button" id="clientSearchBtn">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                            </div>

                                            <!-- Resultados -->
                                            <div id="clientResults" class="list-group mt-2"
                                                style="max-height: 250px; overflow-y: auto;"></div>

                                            <!-- Seleccionado -->
                                            <div id="selectedClient" class="mt-2"></div>

                                            <!-- ID real -->
                                            <input type="hidden" name="id_client" id="clientId">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Observaciones</label>
                                        <textarea name="observations" class="form-control" rows="2"></textarea>
                                    </div>
                                    <hr>

                                    <!-- Buscador de medicamentos -->
                                    <div class="mb-3">
                                        <label class="form-label text-success">Buscar Medicamento</label>
                                        <div class="input-group">
                                            <input type="text" id="medSearch" class="form-control"
                                                placeholder="Código o nombre...">
                                            <button class="btn btn-success" type="button" id="searchBtn"><i
                                                    class="bi bi-search"></i></button>
                                        </div>
                                        <div id="searchResults" class="list-group mt-2"
                                            style="max-height: 300px; overflow-y: auto;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-white fw-bold">Detalle de la Venta</div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover m-0">
                                            <thead class="table-dark">
                                                <th>Producto</th>
                                                <th width="100">Cant.</th>
                                                <th width="130">P.Unitario</th>
                                                <th width="130">Total</th>
                                                <th width="80">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody id="cartItems"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row justify-content-end">
                                <div class="col-md-5">
                                    <div class="card shadow-sm border-primary">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Subtotal:</span>
                                                <span id="subtotal">$0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2 align-items-center">
                                                <span>Descuento (%):</span>
                                                <input type="number" id="discountPercent"
                                                    class="form-control form-control-sm w-25 text-end" value="0"
                                                    step="0.01">
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>IVA (15%):</span>
                                                <span id="iva">$0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
    <span>Exento de IVA:</span>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="ivaExemptSwitch">
        <label class="form-check-label" for="ivaExemptSwitch"></label>
    </div>
</div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <h4 class="fw-bold text-primary">Total a Pagar:</h4>
                                                <h4 class="fw-bold text-primary" id="total">$0.00</h4>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label">Método de Pago</label>
                                                <select name="payment_method" class="form-select">
                                                    <option value="cash">Efectivo</option>
                                                    <option value="card">Tarjeta</option>
                                                    <option value="transfer">Transferencia</option>
                                                    <option value="credit">Crédito</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 d-flex justify-content-center gap-3">
                                        <a href="<?= BASE_URL ?>sales.php"
                                            class="btn btn-outline-danger px-4 py-2 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-x-circle me-1"></i> Cancelar
                                        </a>

                                        <button type="submit"
                                            class="btn btn-primary px-4 py-2 d-flex align-items-center justify-content-center"
                                            id="submitSale">
                                            <i class="bi bi-check-lg me-1"></i> Registrar Venta
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="cart" id="cartData" value="">
                    <input type="hidden" name="subtotal" id="subtotalInput">
                    <input type="hidden" name="tax_total" id="taxTotalInput">
                    <input type="hidden" name="total" id="totalInput">
                    <input type="hidden" name="discount" id="discountInput">
                </form>
            </div>
        </main>
    </div>
</div>

<script>
    let cart = [];
    let searchTimeout;
    let clientTimeout;

    // =========================
    // MEDICATION SEARCH
    // =========================
    const searchInput = document.getElementById('medSearch');
    const searchBtn = document.getElementById('searchBtn');
    const resultsDiv = document.getElementById('searchResults');

    function debounceSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(searchMedications, 300);
    }

    function searchMedications() {
        const q = searchInput.value.trim();

        if (q.length < 2) {
            resultsDiv.innerHTML = '';
            return;
        }

        fetch(`<?= BASE_URL ?>sales.php?action=searchMedications&q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                resultsDiv.innerHTML = '';

                if (data.length === 0) {
                    resultsDiv.innerHTML = '<div class="list-group-item text-muted">No se encontraron medicamentos.</div>';
                    return;
                }

                data.forEach(med => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';

                    btn.innerHTML = `
                        <div>
                            <strong>${med.name}</strong><br>
                            <small>${med.code}</small><br>
                            <small class="text-${med.stock < 5 ? 'danger' : 'muted'}">
                                Stock: ${med.stock} | $${med.sale_price}
                            </small>
                        </div>
                        <i class="bi bi-plus-circle text-success fs-5"></i>
                    `;

                    btn.addEventListener('click', () => addToCart(med));
                    resultsDiv.appendChild(btn);
                });
            })
            .catch(err => {
                console.error(err);
                resultsDiv.innerHTML = '<div class="list-group-item text-danger">Error al buscar medicamentos.</div>';
            });
    }

    // =========================
    // CART LOGIC
    // =========================
    function addToCart(med) {
        const existing = cart.find(item => item.id_medication === med.id_medication);

        if (existing) {
            if (existing.quantity + 1 > med.stock) {
                Swal.fire('Stock insuficiente', 'No hay suficiente stock disponible.', 'warning');
                return;
            }
            existing.quantity++;
        } else {
            cart.push({
                id_medication: med.id_medication,
                name: med.name,
                unit_price: parseFloat(med.sale_price),
                quantity: 1,
                stock: med.stock,
                taxable: med.taxable
            });
        }

        renderCart();
    }

    function renderCart() {
        const tbody = document.getElementById('cartItems');
        tbody.innerHTML = '';

        let subtotal = 0;

        cart.forEach((item, index) => {
            const lineTotal = item.unit_price * item.quantity;
            subtotal += lineTotal;

            tbody.innerHTML += `
                <tr>
                    <td>${item.name}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-center"
                            value="${item.quantity}" min="1"
                            onchange="updateQty(${index}, this.value)">
                    </td>
                    <td>$${item.unit_price.toFixed(2)}</td>
                    <td class="fw-bold">$${lineTotal.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm"
                            onclick="removeItem(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        calculateTotals(subtotal);
    }

    function updateQty(index, newQty) {
        newQty = parseInt(newQty);

        if (isNaN(newQty) || newQty < 1) newQty = 1;

        if (newQty > cart[index].stock) {
            Swal.fire('Stock insuficiente', 'Cantidad supera el stock disponible.', 'warning');
            newQty = cart[index].stock;
        }

        cart[index].quantity = newQty;
        renderCart();
    }

    function removeItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    // =========================
    // TOTALS
    // =========================
    function calculateTotals(subtotal) {
    const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
    const discountAmount = subtotal * (discountPercent / 100);
    const base = subtotal - discountAmount;
    
    // Verificar si el switch de exención de IVA está activado
    const isExempt = document.getElementById('ivaExemptSwitch').checked;
    let iva = 0;
    
    if (!isExempt) {
        let taxableBase = 0;
        cart.forEach(item => {
            if (item.taxable) {
                taxableBase += item.unit_price * item.quantity;
            }
        });
        const taxableDiscounted = taxableBase * (1 - discountPercent / 100);
        iva = taxableDiscounted * 0.15;
    }
    
    const total = base + iva;
    
    document.getElementById('subtotal').innerText = `$${subtotal.toFixed(2)}`;
    document.getElementById('iva').innerText = `$${iva.toFixed(2)}`;
    document.getElementById('total').innerText = `$${total.toFixed(2)}`;
    
    document.getElementById('subtotalInput').value = subtotal.toFixed(2);
    document.getElementById('taxTotalInput').value = iva.toFixed(2);
    document.getElementById('totalInput').value = total.toFixed(2);
    document.getElementById('discountInput').value = discountAmount.toFixed(2);
}
    document.getElementById('discountPercent').addEventListener('input', () => {
        if (cart.length) {
            let subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
            calculateTotals(subtotal);
        }
    });

    document.getElementById('ivaExemptSwitch').addEventListener('change', () => {
    if (cart.length) {
        let subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
        calculateTotals(subtotal);
    }
});

    // =========================
    // CLIENT SEARCH
    // =========================
    const clientInput = document.getElementById('clientSearch');
    const clientBtn = document.getElementById('clientSearchBtn');
    const clientResults = document.getElementById('clientResults');
    const selectedClientDiv = document.getElementById('selectedClient');
    const clientIdInput = document.getElementById('clientId');

    function debounceClientSearch() {
        clearTimeout(clientTimeout);
        clientTimeout = setTimeout(searchClients, 300);
    }

    function searchClients() {
        const q = clientInput.value.trim();

        if (q.length < 2) {
            clientResults.innerHTML = '';
            return;
        }

        fetch(`<?= BASE_URL ?>sales.php?action=searchClients&q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                clientResults.innerHTML = '';

                if (data.length === 0) {
                    clientResults.innerHTML = '<div class="list-group-item text-muted">Sin resultados</div>';
                    return;
                }

                data.forEach(item => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action';

                    btn.innerHTML = `
                        <strong>${item.client_name}</strong><br>
                        <small>Mascota: ${item.pet_name ? item.pet_name : '—'}</small>
                    `;

                    btn.addEventListener('click', () => selectClient(item));
                    clientResults.appendChild(btn);
                });
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Fallo al buscar clientes', 'error');
            });
    }

    function selectClient(item) {
        clientIdInput.value = item.id_client;

        selectedClientDiv.innerHTML = `
            <div class="alert alert-success p-2">
                <strong>${item.client_name}</strong><br>
                <small>Mascota: ${item.pet_name ? item.pet_name : '—'}</small>
            </div>
        `;

        clientResults.innerHTML = '';
        clientInput.value = '';
    }

    // =========================
    // FORM SUBMIT
    // =========================
    document.getElementById('saleForm').addEventListener('submit', function (e) {

        const clientId = document.getElementById('clientId').value;

        if (!clientId) {
            e.preventDefault();
            Swal.fire('Error', 'Debe seleccionar un cliente.', 'error');
            return false;
        }

        if (cart.length === 0) {
            e.preventDefault();
            Swal.fire('Error', 'Agregue al menos un producto a la venta.', 'error');
            return false;
        }

        const invalid = cart.some(item => item.quantity > item.stock);

        if (invalid) {
            e.preventDefault();
            Swal.fire('Error', 'Hay productos sin stock suficiente.', 'error');
            return false;
        }

        const cartForBackend = cart.map(item => ({
            id_medication: item.id_medication,
            quantity: item.quantity,
            unit_price: item.unit_price
        }));

        document.getElementById('cartData').value = JSON.stringify(cartForBackend);
    });

    // =========================
    // EVENTS
    // =========================
    searchBtn.addEventListener('click', searchMedications);
    searchInput.addEventListener('input', debounceSearch);
    searchInput.addEventListener('keypress', e => { if (e.key === 'Enter') searchMedications(); });

    clientBtn.addEventListener('click', searchClients);
    clientInput.addEventListener('input', debounceClientSearch);
    clientInput.addEventListener('keypress', e => { if (e.key === 'Enter') searchClients(); });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>