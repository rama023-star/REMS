<?php
require_once '../includes/config.php';
requireLogin();

$currentPage = 'tenants';
$pageTitle = 'Tenants';

$properties = getProperties();
$tenants = getTenants();

$error = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'email_exists') {
        $error = 'A user with this email already exists.';
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = db()->prepare("DELETE FROM tenants WHERE tenant_id = :id");
    $stmt->bindParam(':id', $_GET['delete']);
    if ($stmt->execute()) {
        redirect('index.php');
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $propertyId = (int)$_POST['property_id'];
    $unitId = (int)$_POST['unit_id'];
    $leaseStart = $_POST['lease_start'];
    $leaseEnd = $_POST['lease_end'];
    $status = $_POST['status'] ?? 'active';

    if ($id) {
        // Update user info
        $userStmt = db()->prepare("UPDATE users u 
            JOIN tenants t ON u.user_id = t.user_id 
            SET u.fullname = :name, u.email = :email, u.phone = :phone 
            WHERE t.tenant_id = :tenant_id");
        $userStmt->bindParam(':name', $name);
        $userStmt->bindParam(':email', $email);
        $userStmt->bindParam(':phone', $phone);
        $userStmt->bindParam(':tenant_id', $id);
        $userStmt->execute();

        // Update tenant phone
        $stmt = db()->prepare("UPDATE tenants SET phone = :phone WHERE tenant_id = :tenant_id");
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':tenant_id', $id);
        $stmt->execute();

        // Update lease
        $leaseStmt = db()->prepare("UPDATE leases SET unit_id = :unit_id, start_date = :lease_start, end_date = :lease_end, status = :status WHERE tenant_id = :tenant_id");
        $leaseStmt->bindParam(':unit_id', $unitId);
        $leaseStmt->bindParam(':lease_start', $leaseStart);
        $leaseStmt->bindParam(':lease_end', $leaseEnd);
        $leaseStmt->bindParam(':status', $status);
        $leaseStmt->bindParam(':tenant_id', $id);
        $leaseStmt->execute();
    } else {
        // Check if email already exists
        $checkStmt = db()->prepare("SELECT user_id FROM users WHERE email = :email");
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();
        if ($checkStmt->fetch()) {
            // Email already exists, redirect with error
            header("Location: index.php?error=email_exists");
            exit;
        }

        // Create user record for tenant
        $password = password_hash(generateToken(8), PASSWORD_DEFAULT);
        $userStmt = db()->prepare("INSERT INTO users (fullname, email, password, phone, role) VALUES (:name, :email, :password, :phone, 'tenant')");
        $userStmt->bindParam(':name', $name);
        $userStmt->bindParam(':email', $email);
        $userStmt->bindParam(':password', $password);
        $userStmt->bindParam(':phone', $phone);
        $userStmt->execute();
        $userId = db()->lastInsertId();

        // Create tenant profile
        $stmt = db()->prepare("INSERT INTO tenants (user_id, phone) VALUES (:user_id, :phone)");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        $tenantId = db()->lastInsertId();

        // Get unit details for rent and deposit
        $unitStmt = db()->prepare("SELECT monthly_rent, security_deposit FROM units WHERE unit_id = :unit_id");
        $unitStmt->bindParam(':unit_id', $unitId);
        $unitStmt->execute();
        $unit = $unitStmt->fetch(PDO::FETCH_ASSOC);
        $monthlyRent = $unit['monthly_rent'];
        $depositAmount = $unit['security_deposit'];

        // Create lease record
        $leaseStmt = db()->prepare("INSERT INTO leases (tenant_id, unit_id, start_date, end_date, monthly_rent, deposit_amount, status) VALUES (:tenant_id, :unit_id, :lease_start, :lease_end, :monthly_rent, :deposit_amount, :status)");
        $leaseStmt->bindParam(':tenant_id', $tenantId);
        $leaseStmt->bindParam(':unit_id', $unitId);
        $leaseStmt->bindParam(':lease_start', $leaseStart);
        $leaseStmt->bindParam(':lease_end', $leaseEnd);
        $leaseStmt->bindParam(':monthly_rent', $monthlyRent);
        $leaseStmt->bindParam(':deposit_amount', $depositAmount);
        $leaseStmt->bindParam(':status', $status);
        $leaseStmt->execute();
    }

    // Update unit status
    $updateUnit = db()->prepare("UPDATE units SET status = 'occupied' WHERE unit_id = :id");
    $updateUnit->bindParam(':id', $unitId);
    $updateUnit->execute();

    redirect('index.php');
}

// Get units for AJAX
if (isset($_GET['get_units']) && isset($_GET['property_id'])) {
    $units = getUnits((int)$_GET['property_id']);
    echo json_encode($units);
    exit;
}
?>
<?php include '../includes/header.php'; ?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tenants</h1>
        <p class="mt-1 text-sm text-gray-500">Manage your tenant database</p>
    </div>
    <button onclick="openModal()" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-plus mr-2"></i>
        Add Tenant
    </button>
</div>

<!-- Tenants Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($tenants as $tenant): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
        <div class="flex items-start justify-between">
            <div class="flex items-center space-x-4">
                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                    <?= strtoupper(substr($tenant['tenant_name'], 0, 1) . substr(strstr($tenant['tenant_name'], ' '), 1, 1)) ?>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($tenant['tenant_name']) ?></h3>
                    <?= getStatusBadge($tenant['lease_status'] ?? 'active') ?>
                </div>
            </div>
            <div class="relative group">
                <button class="p-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="absolute right-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-100 hidden group-hover:block z-10">
                    <button onclick="editTenant(<?= htmlspecialchars(json_encode($tenant)) ?>)" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </button>
                    <button onclick="confirmDelete(<?= $tenant['tenant_id'] ?>)" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 text-red-600 flex items-center">
                        <i class="fas fa-trash mr-2"></i> Delete
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mt-4 space-y-3">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-envelope w-5 mr-2 text-gray-400"></i>
                <?= htmlspecialchars($tenant['tenant_email'] ?? 'No email') ?>
            </div>
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-phone w-5 mr-2 text-gray-400"></i>
                <?= htmlspecialchars($tenant['phone'] ?? 'No phone') ?>
            </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center text-sm">
                <div class="flex-1">
                    <p class="text-gray-500">Property</p>
                    <p class="font-medium text-gray-900"><?= $tenant['property_name'] ? htmlspecialchars($tenant['property_name']) : 'Not assigned' ?></p>
                </div>
                <div class="flex-1">
                    <p class="text-gray-500">Unit</p>
                    <p class="font-medium text-gray-900"><?= $tenant['unit_number'] ? htmlspecialchars($tenant['unit_number']) : 'Not assigned' ?></p>
                </div>
            </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-calendar w-5 mr-2 text-gray-400"></i>
                Lease: <?= $tenant['lease_start'] ? formatDate($tenant['lease_start']) : 'Not set' ?> - <?= $tenant['lease_end'] ? formatDate($tenant['lease_end']) : 'Not set' ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($tenants)): ?>
<div class="text-center py-12">
    <i class="fas fa-users text-5xl text-gray-400"></i>
    <h3 class="mt-4 text-lg font-medium text-gray-900">No tenants found</h3>
    <p class="mt-1 text-sm text-gray-500">Get started by adding your first tenant.</p>
</div>
<?php endif; ?>

<!-- Modal -->
<div id="modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <h2 id="modalTitle" class="text-xl font-bold text-gray-900 mb-4">Add New Tenant</h2>
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 flex items-center space-x-2">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                <p class="text-sm text-red-600"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="id" id="tenant_id">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" id="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="Enter full name">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="email@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" name="phone" id="phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="(555) 123-4567">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Property</label>
                        <select name="property_id" id="property_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" onchange="loadUnits(this.value)">
                            <option value="">Select property</option>
                            <?php foreach ($properties as $property): ?>
                            <option value="<?= $property['property_id'] ?>"><?= htmlspecialchars($property['property_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <select name="unit_id" id="unit_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="">Select unit</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lease Start</label>
                        <input type="date" name="lease_start" id="lease_start" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lease End</label>
                        <input type="date" name="lease_end" id="lease_end" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Tenant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation -->
<div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeDeleteModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Tenant?</h3>
                <p class="text-sm text-gray-500 mb-4">This action cannot be undone.</p>
                <div class="flex justify-center space-x-3">
                    <button onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                    <a id="deleteLink" href="#" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = 'Add New Tenant';
        document.getElementById('tenant_id').value = '';
        document.getElementById('name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('phone').value = '';
        document.getElementById('property_id').value = '';
        document.getElementById('unit_id').innerHTML = '<option value="">Select unit</option>';
        document.getElementById('lease_start').value = '';
        document.getElementById('lease_end').value = '';
    }
    
    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }
    
    function loadUnits(propertyId) {
        fetch('?get_units=1&property_id=' + propertyId)
            .then(response => response.json())
            .then(units => {
                const select = document.getElementById('unit_id');
                select.innerHTML = '<option value="">Select unit</option>';
                units.forEach(unit => {
                    if (unit.status === 'vacant') {
                        select.innerHTML += `<option value="${unit.unit_id}">Unit ${unit.unit_number}</option>`;
                    }
                });
            });
    }
    
    function editTenant(tenant) {
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = 'Edit Tenant';
        document.getElementById('tenant_id').value = tenant.tenant_id;
        document.getElementById('name').value = tenant.tenant_name;
        document.getElementById('email').value = tenant.tenant_email;
        document.getElementById('phone').value = tenant.phone;
        document.getElementById('property_id').value = tenant.property_id;
        document.getElementById('lease_start').value = tenant.lease_start;
        document.getElementById('lease_end').value = tenant.lease_end;
        document.getElementById('status').value = tenant.lease_status || 'active';
        
        // Load units and set current
        loadUnits(tenant.property_id);        setTimeout(() => {
            document.getElementById('unit_id').value = tenant.unit_id || '';
        }, 250);        setTimeout(() => {
            document.getElementById('unit_id').value = tenant.unit_id;
        }, 500);
    }
    
    function confirmDelete(id) {
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteLink').href = '?delete=' + id;
    }
    
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>

<?php include '../includes/footer.php'; ?>
