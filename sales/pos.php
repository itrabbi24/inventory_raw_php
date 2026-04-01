<?php
$pageTitle = 'Point of Sale (POS)';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$customers = $pdo->query("SELECT id, name FROM customers WHERE status=1 ORDER BY name ASC")->fetchAll();
?>

<style>
    :root {
        --pos-primary: #ff9f43;
        --pos-dark: #2d3436;
        --pos-light: #f8f9fa;
        --pos-border: #e9ecef;
    }
    .page-wrapper { padding-top: 60px !important; min-height: 100vh; transition: all 0.3s; background: #fafbfe; }
    
    .pos-container { 
        height: calc(100vh - 60px); 
        overflow: hidden; 
        background: #fff;
        display: flex;
        width: 100%;
    }
    .product-section { 
        flex: 0 0 65%; 
        border-right: 1px solid var(--pos-border); 
        display: flex; 
        flex-direction: column; 
    }
    .cart-section { 
        flex: 0 0 35%; 
        background: #ffffff; 
        display: flex; 
        flex-direction: column; 
        box-shadow: -5px 0 15px rgba(0,0,0,0.02);
    }
    
    .product-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); 
        gap: 15px; 
        padding: 20px;
        overflow-y: auto;
        flex-grow: 1;
    }
    
    .pos-product-card {
        background: #fff;
        border: 1px solid var(--pos-border);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.2s;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        text-align: center;
        padding: 12px;
    }
    .pos-product-card:hover {
        border-color: var(--pos-primary);
        box-shadow: 0 4px 15px rgba(255, 159, 67, 0.15);
        transform: translateY(-3px);
    }
    .pos-product-card .img-box {
        background: #fdf7f2;
        border-radius: 10px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }
    .pos-product-card .name {
        font-size: 0.8rem;
        font-weight: 600;
        height: 2.4rem;
        line-height: 1.2rem;
        overflow: hidden;
        margin-bottom: 8px;
        color: var(--pos-dark);
    }
    .pos-product-card .price {
        font-size: 1rem;
        font-weight: 700;
        color: var(--pos-primary);
    }
    
    .pos-cart-header { background: #fff; padding: 20px 15px; border-bottom: 1px solid var(--pos-border); }
    .pos-cart-body { flex-grow: 1; overflow-y: auto; padding: 0; }
    .pos-cart-footer { background: #fff; border-top: 1px solid var(--pos-border); padding: 25px 20px; }
    
    .cart-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #f8f9fa;
        transition: background 0.2s;
    }
    .cart-item:hover { background: #fdf7f2; }
    .cart-item .item-info { flex-grow: 1; min-width: 0; }
    .cart-item .item-info h6 { font-size: 0.85rem; margin: 0; font-weight: 700; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cart-item .item-info span { font-size: 0.75rem; color: #f90; font-weight: 600; }
    .cart-item .item-qty { width: 90px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 6px; padding: 4px; border: 1px solid #eee; }
    .cart-item .item-qty input { width: 35px; text-align: center; border: 0; background: transparent; font-size: 0.85rem; font-weight: 700; padding: 0; }
    .cart-item .item-price { width: 90px; text-align: right; font-weight: 800; font-size: 0.95rem; color: var(--pos-dark); }
    
    .total-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 1rem; font-weight: 500; }
    .grand-total { border-top: 2px dashed #eee; padding-top: 20px; margin-top: 15px; font-size: 1.6rem; font-weight: 800; color: var(--pos-primary); }
    
    .btn-pay { background: var(--pos-primary); border: none; color: #fff; padding: 18px; border-radius: 12px; font-weight: 800; font-size: 1.2rem; width: 100%; transition: all 0.3s; box-shadow: 0 4px 15px rgba(255, 159, 67, 0.4); }
    .btn-pay:hover { background: #e68a35; transform: scale(1.01); }
</style>

<div class="page-wrapper">
    <div class="pos-container">
        <!-- Left Side -->
        <div class="product-section">
            <div class="p-3 bg-white border-bottom d-flex gap-3 align-items-center">
                <div class="flex-grow-1">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light"><i class="fas fa-barcode"></i></span>
                        <input type="text" id="posProductSearch" class="form-control border-1 bg-light" placeholder="Search by name, model or scan barcode..." autofocus>
                    </div>
                </div>
                <div style="width: 240px;">
                    <select class="select" id="posCategoryFilter" onchange="loadProducts(this.value)">
                        <option value="">All Categories</option>
                        <?php
                        $categories = $pdo->query("SELECT id, name FROM categories WHERE status=1")->fetchAll();
                        foreach ($categories as $cat) {
                            echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="product-grid" id="posProductGrid"></div>
        </div>

        <!-- Right Side (Cart) -->
        <div class="cart-section">
            <div class="pos-cart-header">
                <div class="form-group mb-0">
                    <label class="text-dark small fw-bold mb-2 d-block">BILLING CUSTOMER</label>
                    <select class="select" id="posCustomerId">
                        <option value="0">Walk-in Customer</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="pos-cart-body" id="posCartBody">
                <div class="text-center p-5 text-muted" id="emptyCartMsg">
                    <i class="fas fa-shopping-basket fa-4x mb-4 opacity-25"></i>
                    <h5 class="fw-bold opacity-50">List is empty</h5>
                    <p class="small">Add products to start a sale</p>
                </div>
                <div id="cartItemsList"></div>
            </div>

            <div class="pos-cart-footer">
                <div class="total-row">
                    <span class="text-muted">Subtotal</span>
                    <span id="posSubtotal" class="fw-bold">৳ 0.00</span>
                </div>
                <div class="total-row align-items-center">
                    <span class="text-muted">Discount</span>
                    <div style="width: 130px;">
                        <input type="number" id="posDiscount" class="form-control text-end fw-bold" value="0" oninput="calculatePosTotals()">
                    </div>
                </div>
                <div class="total-row grand-total">
                    <span>Total Due</span>
                    <span id="posGrandTotal">৳ 0.00</span>
                </div>
                <div class="mt-4">
                    <button class="btn btn-pay" onclick="finalizePosSale()">
                        <i class="fas fa-print me-2"></i> COMPLETE SALE
                    </button>
                    <div class="text-center mt-2">
                        <a href="javascript:void(0)" onclick="clearCart()" class="text-danger small fw-bold">Cancel Everything</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    
    document.getElementById('posProductSearch').addEventListener('input', function(e) {
        let q = e.target.value;
        if(q.length > 2 || q.length === 0) {
            fetchProducts(q);
        }
    });
});

function loadProducts(category_id = '') {
    document.getElementById('posProductGrid').innerHTML = '<div class="col-12 text-center p-5"><div class="spinner-border text-warning"></div></div>';
    fetch(`../ajax/search_products.php?q=&category_id=${category_id}`)
        .then(res => res.json())
        .then(data => renderProductGrid(data));
}

function fetchProducts(q) {
    fetch(`../ajax/search_products.php?q=${q}`)
        .then(res => res.json())
        .then(data => renderProductGrid(data));
}

function renderProductGrid(products) {
    let grid = document.getElementById('posProductGrid');
    grid.innerHTML = '';
    
    if(!products || products.error || products.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center p-5 text-muted">No products found</div>';
        return;
    }

    products.forEach(p => {
        let imgPath = p.image ? `../../assets/img/product/${p.image}` : `../../assets/img/icons/product.svg`;
        let card = `
            <div class="pos-product-card shadow-sm" onclick="addToCart(${p.id}, '${p.name.replace(/'/g, "\\'")}', ${p.purchase_price})">
                <div class="img-box">
                    <img src="${imgPath}" alt="img" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                <div class="name" title="${p.name}">${p.name}</div>
                <div class="price">৳ ${parseFloat(p.purchase_price).toFixed(2)}</div>
                <div class="small text-muted mt-1" style="font-size:0.65rem">Stock: ${p.current_stock}</div>
            </div>
        `;
        grid.insertAdjacentHTML('beforeend', card);
    });
}

function addToCart(id, name, price) {
    let existing = cart.find(i => i.id === id);
    if(existing) {
        existing.qty++;
    } else {
        cart.push({ id, name, price: parseFloat(price), qty: 1 });
    }
    renderCart();
}

function renderCart() {
    let list = document.getElementById('cartItemsList');
    let emptyMsg = document.getElementById('emptyCartMsg');
    
    if(cart.length === 0) {
        list.innerHTML = '';
        emptyMsg.style.display = 'block';
    } else {
        emptyMsg.style.display = 'none';
        list.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="item-info">
                    <h6>${item.name}</h6>
                    <span>৳ ${item.price.toFixed(2)}</span>
                </div>
                <div class="item-qty">
                    <button class="btn btn-sm p-0 px-1" onclick="updateQty(${item.id}, -1)"><i class="fas fa-minus text-muted"></i></button>
                    <input type="text" value="${item.qty}" readonly>
                    <button class="btn btn-sm p-0 px-1" onclick="updateQty(${item.id}, 1)"><i class="fas fa-plus text-warning"></i></button>
                </div>
                <div class="item-price">৳ ${(item.price * item.qty).toFixed(2)}</div>
                <div class="ps-3"><a href="javascript:void(0)" onclick="removeItem(${item.id})" class="text-danger"><i class="fas fa-trash-alt"></i></a></div>
            </div>
        `).join('');
    }
    calculatePosTotals();
}

function updateQty(id, delta) {
    let item = cart.find(i => i.id === id);
    if(item) {
        item.qty += delta;
        if(item.qty <= 0) removeItem(id);
        else renderCart();
    }
}

function removeItem(id) {
    cart = cart.filter(i => i.id !== id);
    renderCart();
}

function calculatePosTotals() {
    let subtotal = cart.reduce((total, item) => total + (item.price * item.qty), 0);
    let discount = parseFloat(document.getElementById('posDiscount').value) || 0;
    let grandTotal = subtotal - discount;
    if(grandTotal < 0) grandTotal = 0;
    
    document.getElementById('posSubtotal').innerText = '৳ ' + subtotal.toFixed(2);
    document.getElementById('posGrandTotal').innerText = '৳ ' + grandTotal.toFixed(2);
}

function clearCart() {
    if(confirm('Are you sure you want to clear the entire cart?')) {
        cart = [];
        renderCart();
    }
}

function finalizePosSale() {
    if(cart.length === 0) return alert('Your cart is empty. Please add some products.');
    
    let customerId = document.getElementById('posCustomerId').value;
    let total = document.getElementById('posGrandTotal').innerText;
    
    // In a real app, this would be an AJAX call to process the sale
    Swal.fire({
        title: 'Complete Sale?',
        text: `Total Amount: ${total}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ff9f43',
        confirmButtonText: 'Yes, Complete'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Success', 'Sale has been processed (Demo Mode)', 'success');
            cart = [];
            renderCart();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
