<?php
/**
 * Location: vetapp/app/views/sales/create.php
 */
$title = 'Nueva Venta | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$clientCreated = isset($_GET['client_created']) && $_GET['client_created'] == '1';
$newClientId = $_GET['client_id'] ?? '';
$newClientName = urldecode($_GET['client_name'] ?? '');
$newClientIdentification = urldecode($_GET['client_identification'] ?? '');
?>

<style>
.item-enter {
    animation: slideIn 0.25s ease-out;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateX(-12px); }
    to { opacity: 1; transform: translateX(0); }
}
.client-result:hover {
    background-color: #f0f7ff;
}
.search-highlight em {
    font-style: normal;
    background-color: #fff3cd;
    padding: 0 2px;
    border-radius: 2px;
}
.cart-mobile-card {
    display: none;
}
@media (max-width: 575.98px) {
    .cart-table-desktop { display: none !important; }
    .cart-mobile-card { display: block !important; }
}
</style>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>sales.php"><i class="bi bi-receipt me-1"></i>Ventas</a></li>
                    <li class="breadcrumb-item active">Nueva Venta</li>
                </ol>
            </nav>

            <form id="saleForm" method="POST" action="<?= BASE_URL ?>sales.php?action=store">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <div class="row g-3">
                    <!-- Left column: Cliente + Búsqueda -->
                    <div class="col-lg-4">
                        <!-- Card: Cliente -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-primary text-white d-flex align-items-center gap-2 py-2">
                                <i class="bi bi-person-badge fs-5"></i>
                                <span class="fw-semibold">Información del Cliente</span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-muted">Tipo de Facturación</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="client_type" id="clientTypeRegistered" value="registered" checked>
                                        <label class="btn btn-outline-primary" for="clientTypeRegistered">
                                            <i class="bi bi-person me-1"></i>Registrado
                                        </label>
                                        <input type="radio" class="btn-check" name="client_type" id="clientTypeCF" value="consumidor_final">
                                        <label class="btn btn-outline-warning" for="clientTypeCF">
                                            <i class="bi bi-receipt me-1"></i>Consumidor Final
                                        </label>
                                    </div>
                                </div>

                                <div id="clientSearchSection">
                                    <label class="form-label small fw-semibold text-muted">Buscar Cliente / Mascota</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="clientSearch" class="form-control" placeholder="Nombre o cédula...">
                                        <button class="btn btn-primary" type="button" id="clientSearchBtn">
                                            <i class="bi bi-search"></i>
                                        </button>
                                        <a href="<?= BASE_URL ?>clients.php?action=create" class="btn btn-outline-success" id="btnNewClient" title="Nuevo cliente">
                                            <i class="bi bi-person-plus"></i>
                                        </a>
                                    </div>
                                    <div id="clientResults" class="list-group mt-2" style="max-height:260px;overflow-y:auto"></div>
                                    <div id="selectedClient" class="mt-2"></div>
                                </div>

                                <div id="cfBadgeSection" style="display:none;">
                                    <div class="card border-warning bg-warning-subtle">
                                        <div class="card-body py-2 px-3 text-center">
                                            <i class="bi bi-receipt fs-4 text-warning"></i>
                                            <div class="fw-semibold mt-1">CONSUMIDOR FINAL</div>
                                            <small class="text-muted">RUC: 9999999999999</small>
                                        </div>
                                    </div>
                                </div>

                                <div id="cfLimitAlert" class="alert alert-danger py-2 mt-2" style="display:none;">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    <strong>Límite $150 superado</strong><br>
                                    <small>El SRI exige un máximo de $150 para Consumidor Final. Debe registrar un cliente.</small>
                                </div>

                                <input type="hidden" name="id_client" id="clientId">
                                <input type="hidden" id="cfClientId" value="<?= $cfPlaceholderId ?? '' ?>">

                                <div class="mt-3">
                                    <label class="form-label small fw-semibold text-muted">Observaciones</label>
                                    <textarea name="observations" class="form-control form-control-sm" rows="2" placeholder="Opcional..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Buscar Medicamento -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-success text-white d-flex align-items-center gap-2 py-2">
                                <i class="bi bi-capsule fs-5"></i>
                                <span class="fw-semibold">Buscar Medicamento</span>
                            </div>
                            <div class="card-body">
                                <div class="input-group input-group-sm mb-2">
                                    <input type="text" id="medSearch" class="form-control" placeholder="Código o nombre...">
                                    <button class="btn btn-success" type="button" id="searchBtn">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <div id="searchResults" class="list-group" style="max-height:320px;overflow-y:auto"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right column: Carrito + Totales -->
                    <div class="col-lg-8">
                        <!-- Card: Detalle de Venta -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-dark text-white d-flex align-items-center gap-2 py-2">
                                <i class="bi bi-cart3 fs-5"></i>
                                <span class="fw-semibold">Detalle de la Venta</span>
                                <span class="badge bg-light text-dark ms-auto" id="itemCount">0 items</span>
                            </div>
                            <div class="card-body p-0">
                                <!-- Desktop table -->
                                <div class="table-responsive cart-table-desktop">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th>Producto</th>
                                                <th style="width:90px" class="text-center">Cant.</th>
                                                <th style="width:120px" class="d-none d-sm-table-cell text-end">P.Unitario</th>
                                                <th style="width:120px" class="text-end">Total</th>
                                                <th style="width:60px" class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cartItems"></tbody>
                                    </table>
                                </div>
                                <!-- Mobile cards -->
                                <div id="cartMobileItems" class="cart-mobile-card p-2"></div>
                                <!-- Empty state -->
                                <div id="cartEmpty" class="text-center py-5 text-muted">
                                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                                    <span>El carrito está vacío</span><br>
                                    <small>Busque medicamentos para agregar a la venta</small>
                                </div>
                            </div>
                        </div>

                        <!-- Totales + Botones -->
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="card bg-light border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Subtotal:</span>
                                            <span class="fw-semibold" id="subtotal">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2 align-items-center">
                                            <span class="text-muted">Descuento:</span>
                                            <div class="d-flex align-items-center gap-1">
                                                <input type="number" id="discountPercent"
                                                    class="form-control form-control-sm text-end border-0 bg-white shadow-sm"
                                                    style="width:70px" value="0" step="0.01" min="0" max="100">
                                                <span class="text-muted small">%</span>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">IVA (15%):</span>
                                            <span class="fw-semibold" id="iva">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-muted">Exento de IVA:</span>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="ivaExemptSwitch">
                                                <label class="form-check-label small text-muted" for="ivaExemptSwitch">Activar</label>
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="fw-bold mb-0">Total:</h5>
                                            <h5 class="fw-bold text-primary mb-0" id="total">$0.00</h5>
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label small text-muted fw-semibold mb-1">
                                                <i class="bi bi-credit-card me-1"></i>Método de Pago
                                            </label>
                                            <select name="payment_method" class="form-select form-select-sm border-0 bg-white shadow-sm">
                                                <option value="cash">Efectivo</option>
                                                <option value="card">Tarjeta</option>
                                                <option value="transfer">Transferencia</option>
                                                <option value="credit">Crédito</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 d-flex flex-column justify-content-end">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="submitSale">
                                        <i class="bi bi-check-lg me-1"></i> Registrar Venta
                                    </button>
                                    <a href="<?= BASE_URL ?>sales.php" class="btn btn-outline-danger">
                                        <i class="bi bi-x-circle me-1"></i> Cancelar
                                    </a>
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
        </main>
    </div>
</div>

<script>
    let cart = [];
    let searchTimeout;
    let clientTimeout;
    let isConsumidorFinal = false;
    let currentTotal = 0;

    // =========================
    // CART PERSISTENCE
    // =========================
    function saveCartToStorage() {
        localStorage.setItem('vetapp_cart', JSON.stringify(cart));
    }

    function loadCartFromStorage() {
        const saved = localStorage.getItem('vetapp_cart');
        if (saved) {
            try {
                cart = JSON.parse(saved);
                renderCart();
            } catch (e) {
                localStorage.removeItem('vetapp_cart');
            }
        }
    }

    function clearCartFromStorage() {
        localStorage.removeItem('vetapp_cart');
    }

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
                    resultsDiv.innerHTML = '<div class="list-group-item text-center text-muted py-3">No se encontraron medicamentos</div>';
                    return;
                }
                data.forEach(med => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action d-flex align-items-center gap-3 py-2';
                    const stockClass = med.stock <= 0 ? 'bg-danger' : med.stock < 5 ? 'bg-warning text-dark' : 'bg-success';
                    btn.innerHTML = `
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate">${med.name}</div>
                            <small class="text-muted">${med.code}</small>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="badge ${stockClass} me-1">${med.stock}</span>
                            <div class="small fw-bold text-primary">$${parseFloat(med.sale_price).toFixed(2)}</div>
                        </div>
                        <i class="bi bi-plus-circle-fill text-success fs-5"></i>
                    `;
                    btn.addEventListener('click', () => addToCart(med));
                    resultsDiv.appendChild(btn);
                });
            })
            .catch(() => {
                resultsDiv.innerHTML = '<div class="list-group-item text-center text-danger py-3">Error al buscar medicamentos</div>';
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
        saveCartToStorage();
    }

    function renderCart() {
        const tbody = document.getElementById('cartItems');
        const mobileDiv = document.getElementById('cartMobileItems');
        const emptyDiv = document.getElementById('cartEmpty');
        tbody.innerHTML = '';
        mobileDiv.innerHTML = '';
        let subtotal = 0;

        if (cart.length === 0) {
            emptyDiv.style.display = '';
            document.getElementById('itemCount').textContent = '0 items';
            calculateTotals(0);
            return;
        }
        emptyDiv.style.display = 'none';
        document.getElementById('itemCount').textContent = cart.length + ' item' + (cart.length > 1 ? 's' : '');

        cart.forEach((item, index) => {
            const lineTotal = item.unit_price * item.quantity;
            subtotal += lineTotal;

            // Desktop row
            const tr = document.createElement('tr');
            tr.className = 'item-enter';
            tr.innerHTML = `
                <td><span class="fw-semibold">${item.name}</span></td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center mx-auto"
                        style="width:70px" value="${item.quantity}" min="1"
                        onchange="updateQty(${index}, this.value)">
                </td>
                <td class="d-none d-sm-table-cell text-end text-muted">$${item.unit_price.toFixed(2)}</td>
                <td class="text-end fw-semibold">$${lineTotal.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeItem(${index})" title="Eliminar">
                        <i class="bi bi-trash3"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);

            // Mobile card
            const card = document.createElement('div');
            card.className = 'card border-light shadow-sm mb-2 item-enter';
            card.innerHTML = `
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="fw-semibold small">${item.name}</div>
                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-1" onclick="removeItem(${index})" title="Eliminar">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">Cant:</span>
                            <input type="number" class="form-control form-control-sm text-center" style="width:60px"
                                value="${item.quantity}" min="1" onchange="updateQty(${index}, this.value)">
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">$${item.unit_price.toFixed(2)} c/u</div>
                            <div class="fw-bold small">$${lineTotal.toFixed(2)}</div>
                        </div>
                    </div>
                </div>
            `;
            mobileDiv.appendChild(card);
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
        saveCartToStorage();
    }

    function removeItem(index) {
        cart.splice(index, 1);
        renderCart();
        saveCartToStorage();
    }

    // =========================
    // TOTALS
    // =========================
    function calculateTotals(subtotal) {
        const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
        const discountAmount = subtotal * (discountPercent / 100);
        const base = subtotal - discountAmount;

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
        currentTotal = total;

        document.getElementById('subtotal').innerText = `$${subtotal.toFixed(2)}`;
        document.getElementById('iva').innerText = `$${iva.toFixed(2)}`;
        document.getElementById('total').innerText = `$${total.toFixed(2)}`;

        document.getElementById('subtotalInput').value = subtotal.toFixed(2);
        document.getElementById('taxTotalInput').value = iva.toFixed(2);
        document.getElementById('totalInput').value = total.toFixed(2);
        document.getElementById('discountInput').value = discountAmount.toFixed(2);

        checkCFLimit();
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
    // CLIENT TYPE TOGGLE
    // =========================
    const clientTypeRegistered = document.getElementById('clientTypeRegistered');
    const clientTypeCF = document.getElementById('clientTypeCF');
    const clientSearchSection = document.getElementById('clientSearchSection');
    const cfBadgeSection = document.getElementById('cfBadgeSection');
    const cfLimitAlert = document.getElementById('cfLimitAlert');
    const clientIdInput = document.getElementById('clientId');

    function toggleClientType() {
        if (clientTypeCF.checked) {
            isConsumidorFinal = true;
            clientSearchSection.style.display = 'none';
            cfBadgeSection.style.display = '';
            clearClientSelection();
            clientIdInput.value = document.getElementById('cfClientId').value;
            checkCFLimit();
        } else {
            isConsumidorFinal = false;
            clientSearchSection.style.display = '';
            cfBadgeSection.style.display = 'none';
            cfLimitAlert.style.display = 'none';
            clientTypeCF.disabled = false;
        }
    }

    function clearClientSelection() {
        document.getElementById('selectedClient').innerHTML = '';
        document.getElementById('clientResults').innerHTML = '';
        document.getElementById('clientSearch').value = '';
    }

    function checkCFLimit() {
        if (!isConsumidorFinal) return;

        if (currentTotal >= 150) {
            cfLimitAlert.style.display = '';
            clientTypeCF.disabled = true;
            clientTypeCF.checked = false;
            clientTypeRegistered.checked = true;
            toggleClientType();
            Swal.fire({
                title: 'Límite Consumidor Final',
                html: 'El total ($<strong>' + currentTotal.toFixed(2) + '</strong>) supera el límite de $150.00 del SRI.<br><br>Debe usar un cliente registrado.',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
        } else {
            cfLimitAlert.style.display = 'none';
        }
    }

    clientTypeRegistered.addEventListener('change', toggleClientType);
    clientTypeCF.addEventListener('change', toggleClientType);

    // =========================
    // CLIENT SEARCH
    // =========================
    const clientInput = document.getElementById('clientSearch');
    const clientBtn = document.getElementById('clientSearchBtn');
    const clientResults = document.getElementById('clientResults');
    const selectedClientDiv = document.getElementById('selectedClient');

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
                    clientResults.innerHTML = '<div class="list-group-item text-center text-muted py-3">Sin resultados</div>';

                    Swal.fire({
                        title: 'Cliente no encontrado',
                        html: 'No se encontraron resultados para "<strong>' + q + '</strong>".<br>¿Desea registrar un nuevo cliente?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonText: 'Seguir buscando',
                        confirmButtonText: 'Crear nuevo cliente'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            navigateToCreateClient();
                        }
                    });
                    return;
                }

                data.forEach(item => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action client-result py-2';
                    const initial = (item.client_name || '?').charAt(0).toUpperCase();
                    btn.innerHTML = `
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:36px;height:36px;font-size:14px;font-weight:600;">
                                ${initial}
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-truncate">${item.client_name}</div>
                                <small class="text-muted">
                                    <i class="bi bi-heart-pulse me-1"></i>${item.pet_name ? item.pet_name : 'Sin mascota'}
                                </small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    `;
                    btn.addEventListener('click', () => selectClient(item));
                    clientResults.appendChild(btn);
                });
            })
            .catch(() => {
                Swal.fire('Error', 'Fallo al buscar clientes', 'error');
            });
    }

    function selectClient(item) {
        clientIdInput.value = item.id_client;
        const initial = (item.client_name || '?').charAt(0).toUpperCase();
        selectedClientDiv.innerHTML = `
            <div class="card border-success bg-success-subtle">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:36px;height:36px;font-size:14px;font-weight:600;">
                                ${initial}
                            </div>
                            <div>
                                <div class="fw-semibold small">${item.client_name}</div>
                                <small class="text-muted">
                                    <i class="bi bi-heart-pulse me-1"></i>${item.pet_name ? item.pet_name : 'Sin mascota'}
                                </small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="clearClientSelection(); document.getElementById('clientId').value='';" title="Cambiar cliente">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        clientResults.innerHTML = '';
        clientInput.value = '';
    }

    function navigateToCreateClient() {
        saveCartToStorage();
        window.location.href = '<?= BASE_URL ?>clients.php?action=create';
    }

    document.getElementById('btnNewClient').addEventListener('click', function(e) {
        e.preventDefault();
        navigateToCreateClient();
    });

    // =========================
    // FORM SUBMIT
    // =========================
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        const clientId = document.getElementById('clientId').value;

        if (!clientId) {
            e.preventDefault();
            Swal.fire({
                title: 'Cliente requerido',
                html: 'Debe seleccionar un cliente o usar Consumidor Final.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonText: 'Buscar cliente',
                confirmButtonText: 'Crear nuevo cliente'
            }).then((result) => {
                if (result.isConfirmed) {
                    navigateToCreateClient();
                }
            });
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
        clearCartFromStorage();
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

    // =========================
    // ON LOAD
    // =========================
    loadCartFromStorage();

    <?php if ($clientCreated): ?>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Cliente Creado',
            text: '<?= addslashes($newClientName) ?> se ha registrado exitosamente.',
            confirmButtonText: 'OK',
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            const searchInput = document.getElementById('clientSearch');
            const nameParts = '<?= addslashes($newClientName) ?>'.split(' ');
            searchInput.value = nameParts[0] || '';
            searchInput.focus();
            searchClients();
        });

        clientTypeRegistered.checked = true;
        toggleClientType();
    });
    <?php endif; ?>
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
