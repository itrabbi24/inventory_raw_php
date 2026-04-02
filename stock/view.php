<?php
$pageTitle = 'Purchase Voucher';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT s.*, p.name as product_name, p.model, v.name as vendor_name, v.phone as vendor_phone, v.address as vendor_address 
    FROM stock_in s 
    LEFT JOIN products p ON s.product_id = p.id 
    LEFT JOIN vendors v ON s.vendor_id = v.id 
    WHERE s.id = ?
");
$stmt->execute([$id]);
$purchase = $stmt->fetch();

if (!$purchase) {
    echo "<script>window.location='list.php';</script>";
    exit();
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Purchase Voucher Detail</h4>
                <h6>Full detail for Voucher <?php echo $purchase['invoice_no']; ?></h6>
            </div>
            <div class="page-btn">
                <button onclick="window.print()" class="btn btn-primary shadow-sm"><i class="fas fa-print me-2"></i>Print Voucher</button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <div id="invoice" class="invoice-container">
                    <!-- Header -->
                    <div class="invoice-header d-flex justify-content-between align-items-start mb-5">
                        <div class="company-brand">
                            <?php $logo = !empty($settings['company_logo']) ? BASE_URL . 'uploads/logo/' . $settings['company_logo'] : BASE_URL . 'assets/img/logo.png'; ?>
                            <img src="<?php echo $logo; ?>" alt="logo" class="mb-3" style="max-height: 80px;">
                            <h2 class="fw-bold text-dark m-0"><?php echo $settings['company_name'] ?? 'Inventory POS'; ?></h2>
                            <p class="text-muted small mt-1"><?php echo $settings['company_address'] ?? 'Dhaka, Bangladesh'; ?><br>Stock Entry Voucher</p>
                        </div>
                        <div class="invoice-meta text-end">
                            <h1 class="text-uppercase fw-bold text-info mb-1" style="font-size: 3rem; letter-spacing: -2px;">PURCHASE</h1>
                            <div class="bg-light p-3 rounded-3 d-inline-block shadow-sm">
                                <p class="mb-0 text-muted small fw-bold text-uppercase">Voucher NO</p>
                                <h4 class="fw-bold text-primary mb-2">#<?php echo $purchase['invoice_no']; ?></h4>
                                <p class="mb-0 text-muted small fw-bold text-uppercase">DATE</p>
                                <h5 class="fw-bold m-0"><?php echo date('d M, Y', strtotime($purchase['purchase_date'])); ?></h5>
                            </div>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="row pt-4 border-top">
                        <div class="col-6">
                            <h6 class="text-muted text-uppercase fw-bold small mb-3">Vendor / Supplier:</h6>
                            <h4 class="fw-bold text-dark mb-2"><?php echo $purchase['vendor_name'] ?: 'N/A'; ?></h4>
                            <p class="text-muted mb-1"><i class="fas fa-phone-alt me-2 text-info"></i><?php echo $purchase['vendor_phone']; ?></p>
                            <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-2 text-info"></i><?php echo $purchase['vendor_address']; ?></p>
                        </div>
                        <div class="col-6 text-end">
                            <h6 class="text-muted text-uppercase fw-bold small mb-3">Status:</h6>
                            <h5 class="mb-1"><span class="badge <?php echo ($purchase['due_amount'] <= 0) ? 'bg-lightgreen text-success' : 'bg-lightred text-danger'; ?> py-2 px-3 fw-bold"><?php echo ($purchase['due_amount'] <= 0) ? 'PAID' : 'DUE / PENDING'; ?></span></h5>
                        </div>
                    </div>

                    <!-- Product Table -->
                    <div class="table-responsive mt-5">
                        <table class="table table-hover">
                            <thead class="bg-info text-white">
                                <tr>
                                    <th class="py-3 px-4">#</th>
                                    <th class="py-3 px-4 text-center">Image</th>
                                    <th class="py-3 px-4">Product Name</th>
                                    <th class="py-3 px-4">Serial / Batch</th>
                                    <th class="py-3 px-4 text-center">Quantity</th>
                                    <th class="py-3 px-4 text-end">Purchase Price</th>
                                    <th class="py-3 px-4 text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="align-middle">
                                    <td class="px-4">01</td>
                                    <td class="px-4 text-center"><i class="fas fa-box fa-2x text-muted"></i></td>
                                    <td class="px-4">
                                        <h6 class="fw-bold mb-0"><?php echo $purchase['product_name']; ?></h6>
                                        <small class="text-muted"><?php echo $purchase['model']; ?></small>
                                    </td>
                                    <td class="px-4 small"><?php echo $purchase['serial_number'] ?: 'N/A'; ?></td>
                                    <td class="px-4 text-center fw-bold"><?php echo $purchase['quantity']; ?></td>
                                    <td class="px-4 text-end">৳ <?php echo number_format($purchase['purchase_price'], 2); ?></td>
                                    <td class="px-4 text-end fw-bold">৳ <?php echo number_format($purchase['quantity'] * $purchase['purchase_price'], 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="row mt-5">
                        <div class="col-7">
                            <div class="p-4 bg-light rounded-4 h-100 border-start border-4 border-info shadow-sm">
                                <h6 class="fw-bold mb-3 text-dark text-uppercase small">Note / Remark:</h6>
                                <p class="text-muted small"><?php echo $purchase['notes'] ?: 'No special notes for this entry.'; ?></p>
                                <div class="mt-4 pt-4">
                                    <div class="border-bottom d-inline-block" style="width: 200px;"></div>
                                    <p class="mb-0 text-muted small mt-1">Authorized Person</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="card bg-white border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted fw-bold small">ITEM TOTAL</span>
                                        <span class="fw-bold">৳ <?php echo number_format($purchase['quantity'] * $purchase['purchase_price'], 2); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3 text-info">
                                        <span class="fw-bold small">SHIPPING (+)</span>
                                        <span class="fw-bold">৳ <?php echo number_format($purchase['shipping_charge'], 2); ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-4 mt-2 h4 fw-bold">
                                        <span class="text-dark">VOUCHER TOTAL</span>
                                        <span class="text-info">৳ <?php echo number_format($purchase['total_price'], 2); ?></span>
                                    </div>
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-success fw-bold">PAID TO VENDOR</span>
                                            <span class="text-success fw-bold">৳ <?php echo number_format($purchase['paid_amount'], 2); ?></span>
                                        </div>
                                        <?php if($purchase['due_amount'] > 0): ?>
                                        <div class="d-flex justify-content-between small">
                                            <span class="text-danger fw-bold">PENDING / DUE</span>
                                            <span class="text-danger fw-bold">৳ <?php echo number_format($purchase['due_amount'], 2); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Payments History -->
                <div class="payment-history-section mt-5 no-print border-top pt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold text-dark m-0"><i class="fas fa-history me-2 text-info"></i>Vendor Payment History</h4>
                        <?php if($purchase['due_amount'] > 0): ?>
                        <button class="btn btn-info fw-bold text-white px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                            <i class="fas fa-plus-circle me-2"></i>RECORD PAYMENT
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php
                    $stmt_p = $pdo->prepare("SELECT * FROM purchase_payments WHERE stock_in_id = ? ORDER BY id DESC");
                    $stmt_p->execute([$id]);
                    $payments = $stmt_p->fetchAll();
                    ?>

                    <div class="table-responsive">
                        <table class="table table-hover border">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Method</th>
                                    <th class="py-3 px-4">Amount</th>
                                    <th class="py-3 px-4">Note</th>
                                    <th class="py-3 px-4">Created By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($payments)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No payment records found for this purchase.</td></tr>
                                <?php else: foreach($payments as $p): ?>
                                <tr>
                                    <td class="px-4 py-3"><?php echo date('d M, Y', strtotime($p['payment_date'])); ?></td>
                                    <td class="px-4"><span class="badge bg-light text-dark border px-3 py-2 text-uppercase small"><?php echo $p['method']; ?></span></td>
                                    <td class="px-4 fw-bold text-success">৳ <?php echo number_format($p['amount'], 2); ?></td>
                                    <td class="px-4 text-muted small"><?php echo $p['note'] ?: '-'; ?></td>
                                    <td class="px-4 small">Staff ID: <?php echo $p['created_by']; ?></td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Record Vendor Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPaymentForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="stock_in_id" value="<?php echo $id; ?>">
                    
                    <div class="bg-light p-3 rounded-4 mb-4 d-flex justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small fw-bold">TOTAL PAYABLE</p>
                            <h4 class="fw-bold text-danger mb-0">৳ <?php echo number_format($purchase['due_amount'], 2); ?></h4>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 text-muted small fw-bold">PURCHASE #</p>
                            <h5 class="fw-bold m-0"><?php echo $purchase['invoice_no']; ?></h5>
                        </div>
                    </div>

                    <div class="form-group mb-3"><label class="small fw-bold mb-2">DATE</label><input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="form-group mb-3"><label class="small fw-bold mb-2">AMOUNT (৳)</label><input type="number" name="amount" class="form-control form-control-lg border-info fw-bold" max="<?php echo $purchase['due_amount'] + 0.01; ?>" step="0.01" required></div>
                    <div class="form-group mb-3">
                        <label class="small fw-bold mb-2">METHOD</label>
                        <select name="method" class="select" required>
                            <option value="cash">Cash</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="form-group mb-0"><label class="small fw-bold mb-2">REF / NOTE</label><textarea name="note" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info px-5 fw-bold text-white shadow-sm">SUBMIT PAYMENT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#addPaymentForm').on('submit', function(e) {
    e.preventDefault();
    let btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
    $.ajax({
        url: '<?php echo BASE_URL; ?>ajax/add_purchase_payment.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if(res.success) {
                Swal.fire({ icon: 'success', title: 'Payment Recorded', showConfirmButton: false, timer: 1500 }).then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
                btn.prop('disabled', false).html('SUBMIT PAYMENT');
            }
        }
    });
});
</script>

<style>
@media print {
    .sidebar, .header, .page-header, .btn, footer, .no-print, .payment-history-section { display: none !important; }
    .page-wrapper { margin: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
