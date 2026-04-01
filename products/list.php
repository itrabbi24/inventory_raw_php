<?php
$pageTitle = 'Product List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Fetch all products with category and brand names
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, b.name as brand_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN brands b ON p.brand_id = b.id 
    WHERE p.status = 1 
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Product List</h4>
                <h6>Manage your products</h6>
            </div>
            <div class="page-btn">
                <a href="<?php echo BASE_URL; ?>products/add.php" class="btn btn-added"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add New Product</a>
            </div>
        </div>

        <!-- /product list -->
        <div class="card">
            <div class="card-body">
                <div class="table-top">
                    <div class="search-set">
                        <div class="search-path">
                            <a class="btn btn-filter" id="filter_search">
                                <img src="<?php echo BASE_URL; ?>assets/img/icons/filter.svg" alt="img">
                                <span><img src="<?php echo BASE_URL; ?>assets/img/icons/closes.svg" alt="img"></span>
                            </a>
                        </div>
                        <div class="search-input">
                            <a class="btn btn-searchset"><img src="<?php echo BASE_URL; ?>assets/img/icons/search-white.svg" alt="img"></a>
                        </div>
                    </div>
                    <div class="wordset">
                        <ul>
                            <li>
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="pdf"><img src="<?php echo BASE_URL; ?>assets/img/icons/pdf.svg" alt="img"></a>
                            </li>
                            <li>
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="excel"><img src="<?php echo BASE_URL; ?>assets/img/icons/excel.svg" alt="img"></a>
                            </li>
                            <li>
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="print"><img src="<?php echo BASE_URL; ?>assets/img/icons/printer.svg" alt="img"></a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>
                                    <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>
                                </th>
                                <th>Product Name</th>
                                <th>Model</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Unit</th>
                                <th>Current Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="productimgname">
                                    <a href="javascript:void(0);" class="product-img">
                                        <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo $product['image'] ?: 'no-image.png'; ?>" alt="product">
                                    </a>
                                    <a href="javascript:void(0);"><?php echo $product['name']; ?></a>
                                </td>
                                <td><?php echo $product['model']; ?></td>
                                <td><?php echo $product['category_name']; ?></td>
                                <td><?php echo $product['brand_name']; ?></td>
                                <td><?php echo $product['unit']; ?></td>
                                <td><span class="badges <?php echo ($product['current_stock'] <= $product['min_stock_alert']) ? 'bg-lightred' : 'bg-lightgreen'; ?>"><?php echo $product['current_stock']; ?></span></td>
                                <td>
                                    <a class="me-3" href="<?php echo BASE_URL; ?>products/barcode.php?id=<?php echo $product['id']; ?>" title="Print Barcodes">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/barcode.svg" alt="img">
                                    </a>
                                    <a class="me-3" href="javascript:void(0);" onclick="adjustStock(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['current_stock']; ?>)" title="Adjust Stock">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/edit-5.svg" alt="img">
                                    </a>
                                    <a class="me-3" href="javascript:void(0);" onclick="viewStockHistory(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')" title="View History">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/time.svg" alt="img">
                                    </a>
                                    <a class="me-3" href="<?php echo BASE_URL; ?>products/edit.php?id=<?php echo $product['id']; ?>" title="Edit Product">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/edit.svg" alt="img">
                                    </a>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteProduct(<?php echo $product['id']; ?>)" title="Delete Product">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/delete.svg" alt="img">
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /product list -->
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Stock Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="adjustStockForm">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="adj_product_id">
                    <div class="form-group">
                        <label class="text-muted small fw-bold">PRODUCT</label>
                        <input type="text" id="adj_product_name" class="form-control bg-light border-0" readonly>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="text-muted small fw-bold">CURRENT STOCK</label>
                                <input type="number" id="adj_current_stock" class="form-control bg-light border-0" readonly>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="text-muted small fw-bold">NEW STOCK</label>
                                <input type="number" name="new_stock" id="adj_new_stock" class="form-control border-warning" required>
                                <span class="small text-warning" id="adj_diff_msg"></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="text-muted small fw-bold">REASON FOR ADJUSTMENT</label>
                        <select name="reason" class="select" required>
                            <option value="Damage">Damage</option>
                            <option value="Restock">Restock</option>
                            <option value="Correction">Data Correction</option>
                            <option value="Expired">Expired</option>
                            <option value="Lost">Lost/Theft</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="text-muted small fw-bold">NOTES (OPTIONAL)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-5 fw-bold text-white shadow-sm">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Stock Movement Log: <span id="hist_title_name" class="text-warning"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover" id="historyTable">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th>Date</th>
                                <th>Old Stock</th>
                                <th>New Stock</th>
                                <th>Change</th>
                                <th>Reason</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody id="historyBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewStockHistory(id, name) {
    $('#hist_title_name').text(name);
    $('#historyBody').html('<tr><td colspan="6" class="text-center p-4"><div class="spinner-border text-warning spinner-border-sm me-2"></div>Loading History...</td></tr>');
    $('#historyModal').modal('show');
    
    $.get('<?php echo BASE_URL; ?>ajax/get_stock_history.php', {product_id: id}, function(res) {
        let history = JSON.parse(res);
        if(history.length === 0) {
            $('#historyBody').html('<tr><td colspan="6" class="text-center p-4">No adjustment history found tracking for this item</td></tr>');
            return;
        }
        
        let html = history.map(h => `
            <tr>
                <td class="small text-muted">${h.created_at}</td>
                <td>${h.old_stock}</td>
                <td class="fw-bold">${h.new_stock}</td>
                <td><span class="badge ${h.change_qty >= 0 ? 'bg-lightgreen' : 'bg-lightred'}">${h.change_qty >= 0 ? '+' : ''}${h.change_qty}</span></td>
                <td><span class="text-dark small">${h.reason}</span></td>
                <td class="small text-muted" title="${h.notes}">${h.notes.substring(0, 30)}${h.notes.length > 30 ? '...' : ''}</td>
            </tr>
        `).join('');
        $('#historyBody').html(html);
    });
}

function adjustStock(id, name, current) {
    $('#adj_product_id').val(id);
    $('#adj_product_name').val(name);
    $('#adj_current_stock').val(current);
    $('#adj_new_stock').val(current);
    $('#adj_diff_msg').text('');
    
    $('#adj_new_stock').on('input', function() {
        let diff = $(this).val() - current;
        if(diff > 0) $('#adj_diff_msg').text('Adding +' + diff + ' units').removeClass('text-danger').addClass('text-success');
        else if(diff < 0) $('#adj_diff_msg').text('Subtracting ' + diff + ' units').removeClass('text-success').addClass('text-danger');
        else $('#adj_diff_msg').text('');
    });

    $('#adjustStockModal').modal('show');
}

$('#adjustStockForm').on('submit', function(e) {
    e.preventDefault();
    let btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
    
    $.ajax({
        url: '<?php echo BASE_URL; ?>ajax/adjust_stock.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            let data = JSON.parse(res);
            if(data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Stock Adjusted', showConfirmButton: false, timer: 1500 })
                .then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
                btn.prop('disabled', false).html('Save Adjustment');
            }
        }
    });
});

function deleteProduct(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will deactivate the product!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff9f43',
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'products', id: id}, function(res) {
                let data = JSON.parse(res);
                if(data.status == 'success') {
                    Swal.fire('Deleted!', 'Product removed.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
