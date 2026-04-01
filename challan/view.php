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
        <div class="page-header d-flex justify-content-between align-items-center mb-4 no-print">
            <div class="page-title">
                <h4>Challan Details</h4>
                <h6>Full detail for Delivery Challan #<?php echo $challan['challan_no']; ?></h6>
            </div>
            <div class="page-btn">
                <button onclick="window.print()" class="btn btn-primary shadow-sm px-4">
                    <i class="fas fa-print me-2"></i>Print Challan
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0 invoice-main-card" style="border-radius: 20px;">
            <div class="card-body p-5">
                <div id="challan" class="invoice-container">
                    
                    <!-- Header Section -->
                    <div class="row align-items-start mb-5">
                        <div class="col-8">
                            <div class="company-brand mb-4">
                                <?php $logo = !empty($settings['company_logo']) ? BASE_URL . 'uploads/logo/' . $settings['company_logo'] : BASE_URL . 'assets/img/logo.png'; ?>
                                <img src="<?php echo $logo; ?>" alt="logo" class="logo-img mb-2">
                                <h2 class="fw-bold text-dark m-0 d-block"><?php echo $settings['company_name']; ?></h2>
                                <p class="text-muted small mt-1 line-height-1">
                                    <i class="fas fa-map-marker-alt me-1 text-primary"></i> <?php echo $settings['company_address']; ?><br>
                                    <i class="fas fa-phone-alt me-1 text-primary"></i> <?php echo $settings['company_phone']; ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <h1 class="text-uppercase fw-bold text-success display-5 mb-1" style="letter-spacing: -2px;">CHALLAN</h1>
                            <div class="bg-light p-3 rounded-4 d-inline-block shadow-sm text-start border-start border-4 border-success">
                                <p class="mb-0 text-muted small fw-bold text-uppercase">Challan No</p>
                                <h5 class="fw-bold text-dark mb-2">#<?php echo $challan['challan_no']; ?></h5>
                                <p class="mb-0 text-muted small fw-bold text-uppercase">Delivery Date</p>
                                <h6 class="fw-bold m-0"><?php echo date('d M, Y', strtotime($challan['challan_date'])); ?></h6>
                            </div>
                        </div>
                    </div>

                    <!-- Client Section -->
                    <div class="row pt-4 mb-5">
                        <div class="col-6">
                            <h6 class="text-success text-uppercase fw-extrabold small mb-3 letter-spacing-1">Deliver To:</h6>
                            <h4 class="fw-bold text-dark m-0"><?php echo $challan['customer_name']; ?></h4>
                            <p class="text-muted mt-2 mb-1"><i class="fas fa-phone-alt me-2 text-success"></i><?php echo $challan['phone']; ?></p>
                            <p class="text-muted mb-0"><i class="fas fa-truck me-2 text-success"></i><?php echo $challan['delivery_address'] ?: $challan['address']; ?></p>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr class="bg-success text-white">
                                    <th class="py-3 px-4 border-0" style="width: 50px;">#</th>
                                    <th class="py-3 px-4 border-0">Product Description</th>
                                    <th class="py-3 px-2 border-0 text-center" style="width: 100px;">Quantity</th>
                                    <th class="py-3 px-4 border-0">Serial Number / Tracking</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=1; foreach($items as $item): ?>
                                <tr class="item-row align-middle border-bottom">
                                    <td class="py-3 px-4 text-center text-muted"><?php echo str_pad($i++, 2, '0', STR_PAD_LEFT); ?></td>
                                    <td class="py-3 px-4">
                                        <h6 class="fw-bold text-dark mb-1"><?php echo $item['product_name']; ?></h6>
                                        <?php if(!empty($item['description'])): ?>
                                            <p class="text-muted small mb-0 lh-sm"><?php echo nl2br($item['description']); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-2 text-center fw-bold h5 mb-0"><?php echo $item['quantity']; ?></td>
                                    <td class="py-3 px-4">
                                        <?php if(!empty($item['serial_number'])): ?>
                                            <span class="badge bg-light text-dark border px-3 py-2 fw-bold" style="font-size: 11px;"><?php echo $item['serial_number']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted italic small">No S/N recorded</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Section -->
                    <div class="row pt-5 mt-5">
                        <div class="col-4 text-center">
                            <div class="mt-5 pt-2 border-top mx-auto" style="width: 150px; border-color: #ddd !important;">
                                <p class="mb-0 text-muted small fw-bold">Received By</p>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="mt-5 pt-2 border-top mx-auto" style="width: 150px; border-color: #ddd !important;">
                                <p class="mb-0 text-muted small fw-bold">Delivered By</p>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="mt-5 pt-2 border-top mx-auto" style="width: 150px; border-color: #ddd !important;">
                                <p class="mb-0 text-muted small fw-bold">Authorized By</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-5 pt-5 text-center border-top no-print">
                        <p class="text-muted small">This is a system generated delivery challan.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.invoice-container { font-family: 'Inter', sans-serif; background: #fff; position: relative; }
.logo-img { max-height: 70px; width: auto; object-fit: contain; }
.fw-extrabold { font-weight: 800; }
.letter-spacing-1 { letter-spacing: 1px; }
.line-height-1 { line-height: 1.5; }
.table-custom thead th { border-top-left-radius: 8px; border-top-right-radius: 0; font-size: 13px; text-transform: uppercase; }
.item-row td { vertical-align: middle; }
.bg-success { background-color: #28a745 !important; }
.text-success { color: #28a745 !important; }
.border-success { border-color: #28a745 !important; }

@media print {
    .sidebar, .header, .page-header, .btn, footer, .no-print { display: none !important; }
    .page-wrapper { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .card { box-shadow: none !important; border: none !important; }
    .invoice-container { padding: 30px !important; }
    .table { width: 100% !important; table-layout: fixed !important; }
    .bg-success { background-color: #28a745 !important; -webkit-print-color-adjust: exact; }
    .text-white { color: #fff !important; -webkit-print-color-adjust: exact; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
