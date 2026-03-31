<?php
$pageTitle = 'Brand List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$stmt = $pdo->query("SELECT * FROM brands WHERE status=1 ORDER BY id DESC");
$brands = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_brand'])) {
    $name = sanitize($_POST['name'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO brands (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $desc]);
        header('Location: list.php');
        exit();
    }
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Brand List</h4>
                <h6>Manage your product brands</h6>
            </div>
            <div class="page-btn">
                <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addBrandModal"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add Brand</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr><th>Brand Name</th><th>Description</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($brands as $brand): ?>
                            <tr>
                                <td><?php echo $brand['name']; ?></td>
                                <td><?php echo $brand['description']; ?></td>
                                <td>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteBrand(<?php echo $brand['id']; ?>)">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/delete.svg" alt="img">
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Brand</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="list.php" method="POST">
                <div class="modal-body">
                    <div class="form-group"><label>Brand Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_brand" class="btn btn-primary">Add</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteBrand(id) {
    Swal.fire({
        title: 'Delete Brand?',
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'brands', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
