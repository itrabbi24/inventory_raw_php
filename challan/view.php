<?php
$pageTitle = 'View Challan';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT ch.*, c.name as customer_name, c.phone, c.address FROM challan ch LEFT JOIN customers c ON ch.customer_id = c.id WHERE ch.id = ?");
$stmt->execute([$id]);
$challan = $stmt->fetch();


if (!$challan) {
    echo "<script>window.location='list.php';</script>";
    exit();
}

$stmt_items = $pdo->prepare("SELECT ci.*, p.name as product_name FROM challan_items ci LEFT JOIN products p ON ci.product_id = p.id WHERE ci.challan_id = ?");
$stmt_items->execute([$id]);
$items = $stmt_items->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Challan Detail</h4>
                <h6>Full detail for Challan <?php echo $challan['challan_no']; ?></h6>
            </div>
            <div class="page-btn">
                <button onclick="window.print()" class="btn btn-primary"><img src="<?php echo BASE_URL; ?>assets/img/icons/printer.svg" alt="img" class="me-1">Print Challan</button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="invoice-box" id="challan">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <h2 class="text-uppercase">Delivery Challan</h2>
                            <h3 class="text-primary"><?php echo $settings['company_name']; ?></h3>
                            <p><?php echo $settings['company_address']; ?></p>
                            <p>Phone: <?php echo $settings['company_phone']; ?></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <h6>Deliver To:</h6>
                            <p><strong><?php echo $challan['customer_name']; ?></strong></p>
                            <p>Phone: <?php echo $challan['phone']; ?></p>
                            <p>Delivery Address: <?php echo $challan['delivery_address'] ?: $challan['address']; ?></p>
                        </div>
                        <div class="col-lg-6 text-end">
                            <h6>Challan Info:</h6>
                            <h5>#<?php echo $challan['challan_no']; ?></h5>
                            <p>Date: <?php echo $challan['challan_date']; ?></p>
                        </div>
                    </div>

                    <div class="table-responsive mt-5">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr><th>#</th><th>Product Description</th><th>Quantity</th><th>Serial Number</th></tr>
                            </thead>
                            <tbody>
                                <?php $i=1; foreach($items as $item): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td>
                                        <strong><?php echo $item['product_name']; ?></strong>
                                        <?php if(!empty($item['description'])): ?>
                                            <br><small class="text-muted"><?php echo nl2br($item['description']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo $item['serial_number']; ?></td>
                                </tr>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-5">
                        <div class="col-lg-4 text-center"><p class="mt-5">_______________________<br>Customer Received</p></div>
                        <div class="col-lg-4 text-center"><p class="mt-5">_______________________<br>Delivered By</p></div>
                        <div class="col-lg-4 text-center"><p class="mt-5">_______________________<br>Authorized Signature</p></div>
                    </div>
                    
                    <div class="mt-5 text-center">
                        <p class="text-muted">Developed by <strong>ARG RABBI</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style> @media print { .sidebar, .header, .page-header, .btn, footer { display: none !important; } .page-wrapper { margin: 0 !important; padding: 0 !important; } .card { border: none !important; } } </style>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
