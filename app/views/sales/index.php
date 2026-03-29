<?php
/**
 * Location: vetapp/app/views/sales/index.php
 */
$title = 'Ventas | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

    <div class="container-fluid">
        <div class="row">
            <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Gestión de Ventas</h2>
                    <a href="<?= BASE_URL ?>sales.php?action=create" class="btn btn-primary">
                        <i class="bi bi-cart-plus me-1"></i> Nueva Venta
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                <th>ID</th>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Usuario</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td><?= $sale['id_sale'] ?></td>
                                        <td><?= htmlspecialchars($sale['sale_code']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($sale['sale_date'])) ?></td>
                                        <td><?= htmlspecialchars($sale['client_name'] ?? 'Consumidor final') ?></td>
                                        <td><?= htmlspecialchars($sale['user_name']) ?></td>
                                        <td>$<?= number_format($sale['total'], 2) ?></td>
                                        <td>
                                            <?php if ($sale['status'] == 'paid'): ?>
                                                <span class="badge bg-success">Pagada</span>
                                            <?php elseif ($sale['status'] == 'cancelled'): ?>
                                                <span class="badge bg-danger">Cancelada</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $sale['id_sale'] ?>" class="btn btn-sm btn-info" title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($sale['status'] != 'cancelled'): ?>
                                                <button type="button" class="btn btn-sm btn-warning btn-cancel" data-id="<?= $sale['id_sale'] ?>" data-code="<?= $sale['sale_code'] ?>">
                                                    <i class="bi bi-x-circle"></i> Cancelar
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Búsqueda de medicamentos (AJAX)
        const searchInput = document.getElementById('medSearch');
        const searchBtn = document.getElementById('searchBtn');
        const resultsDiv = document.getElementById('searchResults');

        let searchTimeout;

        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchMedications();
            }, 300);
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
                        btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                        btn.innerHTML = `
                    <div>
                        <strong>${med.name}</strong><br>
                        <small>${med.code} | Stock: ${med.stock} | $${med.sale_price}</small>
                    </div>
                    <i class="bi bi-plus-circle text-success fs-5"></i>
                `;
                        btn.addEventListener('click', () => addToCart(med));
                        resultsDiv.appendChild(btn);
                    });
                });
        }

        searchBtn.addEventListener('click', searchMedications);
        searchInput.addEventListener('input', debounceSearch);
        searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') searchMedications(); });
    </script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>