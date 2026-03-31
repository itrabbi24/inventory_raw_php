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

        <div class="card">
            <div class="card-body">
                <div class="invoice-box" id="invoice">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="invoice-logo">
                                <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="logo">
                                <h3 class="mt-2 text-primary"><?php echo $settings['company_name']; ?></h3>
                            </div>
                        </div>
                        <div class="col-lg-6 text-end">
                            <h2 class="text-uppercase">Invoice</h2>
                            <h5>#<?php echo $sale['invoice_no']; ?></h5>
                            <p>Date: <?php echo $sale['sale_date']; ?></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <h6>Invoice To:</h6>
                            <p><strong><?php echo $sale['customer_name'] ?: 'Walk-in Customer'; ?></strong></p>
                            <p>Phone: <?php echo $sale['phone']; ?></p>
                            <p>Address: <?php echo $sale['address']; ?></p>
                        </div>
                        <div class="col-lg-6 text-end">
                            <h6>Company Info:</h6>
                            <p><strong><?php echo $settings['company_name']; ?></strong></p>
                            <p><?php echo $settings['company_address']; ?></p>
                            <p>Phone: <?php echo $settings['company_phone']; ?></p>
                        </div>
                    </div>

                    <div class="table-responsive mt-5">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product Description</th>
                                    <th>Serial No</th>
                                    <th>Warranty</th>
                                    <th>Quantity</th>
                                    <th>Price (৳)</th>
                                    <th>Total (৳)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=1; foreach($items as $item): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo $item['product_name']; ?></td>
                                    <td><?php echo $item['serial_number']; ?></td>
                                    <td><?php echo $item['warranty_months']; ?> Months</td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td><?php echo number_format($item['total_price'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-lg-7">
                            <p><strong>Notes:</strong> <?php echo $sale['notes']; ?></p>
                            <p class="mt-5">_______________________<br>Authorized Signature</p>
                        </div>
                        <div class="col-lg-5">
                            <table class="table table-borderless">
                                <tr><td><strong>Subtotal:</strong></td><td class="text-end"><?php echo formatCurrency($sale['subtotal']); ?></td></tr>
                                <tr><td><strong>Discount:</strong></td><td class="text-end"><?php echo formatCurrency($sale['discount']); ?></td></tr>
                                <tr><td><strong>Grand Total:</strong></td><td class="text-end"><h4><?php echo formatCurrency($sale['total_amount']); ?></h4></td></tr>
                                <tr class="text-success"><td><strong>Paid Amount:</strong></td><td class="text-end"><?php echo formatCurrency($sale['paid_amount']); ?></td></tr>
                                <tr class="text-danger"><td><strong>Due Amount:</strong></td><td class="text-end"><strong><?php echo formatCurrency($sale['due_amount']); ?></strong></td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-5 text-center">
                        <p class="text-muted">Developed by <strong>ARG RABBI</strong></p>
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
