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
        <div class="page-header">
            <div class="page-title">
                <h4>Quotation Detail</h4>
                <h6>Full detail for Quotation <?php echo $quotation['quotation_no']; ?></h6>
            </div>
            <div class="page-btn">
                <button onclick="window.print()" class="btn btn-primary"><img src="<?php echo BASE_URL; ?>assets/img/icons/printer.svg" alt="img" class="me-1">Print Quotation</button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="invoice-box" id="quotation">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="invoice-logo">
                                <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="logo">
                                <h3 class="mt-2 text-primary"><?php echo $settings['company_name']; ?></h3>
                                <p><?php echo $settings['company_address']; ?></p>
                            </div>
                        </div>
                        <div class="col-lg-6 text-end">
                            <h2 class="text-uppercase">Quotation</h2>
                            <h5>#<?php echo $quotation['quotation_no']; ?></h5>
                            <p>Date: <?php echo $quotation['quotation_date']; ?></p>
                            <p>Valid Until: <?php echo date('Y-m-d', strtotime('+30 days', strtotime($quotation['quotation_date']))); ?></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <h6>Quot To:</h6>
                            <p><strong><?php echo $quotation['customer_name']; ?></strong></p>
                            <p>Phone: <?php echo $quotation['phone']; ?></p>
                            <p>Address: <?php echo $quotation['address']; ?></p>
                        </div>
                    </div>

                    <div class="table-responsive mt-5">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr><th>#</th><th>Product Description</th><th>Quantity</th><th>Price (৳)</th><th>Total (৳)</th></tr>
                            </thead>
                            <tbody>
                                <?php $i=1; foreach($items as $item): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo $item['product_name']; ?></td>
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
                            <p><strong>Notes:</strong> <?php echo $quotation['notes'] ?: 'No additional notes.'; ?></p>
                            <p class="mt-5">_______________________<br>Authorized Signature</p>
                        </div>
                        <div class="col-lg-5">
                            <table class="table table-borderless">
                                <tr><td><strong>Subtotal:</strong></td><td class="text-end"><?php echo formatCurrency($quotation['subtotal']); ?></td></tr>
                                <tr><td><strong>Discount:</strong></td><td class="text-end"><?php echo formatCurrency($quotation['discount']); ?></td></tr>
                                <tr><td><strong>Grand Total:</strong></td><td class="text-end"><h4><?php echo formatCurrency($quotation['total_amount']); ?></h4></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style> @media print { .sidebar, .header, .page-header, .btn, footer { display: none !important; } .page-wrapper { margin: 0 !important; padding: 0 !important; } .card { border: none !important; } } </style>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
