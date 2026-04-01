<?php
$pageTitle = 'Category List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$stmt = $pdo->query("SELECT * FROM categories WHERE status=1 ORDER BY id DESC");
$categories = $stmt->fetchAll();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = sanitize($_POST['name'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');

    if (!empty($name)) {
        // Duplicate Check
        $check = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND status = 1");
        $check->execute([$name]);
        if ($check->rowCount() > 0) {
            $error = "Category '{$name}' already exists!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $desc]);
            $_SESSION['message'] = 'Category added successfully!';
            header('Location: list.php');
            exit();
        }
    }
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Product Category list</h4>
                <h6>View/Search product Category</h6>
            </div>
            <div class="page-btn">
                <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add Category</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?php echo $cat['name']; ?></td>
                                <td><?php echo $cat['description']; ?></td>
                                <td>
                                    <a class="me-3" href="javascript:void(0);" onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo $cat['name']; ?>', '<?php echo $cat['description']; ?>')">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/edit.svg" alt="img">
                                    </a>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteCategory(<?php echo $cat['id']; ?>)">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="list.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_category" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteCategory(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'categories', id: id}, function(res) {
                location.reload();
            });
        }
    })
}

function editCategory(id, name, desc) {
    // Basic edit logic here or use another modal
    Swal.fire({
        title: 'Edit Category',
        html: `<input id="swal-input1" class="swal2-input" value="${name}">` +
              `<textarea id="swal-input2" class="swal2-textarea">${desc}</textarea>`,
        focusConfirm: false,
        preConfirm: () => {
            return [
                document.getElementById('swal-input1').value,
                document.getElementById('swal-input2').value
            ]
        }
    }).then((result) => {
        if (result.value) {
            // Ajax update call should be here
            $.post('<?php echo BASE_URL; ?>ajax/update_record.php', {table: 'categories', id: id, name: result.value[0], description: result.value[1]}, function() {
                location.reload();
            });
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
