<?php
$pageTitle = 'View Quotation';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT q.*, c.name as customer_name, c.phone, c.address FROM quotations q LEFT JOIN customers c ON q.customer_id = c.id WHERE q.id = ?");
$stmt->execute([$id]);
$quotation = $stmt->fetch();

if (!$quotation) {
    echo "<script>window.location='list.php';</script>";
    exit();
}

$stmt_items = $pdo->prepare("SELECT qi.*, p.name as product_name FROM quotation_items qi LEFT JOIN products p ON qi.product_id = p.id WHERE qi.quotation_id = ?");
$stmt_items->execute([$id]);
$items = $stmt_items->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header d-flex justify-content-between align-items-center mb-4 no-print">
            <div class="page-title">
                <h4>Quotation Details</h4>
                <h6>Full detail for Quotation #<?php echo $quotation['quotation_no']; ?></h6>
            </div>
            <div class="page-btn">
                <button onclick="window.print()" class="btn btn-primary shadow-sm px-4">
                    <i class="fas fa-print me-2"></i>Print/Download PDF
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0 invoice-main-card" style="border-radius: 20px;">
            <div class="card-body p-5">
                <div id="invoice" class="invoice-container">
                    
                    <!-- Header Section -->
                    <div class="row align-items-start mb-5">
                        <div class="col-8">
                            <div class="company-brand mb-4">
                                <?php $logo = !empty($settings['company_logo']) ? BASE_URL . 'uploads/logo/' . $settings['company_logo'] : BASE_URL . 'assets/img/logo.png'; ?>
                                <img src="<?php echo $logo; ?>" alt="logo" class="logo-img mb-2">
                                <h2 class="fw-bold text-dark m-0 d-block"><?php echo $settings['company_name']; ?></h2>
                                <p class="text-muted small mt-1 line-height-1">
                                    <i class="fas fa-map-marker-alt me-1 text-primary"></i> <?php echo $settings['company_address']; ?><br>
                                    <i class="fas fa-phone-alt me-1 text-primary"></i> <?php echo $settings['company_phone']; ?> | 
                                    <i class="fas fa-envelope me-1 text-primary"></i> <?php echo $settings['company_email']; ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <h1 class="text-uppercase fw-bold text-primary display-5 mb-1" style="letter-spacing: -2px;">QUOTATION</h1>
                            <div class="bg-light p-3 rounded-4 d-inline-block shadow-sm text-start border-start border-4 border-primary">
                                <p class="mb-0 text-muted small fw-bold text-uppercase">Serial No</p>
                                <h5 class="fw-bold text-dark mb-2">#<?php echo $quotation['quotation_no']; ?></h5>
                                <p class="mb-0 text-muted small fw-bold text-uppercase">Issue Date</p>
                                <h6 class="fw-bold m-0"><?php echo date('d M, Y', strtotime($quotation['quotation_date'])); ?></h6>
                                <div class="mt-2 pt-2 border-top">
                                    <p class="mb-0 text-danger small fw-bold text-uppercase">Valid Until</p>
                                    <h6 class="fw-bold m-0 text-danger"><?php echo date('d M, Y', strtotime('+30 days', strtotime($quotation['quotation_date']))); ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recipient Section -->
                    <div class="row pt-4 mb-5">
                        <div class="col-6">
                            <h6 class="text-primary text-uppercase fw-extrabold small mb-3 letter-spacing-1">Quotation Prepared For:</h6>
                            <h4 class="fw-bold text-dark m-0"><?php echo $quotation['customer_name'] ?: 'Valued Customer'; ?></h4>
                            <p class="text-muted mt-2 mb-1"><i class="fas fa-phone-alt me-2 text-primary"></i><?php echo $quotation['phone'] ?: 'N/A'; ?></p>
                            <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-2 text-primary"></i><?php echo $quotation['address'] ?: 'N/A'; ?></p>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr class="bg-primary text-white">
                                    <th class="py-3 px-4 border-0" style="width: 50px;">#</th>
                                    <th class="py-3 px-4 border-0">Product Description</th>
                                    <th class="py-3 px-2 border-0 text-center" style="width: 80px;">QTY</th>
                                    <th class="py-3 px-4 border-0 text-end" style="width: 140px;">Unit Price</th>
                                    <th class="py-3 px-4 border-0 text-end" style="width: 140px;">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=1; foreach($items as $item): ?>
                                <tr class="item-row align-middle border-bottom">
                                    <td class="py-3 px-4 text-center text-muted"><?php echo str_pad($i++, 2, '0', STR_PAD_LEFT); ?></td>
                                    <td class="py-3 px-4">
                                        <h6 class="fw-bold text-dark mb-1"><?php echo $item['product_name']; ?></h6>
                                        <?php if(!empty($item['description'])): ?>
                                            <p class="text-muted small mb-1 lh-sm"><?php echo nl2br($item['description']); ?></p>
                                        <?php endif; ?>
                                        <?php if(!empty($item['serial_number'])): ?>
                                            <span class="badge bg-light text-primary border px-2 py-1" style="font-size: 10px;">S/N: <?php echo $item['serial_number']; ?></span>
                                        <?php endif; ?>
                                        <?php if($item['warranty_months'] > 0): ?>
                                            <span class="badge bg-light text-info border px-2 py-1 ms-1" style="font-size: 10px;">Warranty: <?php echo $item['warranty_months']; ?> Mo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-2 text-center fw-bold"><?php echo $item['quantity']; ?></td>
                                    <td class="py-3 px-4 text-end text-muted">৳ <?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td class="py-3 px-4 text-end fw-bold text-dark">৳ <?php echo number_format($item['total_price'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Section -->
                    <div class="row g-0">
                        <div class="col-7 p-4 bg-light rounded-bottom-4">
                            <h6 class="text-uppercase fw-bold text-primary small mb-3">Terms & Conditions:</h6>
                            <ul class="text-muted small list-unstyled">
                                <li class="mb-1">• This quotation is valid for 30 days from the date of issue.</li>
                                <li class="mb-1">• Delivery will be made within 3-5 working days of order confirmation.</li>
                                <li class="mb-1">• Payment should be made in favor of <?php echo $settings['company_name']; ?>.</li>
                                <li>• Please contact us for any further clarification or modification.</li>
                            </ul>
                            <?php if(!empty($quotation['notes'])): ?>
                                <h6 class="text-uppercase fw-bold text-dark small mt-4 mb-2">Note:</h6>
                                <p class="text-muted small mb-0"><?php echo $quotation['notes']; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-5 p-4 border-start bg-white">
                            <div class="d-flex justify-content-between mb-3 text-muted">
                                <span class="fw-bold text-uppercase small">Subtotal</span>
                                <span class="fw-bold">৳ <?php echo number_format($quotation['subtotal'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 text-info">
                                <span class="fw-bold text-uppercase small">Discount (-)</span>
                                <span class="fw-bold">৳ <?php echo number_format($quotation['discount'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-4 mt-2">
                                <span class="h4 fw-extrabold text-dark m-0">GRAND TOTAL</span>
                                <span class="h4 fw-extrabold text-primary m-0">৳ <?php echo number_format($quotation['total_amount'], 2); ?></span>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4 border border-primary border-opacity-25 mt-2">
                                <p class="mb-0 text-center text-primary small fw-bold">Amount in Word: <?php echo ucfirst(numberToWords($quotation['total_amount'])); ?> Only.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Signature -->
                    <div class="row pt-5 mt-5">
                        <div class="col-6">
                            <div class="mt-5 text-center d-inline-block">
                                <div class="border-top pt-2" style="width: 200px; border-color: #ddd !important;">
                                    <p class="mb-0 text-muted small fw-bold">Customer's Signature</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="mt-5 text-center d-inline-block">
                                <div class="border-top pt-2" style="width: 200px; border-color: #ddd !important;">
                                    <p class="mb-0 text-muted small fw-bold">Authorized Signature</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.invoice-container { font-family: 'Inter', sans-serif; background: #fff; position: relative; }
.logo-img { max-height: 80px; width: auto; object-fit: contain; }
.fw-extrabold { font-weight: 800; }
.letter-spacing-1 { letter-spacing: 1px; }
.line-height-1 { line-height: 1.5; }
.table-custom thead th { border-top-left-radius: 8px; border-top-right-radius: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
.table-custom tbody tr:hover { background-color: #fcfcfc; }
.item-row td { vertical-align: top; }
.bg-primary { background-color: #ff9b44 !important; }
.text-primary { color: #ff9b44 !important; }
.border-primary { border-color: #ff9b44 !important; }

@media print {
    body { background: #fff !important; }
    .sidebar, .header, .page-header, .btn, footer, .no-print { display: none !important; }
    .page-wrapper { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .card { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; }
    .card-body { padding: 0 !important; }
    .invoice-container { padding: 40px !important; }
    .table-responsive { overflow: visible !important; }
    .table { width: 100% !important; table-layout: fixed !important; }
    .table td, .table th { word-wrap: break-word !important; }
    .bg-light { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
    .bg-primary { background-color: #ff9b44 !important; -webkit-print-color-adjust: exact; }
    .text-white { color: #fff !important; -webkit-print-color-adjust: exact; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
