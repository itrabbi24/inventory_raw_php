<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: list.php');
    exit();
}

// Fetch current product data
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: list.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = sanitize($_POST['name'] ?? '');
    $model          = sanitize($_POST['model'] ?? '');
    $category_id    = (int)($_POST['category_id'] ?? 0);
    $brand_id       = (int)($_POST['brand_id'] ?? 0);
    $unit           = sanitize($_POST['unit'] ?? 'pcs');
    $min_stock      = (int)($_POST['min_stock'] ?? 5);
    $buying_price   = (float)($_POST['buying_price'] ?? 0);
    $selling_price  = (float)($_POST['selling_price'] ?? 0);
    $description    = sanitize($_POST['description'] ?? '');

    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $image = uploadFile($_FILES['image'], 'products');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    if (empty($name)) {
        $error = 'Product name is required';
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name=?, model=?, category_id=?, brand_id=?, unit=?, min_stock_alert=?, buying_price=?, selling_price=?, image=?, description=? 
                WHERE id=?
            ");
            $stmt->execute([$name, $model, $category_id, $brand_id, $unit, $min_stock, $buying_price, $selling_price, $image, $description, $id]);
            
            logActivity($pdo, $_SESSION['user_id'], "Updated product: {$name}", 'products', $id);
            $pdo->commit();
            $_SESSION['message'] = 'Product updated successfully!';
            header('Location: list.php');
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Edit Product';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$categories = $pdo->query("SELECT * FROM categories WHERE status=1")->fetchAll();
$brands     = $pdo->query("SELECT * FROM brands WHERE status=1")->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Product Edit</h4>
                <h6>Update existing product</h6>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $product['name']; ?>" required>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Model</label>
                                <input type="text" name="model" class="form-control" value="<?php echo $product['model']; ?>">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Category</label>
                                <select class="select" name="category_id">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $product['category_id']) ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Brand</label>
                                <select class="select" name="brand_id">
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>" <?php echo ($brand['id'] == $product['brand_id']) ? 'selected' : ''; ?>><?php echo $brand['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Unit</label>
                                <select class="select" name="unit">
                                    <option value="pcs" <?php echo ($product['unit'] == 'pcs') ? 'selected' : ''; ?>>Pcs</option>
                                    <option value="box" <?php echo ($product['unit'] == 'box') ? 'selected' : ''; ?>>Box</option>
                                    <option value="set" <?php echo ($product['unit'] == 'set') ? 'selected' : ''; ?>>Set</option>
                                    <option value="kg" <?php echo ($product['unit'] == 'kg') ? 'selected' : ''; ?>>KG</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Minimum Stock alert</label>
                                <input type="number" name="min_stock" value="<?php echo $product['min_stock_alert']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Buying Price (৳)</label>
                                <input type="number" name="buying_price" value="<?php echo $product['buying_price']; ?>" step="0.01" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Selling Price (৳)</label>
                                <input type="number" name="selling_price" value="<?php echo $product['selling_price']; ?>" step="0.01" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description"><?php echo $product['description']; ?></textarea>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label> Product Image</label>
                                <div class="image-upload">
                                    <input type="file" name="image">
                                    <div class="image-uploads">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/upload.svg" alt="img">
                                        <h4>Drag and drop a file to upload</h4>
                                    </div>
                                </div>
                                <?php if($product['image']): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo BASE_URL . 'uploads/products/' . $product['image']; ?>" class="rounded" style="width: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-submit me-2">Update Product</button>
                            <a href="list.php" class="btn btn-cancel">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
