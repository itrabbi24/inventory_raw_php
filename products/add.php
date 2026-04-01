<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = sanitize($_POST['name'] ?? '');
    $model          = sanitize($_POST['model'] ?? '');
    $category_id    = (int)($_POST['category_id'] ?? 0);
    $brand_id       = (int)($_POST['brand_id'] ?? 0);
    $unit           = sanitize($_POST['unit'] ?? 'pcs');
    $min_stock      = (int)($_POST['min_stock'] ?? 5);
    $description    = sanitize($_POST['description'] ?? '');

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $image = uploadFile($_FILES['image'], 'products');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    if (empty($name)) {
        $error = 'Product name is required';
    } else {
        // Duplicate Check
        $check = $pdo->prepare("SELECT id FROM products WHERE name = ? AND model = ? AND status = 1");
        $check->execute([$name, $model]);
        if ($check->rowCount() > 0) {
            $error = "Product '{$name}' with model '{$model}' already exists!";
        }
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
                INSERT INTO products (name, model, category_id, brand_id, unit, min_stock_alert, image, description, current_stock) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([$name, $model, $category_id, $brand_id, $unit, $min_stock, $image, $description]);
            $product_id = $pdo->lastInsertId();
            
            logActivity($pdo, $_SESSION['user_id'], 'Added new product', 'products', $product_id);
            $pdo->commit();
            $_SESSION['message'] = 'Product added successfully!';
            header('Location: list.php');
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Add Product';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$categories = $pdo->query("SELECT * FROM categories WHERE status=1")->fetchAll();
$brands     = $pdo->query("SELECT * FROM brands WHERE status=1")->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Product Add</h4>
                <h6>Create new product</h6>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success mt-3"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="add.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Model</label>
                                <input type="text" name="model" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Category</label>
                                <select class="select" name="category_id">
                                    <option value="0">Choose Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Brand</label>
                                <select class="select" name="brand_id">
                                    <option value="0">Choose Brand</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>"><?php echo $brand['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Unit</label>
                                <select class="select" name="unit">
                                    <option value="pcs">Pcs</option>
                                    <option value="box">Box</option>
                                    <option value="set">Set</option>
                                    <option value="kg">KG</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Minimum Stock alert</label>
                                <input type="number" name="min_stock" value="5" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description"></textarea>
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
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-submit me-2">Submit</button>
                            <a href="list.php" class="btn btn-cancel">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
