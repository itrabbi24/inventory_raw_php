<?php
$pageTitle = 'View Invoice';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT s.*, c.name as customer_name, c.phone, c.address FROM sales s LEFT JOIN customers c ON s.customer_id = c.id WHERE s.id = ?");
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
                            <h2 class="fw-bold text-dark m-0"><?php echo $settings['company_name']; ?></h2>
                            <p class="text-muted small mt-1"><?php echo $settings['company_address']; ?><br>Email: <?php echo $settings['company_email']; ?> | Phone: <?php echo $settings['company_phone']; ?></p>
                        </div>
                        <div class="invoice-meta text-end">
                            <h1 class="text-uppercase fw-bold text-warning mb-1" style="font-size: 3rem; letter-spacing: -2px;">INVOICE</h1>
                            <div class="bg-light p-3 rounded-3 d-inline-block shadow-sm">
                                <p class="mb-0 text-muted small fw-bold">INVOICE NO</p>
                                <h4 class="fw-bold text-primary mb-2">#<?php echo $sale['invoice_no']; ?></h4>
                                <p class="mb-0 text-muted small fw-bold">DATE</p>
                                <h5 class="fw-bold m-0"><?php echo date('d M, Y', strtotime($sale['sale_date'])); ?></h5>
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
                            <p class="text-muted small">Processed by: <strong><?php echo $_SESSION['name']; ?></strong></p>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mt-5">
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

                    <!-- Calculations -->
                    <div class="row mt-5">
                        <div class="col-7">
                            <div class="p-4 bg-light rounded-4 h-100 border-start border-4 border-warning shadow-sm">
                                <h6 class="fw-bold mb-3 text-dark text-uppercase small">Payment Notes:</h6>
                                <p class="text-muted small mb-4 italic"><?php echo $sale['notes'] ?: 'No special notes for this transaction.'; ?></p>
                                <div class="mt-auto">
                                    <p class="mb-1 text-muted small">Authorized signature for: <strong><?php echo $settings['company_name']; ?></strong></p>
                                    <div class="mt-4 border-bottom d-inline-block" style="width: 250px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="card bg-white border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted fw-bold small">SUBTOTAL</span>
                                        <span class="fw-bold">৳ <?php echo number_format($sale['subtotal'], 2); ?></span>
                                    </div>
                                    <?php if($sale['discount'] > 0): ?>
                                    <div class="d-flex justify-content-between mb-3 text-danger">
                                        <span class="fw-bold small">DISCOUNT (-)</span>
                                        <span class="fw-bold">৳ <?php echo number_format($sale['discount'], 2); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-4 mt-2">
                                        <span class="h5 fw-bold text-dark">GRAND TOTAL</span>
                                        <span class="h4 fw-bold text-warning">৳ <?php echo number_format($sale['total_amount'], 2); ?></span>
                                    </div>
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-success fw-bold">AMOUNT PAID</span>
                                            <span class="text-success fw-bold">৳ <?php echo number_format($sale['paid_amount'], 2); ?></span>
                                        </div>
                                        <?php if($sale['due_amount'] > 0): ?>
                                        <div class="d-flex justify-content-between small">
                                            <span class="text-danger fw-bold">BALANCE DUE</span>
                                            <span class="text-danger fw-bold">৳ <?php echo number_format($sale['due_amount'], 2); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="footer text-center mt-5 pt-5 border-top">
                        <p class="text-dark fw-bold mb-1">Thank you for your business!</p>
                        <p class="text-muted small">Generated on <?php echo date('d-m-Y H:i:s'); ?> | Developed by <strong>ARG RABBI</strong></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
@media print {
    .sidebar, .header, .page-header, .btn, footer { display: none !important; }
    .page-wrapper { margin: 0 !important; padding: 0 !important; }
    .card { border: none !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
