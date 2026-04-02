<?php
$pageTitle = 'View Invoice';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT s.*, c.name as customer_name, c.phone, c.address, u.name as creator_name FROM sales s LEFT JOIN customers c ON s.customer_id = c.id LEFT JOIN users u ON s.created_by = u.id WHERE s.id = ?");
$stmt->execute([$id]);
$sale = $stmt->fetch();


if (!$sale) {
    echo "<script>window.location='list.php';</script>";
    exit();
}

$stmt_items = $pdo->prepare("SELECT si.*, p.name as product_name FROM sale_items si LEFT JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
$stmt_items->execute([$id]);
$items = $stmt_items->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Sale Invoice Detail</h4>
                <h6>Full detail for Invoice <?php echo $sale['invoice_no']; ?></h6>
            </div>
            <div class="page-btn">
                <button onclick="window.print()" class="btn btn-primary"><img src="<?php echo BASE_URL; ?>assets/img/icons/printer.svg" alt="img" class="me-1">Print Invoice</button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <div id="invoice" class="invoice-container">
                    <!-- Invoice Header -->
                    <div class="invoice-header d-flex justify-content-between align-items-start mb-5">
                        <div class="company-brand">
                            <?php 
                            $logo = !empty($settings['company_logo']) ? BASE_URL . 'uploads/logo/' . $settings['company_logo'] : BASE_URL . 'assets/img/logo.png';
                            ?>
                            <img src="<?php echo $logo; ?>" alt="logo" class="mb-3" style="max-height: 80px;">
                            <h2 class="fw-bold text-dark m-0"><?php echo $settings['company_name'] ?? 'Inventory POS'; ?></h2>
                            <p class="text-muted small mt-1"><?php echo $settings['company_address'] ?? 'Dhaka, Bangladesh'; ?><br>Email: <?php echo $settings['company_email'] ?? 'admin@example.com'; ?> | Phone: <?php echo $settings['company_phone'] ?? '0123456789'; ?></p>
                        </div>
                        <div class="invoice-meta text-end">
                            <h1 class="text-uppercase fw-bold text-warning mb-0" style="font-size: 2.2rem; letter-spacing: -1px;">INVOICE</h1>
                            <div class="bg-light p-2 rounded-3 d-inline-block border">
                                <p class="mb-0 text-muted small fw-bold">NO: <span class="text-primary">#<?php echo $sale['invoice_no']; ?></span> | DATE: <?php echo date('d M, Y', strtotime($sale['sale_date'])); ?></p>
                            </div>
                        </div>

                    </div>

                    <!-- Client Info -->
                    <div class="row pt-4 border-top">
                        <div class="col-6">
                            <h6 class="text-muted text-uppercase fw-bold small mb-3">Invoice To:</h6>
                            <h4 class="fw-bold text-dark mb-2"><?php echo $sale['customer_name'] ?: 'Walk-in Customer'; ?></h4>
                            <p class="text-muted mb-1"><i class="fas fa-phone-alt me-2 text-warning"></i><?php echo $sale['phone'] ?: 'N/A'; ?></p>
                            <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-2 text-warning"></i><?php echo $sale['address'] ?: 'N/A'; ?></p>
                        </div>
                        <div class="col-6 text-end">
                            <h6 class="text-muted text-uppercase fw-bold small mb-3">Status:</h6>
                            <h5 class="mb-1"><span class="badge bg-lightgreen text-success py-2 px-3 fw-bold">PAID (<?php echo $sale['payment_method']; ?>)</span></h5>
                            <p class="text-muted small">Invoice Created By: <strong class="text-dark"><?php echo htmlspecialchars($sale['creator_name'] ?? 'System'); ?></strong></p>


                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mt-3">

                        <table class="table table-hover">
                            <thead class="bg-warning text-white">
                                <tr>
                                    <th class="py-3 px-4" style="border-radius: 12px 0 0 0;">#</th>
                                    <th class="py-3 px-4">Product Details</th>
                                    <th class="py-3 px-4 text-center">Warranty</th>
                                    <th class="py-3 px-4 text-center">Quantity</th>
                                    <th class="py-3 px-4 text-end">Unit Price</th>
                                    <th class="py-3 px-4 text-end" style="border-radius: 0 12px 0 0;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=1; foreach($items as $item): ?>
                                <tr class="align-middle">
                                    <td class="px-4 text-muted"><?php echo str_pad($i++, 2, '0', STR_PAD_LEFT); ?></td>
                                    <td class="px-4 py-3">
                                        <h6 class="fw-bold mb-0 text-dark"><?php echo $item['product_name']; ?></h6>
                                        <small class="text-muted">Serial: <?php echo $item['serial_number'] ?: 'N/A'; ?></small>
                                    </td>
                                    <td class="px-4 text-center small fw-bold text-primary"><?php echo $item['warranty_months']; ?> Months</td>
                                    <td class="px-4 text-center fw-bold"><?php echo $item['quantity']; ?></td>
                                    <td class="px-4 text-end">৳ <?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td class="px-4 text-end fw-bold">৳ <?php echo number_format($item['total_price'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Calculations & Notes Row -->
                    <div class="row mt-3">
                        <div class="col-7">
                            <div class="p-3 bg-light rounded-4 h-100 border-start border-4 border-warning">
                                <h6 class="fw-bold mb-1 text-dark text-uppercase small">Payment Notes:</h6>
                                <p class="text-muted small mb-0 italic"><?php echo $sale['notes'] ?: 'No special notes for this transaction.'; ?></p>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="card bg-white border border-light shadow-sm rounded-4">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted fw-bold small">SUBTOTAL</span>
                                        <span class="fw-bold text-dark">৳ <?php echo number_format($sale['subtotal'], 2); ?></span>
                                    </div>
                                    <?php if($sale['discount'] > 0): ?>
                                    <div class="d-flex justify-content-between mb-2 text-danger">
                                        <span class="fw-bold small">DISCOUNT (-)</span>
                                        <span class="fw-bold">৳ <?php echo number_format($sale['discount'], 2); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold text-dark small text-uppercase">Grand Total</span>
                                        <span class="h5 fw-bold text-warning mb-0">৳ <?php echo number_format($sale['total_amount'], 2); ?></span>
                                    </div>
                                    <div class="p-2 bg-light rounded-3">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-success fw-bold">PAID</span>
                                            <span class="text-success fw-bold">৳ <?php echo number_format($sale['paid_amount'], 2); ?></span>
                                        </div>
                                        <?php if($sale['due_amount'] > 0): ?>
                                        <div class="d-flex justify-content-between small">
                                            <span class="text-danger fw-bold">DUE</span>
                                            <span class="text-danger fw-bold">৳ <?php echo number_format($sale['due_amount'], 2); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Signature Row -->
                    <div class="row" style="margin-top: 45px;">
                        <div class="col-6 text-center">
                            <div class="d-inline-block">
                                <div class="border-bottom border-dark" style="width: 180px;"></div>
                                <p class="mt-1 text-dark fw-bold small text-uppercase" style="font-size: 0.75rem;">Customer Signature</p>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="d-inline-block">
                                <div class="border-bottom border-dark" style="width: 180px;"></div>
                                <p class="mt-1 text-dark fw-bold small text-uppercase" style="font-size: 0.75rem;">Authorized Signature</p>
                            </div>
                        </div>
                    </div>


                    
                    <div class="footer text-center mt-3 pt-3 border-top">

                        <p class="text-dark fw-bold mb-1">Thank you for your business!</p>
                        <p class="text-muted small">Generated on <?php echo date('d-m-Y H:i:s'); ?> | Developed by <strong>ARG RABBI</strong></p>
                    </div>
                </div>

                <!-- Payment History (Hidden in Print) -->
                <div class="payment-history-section mt-5 no-print border-top pt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold text-dark m-0"><i class="fas fa-history me-2 text-warning"></i>Payment Installments</h4>
                        <?php if($sale['due_amount'] > 0): ?>
                        <button class="btn btn-warning fw-bold text-white px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                            <i class="fas fa-plus-circle me-2"></i>ADD INSTALLMENT
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php
                    $stmt_p = $pdo->prepare("SELECT sp.*, u.name as staff_name FROM sale_payments sp LEFT JOIN users u ON sp.created_by = u.id WHERE sp.sale_id = ? ORDER BY sp.id DESC");
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
                                    <th class="py-3 px-4">Received By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($payments)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No installment payments recorded yet.</td></tr>
                                <?php else: foreach($payments as $p): ?>
                                <tr>
                                    <td class="px-4 py-3"><?php echo date('d M, Y', strtotime($p['payment_date'])); ?></td>
                                    <td class="px-4"><span class="badge bg-light text-dark border px-3 py-2 text-uppercase small"><?php echo $p['method']; ?></span></td>
                                    <td class="px-4 fw-bold text-success">৳ <?php echo number_format($p['amount'], 2); ?></td>
                                    <td class="px-4 text-muted small"><?php echo $p['note'] ?: '-'; ?></td>
                                    <td class="px-4 small"><?php echo htmlspecialchars($p['staff_name'] ?? 'Create By: ' . $p['created_by']); ?></td>

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

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Record Installment Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPaymentForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="sale_id" value="<?php echo $id; ?>">
                    
                    <div class="bg-light p-3 rounded-4 mb-4 d-flex justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small fw-bold">TOTAL DUE</p>
                            <h4 class="fw-bold text-danger mb-0">৳ <?php echo number_format($sale['due_amount'], 2); ?></h4>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 text-muted small fw-bold">INVOICE</p>
                            <h5 class="fw-bold m-0">#<?php echo $sale['invoice_no']; ?></h5>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-muted small fw-bold mb-2">PAYMENT DATE</label>
                        <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required shadow-sm>
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-muted small fw-bold mb-2">AMOUNT TO PAY (৳)</label>
                        <input type="number" name="amount" class="form-control form-control-lg border-warning fw-bold h-auto py-3" max="<?php echo $sale['due_amount'] + 0.01; ?>" step="0.01" required placeholder="0.00">
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-muted small fw-bold mb-2">PAYMENT METHOD</label>
                        <select name="method" class="select" required>
                            <option value="cash">Cash</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="text-muted small fw-bold mb-2">NOTE / REFERENCE (OPTIONAL)</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="e.g. 2nd Installment"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-5 fw-bold text-white shadow-sm">
                        <i class="fas fa-check-circle me-1"></i> SUBMIT PAYMENT
                    </button>
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
        url: '<?php echo BASE_URL; ?>ajax/add_sale_payment.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            if(res.success) {
                Swal.fire({ icon: 'success', title: 'Payment Recorded', text: res.message, showConfirmButton: false, timer: 1500 })
                .then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
                btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> SUBMIT PAYMENT');
            }
        }
    });
});
</script>

<style>
@media print {
    .sidebar, .header, .page-header, .btn, footer, .no-print, .payment-history-section, .modal { display: none !important; }
    .page-wrapper { margin: 0 !important; padding: 0 !important; width: 100% !important; min-height: unset !important; }
    .content { padding: 0 !important; margin: 0 !important; }
    .card { border: none !important; box-shadow: none !important; margin: 0 !important; }
    .card-body { padding: 10mm !important; }
    body { background: white !important; font-size: 12px; }
    .table th, .table td { padding: 8px !important; }
    .invoice-container { width: 100% !important; }
    @page { margin: 0.5cm; size: a4; }
    .bg-light { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
    .bg-warning { background-color: #ff9f43 !important; -webkit-print-color-adjust: exact; }
    .text-white { color: white !important; -webkit-print-color-adjust: exact; }
}

</style>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
