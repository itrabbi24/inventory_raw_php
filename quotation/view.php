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
    <div class="content p-3">
        <div class="page-header d-flex justify-content-between align-items-center mb-3 no-print">
            <div class="page-title">
                <h4>Quotation #<?php echo $quotation['quotation_no']; ?></h4>
            </div>
            <div class="page-btn">
                <button onclick="window.print()" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="fas fa-print me-1"></i>Print/PDF
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-4 p-md-5">
                <div id="invoice" class="invoice-container">
                    
                    <!-- Top Ribbon -->
                    <div class="row align-items-center mb-4">
                        <div class="col-7">
                            <div class="d-flex align-items-center">
                                <?php $logo = !empty($settings['company_logo']) ? BASE_URL . 'uploads/logo/' . $settings['company_logo'] : BASE_URL . 'assets/img/logo.png'; ?>
                                <img src="<?php echo $logo; ?>" alt="logo" class="logo-img me-3">
                                <div>
                                    <h3 class="fw-bold text-dark m-0"><?php echo $settings['company_name'] ?? 'Inventory POS'; ?></h3>
                                    <p class="text-muted small m-0 lh-sm">
                                        <?php echo $settings['company_address'] ?? 'Dhaka, Bangladesh'; ?><br>
                                        <?php echo $settings['company_phone'] ?? '0123456789'; ?> | <?php echo $settings['company_email'] ?? 'admin@example.com'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-5 text-end">
                            <h2 class="text-primary fw-extrabold m-0" style="font-size: 32px;">QUOTATION</h2>
                            <p class="text-dark fw-bold m-0 small mt-1">#<?php echo $quotation['quotation_no']; ?></p>
                            <div class="d-inline-block bg-light px-2 py-1 rounded small mt-2 border">
                                <span class="text-muted">Issue:</span> <?php echo date('d-m-Y', strtotime($quotation['quotation_date'])); ?> |
                                <span class="text-danger fw-bold">Valid Until:</span> <?php echo date('d-m-Y', strtotime('+30 days', strtotime($quotation['quotation_date']))); ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4 pt-2">
                        <div class="col-12">
                            <div class="bg-light p-3 rounded-3 border-start border-4 border-primary">
                                <h6 class="text-primary text-uppercase small fw-bold mb-1">Customer Info:</h6>
                                <h5 class="fw-bold text-dark m-0"><?php echo $quotation['customer_name'] ?: 'Valued Customer'; ?></h5>
                                <p class="text-muted m-0 small"><?php echo $quotation['phone']; ?> | <?php echo $quotation['address']; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered mb-0 printable-table">
                            <thead>
                                <tr class="bg-primary text-white text-center">
                                    <th style="width: 40px;">#</th>
                                    <th>Product & Specification</th>
                                    <th style="width: 60px;">QTY</th>
                                    <th style="width: 110px;">Unit Price</th>
                                    <th style="width: 110px;">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=1; foreach($items as $item): ?>
                                <tr class="text-dark align-middle">
                                    <td class="text-center small py-2"><?php echo $i++; ?></td>
                                    <td class="py-2">
                                        <div class="fw-bold lh-sm"><?php echo $item['product_name']; ?></div>
                                        <?php if(!empty($item['description'])): ?>
                                            <div class="text-muted x-small lh-xs mt-1"><?php echo nl2br($item['description']); ?></div>
                                        <?php endif; ?>
                                        <div class="mt-1">
                                            <?php if(!empty($item['serial_number'])): ?><span class="badge bg-light text-dark border x-small p-1">S/N: <?php echo $item['serial_number']; ?></span><?php endif; ?>
                                            <?php if($item['warranty_months'] > 0): ?><span class="badge bg-light text-primary border x-small p-1 ms-1">Warranty: <?php echo $item['warranty_months']; ?>Mo</span><?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold small"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end small">৳ <?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td class="text-end fw-bold small">৳ <?php echo number_format($item['total_price'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold bg-light small">Subtotal:</td>
                                    <td class="text-end fw-bold bg-light small">৳ <?php echo number_format($quotation['subtotal'], 2); ?></td>
                                </tr>
                                <?php if($quotation['discount'] > 0): ?>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold bg-light small text-info">Discount:</td>
                                    <td class="text-end fw-bold bg-light small text-info">- ৳ <?php echo number_format($quotation['discount'], 2); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="bg-primary bg-opacity-10">
                                    <td colspan="4" class="text-end h5 fw-extrabold m-0 py-2">GRAND TOTAL:</td>
                                    <td class="text-end h5 fw-extrabold text-primary m-0 py-2">৳ <?php echo number_format($quotation['total_amount'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Footer Section -->
                    <div class="row align-items-end g-0">
                        <div class="col-8">
                            <p class="small text-dark mb-1"><strong>In Word:</strong> <?php echo ucfirst(numberToWords($quotation['total_amount'])); ?> only.</p>
                            <?php if(!empty($quotation['notes'])): ?>
                                <div class="bg-light p-2 rounded small border mt-2" style="max-width: 90%;">
                                    <strong>Note:</strong> <?php echo $quotation['notes']; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-4">
                                <h6 class="small fw-bold text-primary text-uppercase mb-1">General Terms & Conditions:</h6>
                                <div class="text-muted x-small lh-xs">
                                    • Quote valid for 30 days. • Prices include relevant taxes. • Confirmation required for order placement.
                                </div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="mt-5 pt-2 border-top mx-auto" style="width: 180px; border-color: #333 !important;">
                                <p class="mb-0 text-dark small fw-bold">Authorized Signature</p>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
.invoice-container { font-family: 'Inter', sans-serif; line-height: 1.2; }
.logo-img { max-height: 60px; width: auto; object-fit: contain; }
.fw-extrabold { font-weight: 800; }
.x-small { font-size: 10px; }
.lh-xs { line-height: 1.2; }
.bg-primary { background-color: #ff9b44 !important; }
.text-primary { color: #ff9b44 !important; }
.border-primary { border-color: #ff9b44 !important; }
.printable-table th { padding: 6px !important; font-size: 11px; text-transform: uppercase; }

@media print {
    @page { size: A4; margin: 10mm; }
    body { background: #fff !important; }
    .sidebar, .header, .page-header, .btn, footer, .no-print { display: none !important; }
    .page-wrapper { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .card { box-shadow: none !important; border: none !important; }
    .card-body { padding: 0 !important; }
    .table-responsive { overflow: visible !important; }
    .bg-light { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
    .bg-primary { background-color: #ff9b44 !important; -webkit-print-color-adjust: exact; }
    .text-white { color: #fff !important; -webkit-print-color-adjust: exact; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
