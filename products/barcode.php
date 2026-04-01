<?php
$pageTitle = 'Print Barcodes';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$id = (int)($_GET['id'] ?? 0);
$product = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$product->execute([$id]);
$p = $product->fetch();

if (!$p) {
    echo "<div class='page-wrapper'><div class='content'>Product not found</div></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit();
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Print Barcode</h4>
                <h6>Generate labels for <?php echo $p['name']; ?></h6>
            </div>
            <div class="page-btn">
                <button onclick="window.print()" class="btn btn-added"><i class="fas fa-print me-2"></i>Print Labels</button>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="fw-bold small text-muted">NUMBER OF LABELS</label>
                            <input type="number" id="labelCount" class="form-control" value="12" min="1" max="100" oninput="generateLabels()">
                        </div>
                    </div>
                </div>

                <div class="barcode-print-area mt-4" id="barcodeArea">
                    <!-- Barcodes generated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
<script>
function generateLabels() {
    const count = document.getElementById('labelCount').value;
    const area = document.getElementById('barcodeArea');
    area.innerHTML = '';
    
    let html = '<div class="row g-4">';
    for(let i=0; i<count; i++) {
        html += `
            <div class="col-md-3 text-center mb-4 barcode-card">
                <div class="p-3 border rounded bg-white shadow-sm h-100">
                    <div class="small fw-bold text-truncate mb-1"><?php echo $p['name']; ?></div>
                    <svg class="barcode-svg" id="barcode-${i}"></svg>
                    <div class="fw-bold mt-1">৳ <?php echo number_format($p['purchase_price'], 2); ?></div>
                </div>
            </div>
        `;
    }
    html += '</div>';
    area.innerHTML = html;

    for(let i=0; i<count; i++) {
        JsBarcode("#barcode-" + i, "<?php echo $p['id'] . str_pad($p['id'], 5, '0', STR_PAD_LEFT); ?>", {
            format: "CODE128",
            width: 1.5,
            height: 40,
            displayValue: true,
            fontSize: 10
        });
    }
}

document.addEventListener('DOMContentLoaded', generateLabels);
</script>

<style>
@media print {
    .header, .sidebar, .page-header, .card-header, .form-group, .btn-added { display: none !important; }
    .page-wrapper { margin: 0 !important; padding: 0 !important; }
    .content { padding: 0 !important; }
    .barcode-card { width: 33.33% !important; float: left; border: none !important; }
    .barcode-card div { border: 1px solid #eee !important; box-shadow: none !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
