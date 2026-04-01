<?php
$current_page = $_SERVER['PHP_SELF'];
?>
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-inner slimscroll">
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>
                        <li class="<?php echo (strpos($current_page, 'dashboard/index.php') !== false) ? 'active' : ''; ?>">
                            <a href="<?php echo BASE_URL; ?>dashboard/index.php"><img src="<?php echo BASE_URL; ?>assets/img/icons/dashboard.svg" alt="img"><span> Dashboard</span> </a>
                        </li>
                        
                        <?php if (hasPermission('products')): ?>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="<?php echo BASE_URL; ?>assets/img/icons/product.svg" alt="img"><span> Inventory</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo BASE_URL; ?>products/list.php" class="<?php echo (strpos($current_page, 'products/list.php') !== false) ? 'active' : ''; ?>">Product List</a></li>
                                <li><a href="<?php echo BASE_URL; ?>products/add.php" class="<?php echo (strpos($current_page, 'products/add.php') !== false) ? 'active' : ''; ?>">Add Product</a></li>
                                <li><a href="<?php echo BASE_URL; ?>categories/list.php" class="<?php echo (strpos($current_page, 'categories/list.php') !== false) ? 'active' : ''; ?>">Category List</a></li>
                                <li><a href="<?php echo BASE_URL; ?>brands/list.php" class="<?php echo (strpos($current_page, 'brands/list.php') !== false) ? 'active' : ''; ?>">Brand List</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('sales')): ?>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="<?php echo BASE_URL; ?>assets/img/icons/sales1.svg" alt="img"><span> Sales</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo BASE_URL; ?>sales/list.php" class="<?php echo (strpos($current_page, 'sales/list.php') !== false) ? 'active' : ''; ?>">Sales List</a></li>
                                <li><a href="<?php echo BASE_URL; ?>sales/add.php" class="<?php echo (strpos($current_page, 'sales/add.php') !== false) ? 'active' : ''; ?>">New Sales</a></li>
                                <li><a href="<?php echo BASE_URL; ?>sales/pos.php" class="<?php echo (strpos($current_page, 'sales/pos.php') !== false) ? 'active' : ''; ?>">POS (Sales)</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('stock')): ?>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="<?php echo BASE_URL; ?>assets/img/icons/purchase1.svg" alt="img"><span> Stock Management</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo BASE_URL; ?>stock/list.php" class="<?php echo (strpos($current_page, 'stock/list.php') !== false) ? 'active' : ''; ?>">Stock In List</a></li>
                                <li><a href="<?php echo BASE_URL; ?>stock/add.php" class="<?php echo (strpos($current_page, 'stock/add.php') !== false) ? 'active' : ''; ?>">Add Stock</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('quotation')): ?>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="<?php echo BASE_URL; ?>assets/img/icons/quotation1.svg" alt="img"><span> Quotation</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo BASE_URL; ?>quotation/list.php" class="<?php echo (strpos($current_page, 'quotation/list.php') !== false) ? 'active' : ''; ?>">Quotation List</a></li>
                                <li><a href="<?php echo BASE_URL; ?>quotation/add.php" class="<?php echo (strpos($current_page, 'quotation/add.php') !== false) ? 'active' : ''; ?>">Add Quotation</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('challan')): ?>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="<?php echo BASE_URL; ?>assets/img/icons/transfer1.svg" alt="img"><span> Challan</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo BASE_URL; ?>challan/list.php" class="<?php echo (strpos($current_page, 'challan/list.php') !== false) ? 'active' : ''; ?>">Challan List</a></li>
                                <li><a href="<?php echo BASE_URL; ?>challan/add.php" class="<?php echo (strpos($current_page, 'challan/add.php') !== false) ? 'active' : ''; ?>">Add Challan</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('deposit')): ?>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="<?php echo BASE_URL; ?>assets/img/icons/expense1.svg" alt="img"><span> Deposit</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo BASE_URL; ?>deposit/names/list.php" class="<?php echo (strpos($current_page, 'deposit/names/list.php') !== false) ? 'active' : ''; ?>">Deposit Names</a></li>
                                <li><a href="<?php echo BASE_URL; ?>deposit/transactions/list.php" class="<?php echo (strpos($current_page, 'deposit/transactions/list.php') !== false) ? 'active' : ''; ?>">Transactions</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('vendors') || hasPermission('customers')): ?>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="<?php echo BASE_URL; ?>assets/img/icons/users1.svg" alt="img"><span> People</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <?php if (hasPermission('vendors')): ?>
                                <li><a href="<?php echo BASE_URL; ?>vendors/list.php" class="<?php echo (strpos($current_page, 'vendors/list.php') !== false) ? 'active' : ''; ?>">Vendor List</a></li>
                                <?php endif; ?>
                                <?php if (hasPermission('customers')): ?>
                                <li><a href="<?php echo BASE_URL; ?>customers/list.php" class="<?php echo (strpos($current_page, 'customers/list.php') !== false) ? 'active' : ''; ?>">Customer List</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('reports')): ?>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="<?php echo BASE_URL; ?>assets/img/icons/time.svg" alt="img"><span> Reports</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="<?php echo BASE_URL; ?>reports/sales.php" class="<?php echo (strpos($current_page, 'reports/sales.php') !== false) ? 'active' : ''; ?>">Sales Report</a></li>
                                <li><a href="<?php echo BASE_URL; ?>reports/product_sales.php" class="<?php echo (strpos($current_page, 'reports/product_sales.php') !== false) ? 'active' : ''; ?>">Product Wise Sales</a></li>
                                <li><a href="<?php echo BASE_URL; ?>reports/purchases.php" class="<?php echo (strpos($current_page, 'reports/purchases.php') !== false) ? 'active' : ''; ?>">Purchase Report</a></li>
                                <li><a href="<?php echo BASE_URL; ?>reports/inventory.php" class="<?php echo (strpos($current_page, 'reports/inventory.php') !== false) ? 'active' : ''; ?>">Stock Report</a></li>
                                <li><a href="<?php echo BASE_URL; ?>reports/deposit.php" class="<?php echo (strpos($current_page, 'reports/deposit.php') !== false) ? 'active' : ''; ?>">Deposit Report</a></li>
                                <li><a href="<?php echo BASE_URL; ?>reports/profit_loss.php" class="<?php echo (strpos($current_page, 'reports/profit_loss.php') !== false) ? 'active' : ''; ?>">Profit/Loss</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <?php if (hasPermission('users') || hasPermission('settings')): ?>
                        <li class="submenu">
                            <a href="javascript:void(0);"><img src="<?php echo BASE_URL; ?>assets/img/icons/settings.svg" alt="img"><span> Settings</span> <span class="menu-arrow"></span></a>
                            <ul>
                                <?php if (hasPermission('users')): ?>
                                <li><a href="<?php echo BASE_URL; ?>users/list.php" class="<?php echo (strpos($current_page, 'users/list.php') !== false) ? 'active' : ''; ?>">User Management</a></li>
                                <?php endif; ?>
                                <?php if (hasPermission('settings')): ?>
                                <li><a href="<?php echo BASE_URL; ?>settings/general.php" class="<?php echo (strpos($current_page, 'settings/general.php') !== false) ? 'active' : ''; ?>">General Settings</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Sidebar -->
