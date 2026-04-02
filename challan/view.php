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
    <div class="content p-3">
        <div class="page-header d-flex justify-content-between align-items-center mb-3 no-print">
            <div class="page-title">
                <h4>Challan #<?php echo $challan['challan_no']; ?></h4>
            </div>
            <div class="page-btn">
                <button onclick="window.print()" class="btn btn-success btn-sm px-3 shadow-sm">
                    <i class="fas fa-print me-1"></i>Print/PDF
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-4 p-md-5">
                <div id="challan" class="invoice-container">
                    
                    <!-- Header -->
                    <div class="row align-items-center mb-4">
                        <div class="col-7 text-start">
                            <div class="d-flex align-items-center">
                                <?php $logo = !empty($settings['company_logo']) ? BASE_URL . 'uploads/logo/' . $settings['company_logo'] : BASE_URL . 'assets/img/logo.png'; ?>
                                <img src="<?php echo $logo; ?>" alt="logo" class="logo-img me-3">
                                <div>
                                    <h3 class="fw-bold text-dark m-0"><?php echo $settings['company_name'] ?? 'Inventory POS'; ?></h3>
                                    <p class="text-muted small m-0 lh-sm"><?php echo $settings['company_address'] ?? 'Dhaka, Bangladesh'; ?><br><?php echo $settings['company_phone'] ?? '0123456789'; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-5 text-end">
                            <h2 class="text-success fw-extrabold m-0" style="font-size: 32px;">CHALLAN</h2>
                            <p class="text-dark fw-bold m-0 small mt-1">#<?php echo $challan['challan_no']; ?></p>
                            <div class="d-inline-block bg-light px-2 py-1 rounded small mt-2 border">
                                <span class="text-muted small fw-bold">DELIVERY DATE:</span> <?php echo date('d-m-Y', strtotime($challan['challan_date'])); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recipient -->
                    <div class="row mb-4 pt-2">
                        <div class="col-12">
                            <div class="bg-light p-3 rounded-3 border-start border-4 border-success">
                                <h6 class="text-success text-uppercase small fw-bold mb-1">Deliver To:</h6>
                                <h5 class="fw-bold text-dark m-0"><?php echo $challan['customer_name']; ?></h5>
                                <p class="text-muted m-0 small"><strong>Phone:</strong> <?php echo $challan['phone']; ?></p>
                                <p class="text-muted m-0 small"><strong>Address:</strong> <?php echo $challan['delivery_address'] ?: $challan['address']; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mb-5">
                        <table class="table table-sm table-bordered printable-table">
                            <thead>
                                <tr class="bg-success text-white text-center">
                                    <th style="width: 40px;">#</th>
                                    <th>Product & Specification</th>
                                    <th style="width: 100px;">Warranty</th>
                                    <th style="width: 60px;">QTY</th>
                                    <th style="width: 180px;">Serial / Tracking No</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=1; foreach($items as $item): ?>
                                <tr class="text-dark align-middle">
                                    <td class="text-center py-2"><?php echo $i++; ?></td>
                                    <td class="py-2">
                                        <div class="fw-bold lh-sm"><?php echo $item['product_name']; ?></div>
                                        <?php if(!empty($item['description'])): ?>
                                            <p class="text-muted x-small lh-xs mt-1 mb-0"><?php echo nl2br($item['description']); ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center small fw-bold text-success"><?php echo $item['warranty_months'] > 0 ? $item['warranty_months'] . ' Months' : 'No Warranty'; ?></td>
                                    <td class="text-center fw-bold h5 mb-0"><?php echo $item['quantity']; ?></td>
                                    <td class="py-2 text-center text-muted">
                                        <?php if(!empty($item['serial_number'])): ?>
                                            <span class="badge bg-light text-dark border px-2 py-1 fw-bold"><?php echo $item['serial_number']; ?></span>
                                        <?php else: ?>
                                            <span class="italic small">-</span>
                                        <?php endif; ?>
                                    </td>

                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Signatures -->
                    <div class="row pt-5 mt-5">
                        <div class="col-4 text-center">
                            <div class="pt-2 border-top mx-auto" style="width: 150px; border-color: #333 !important;">
                                <p class="mb-0 text-dark x-small fw-bold">Customer Received</p>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="pt-2 border-top mx-auto" style="width: 150px; border-color: #333 !important;">
                                <p class="mb-0 text-dark x-small fw-bold">Delivered By</p>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="pt-2 border-top mx-auto" style="width: 150px; border-color: #333 !important;">
                                <p class="mb-0 text-dark x-small fw-bold">Authorized By</p>
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
.invoice-container { font-family: 'Inter', sans-serif; line-height: 1.1; }
.logo-img { max-height: 50px; width: auto; object-fit: contain; }
.fw-extrabold { font-weight: 800; }
.x-small { font-size: 10px; }
.bg-success { background-color: #28a745 !important; }
.text-success { color: #28a745 !important; }
.border-success { border-color: #28a745 !important; }
.printable-table th { padding: 6px !important; font-size: 11px; text-transform: uppercase; }

@media print {
    @page { size: A4; margin: 10mm; }
    .sidebar, .header, .page-header, .btn, footer, .no-print { display: none !important; }
    .page-wrapper { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .card { box-shadow: none !important; border: none !important; }
    .invoice-container { padding: 20px !important; }
    .bg-light { background-color: #f8f9fa !important; }
    .bg-success { background-color: #28a745 !important; -webkit-print-color-adjust: exact; }
    .text-white { color: #fff !important; -webkit-print-color-adjust: exact; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
