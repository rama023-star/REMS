<?php
require_once '../includes/config.php';
requireLogin();

$currentPage = 'maintenance';
$pageTitle = 'Maintenance';

$requests = getMaintenanceRequests();
$properties = getProperties();
$units = getUnits();
$tenants = getTenants();

// Handle status update
if (isset($_GET['update']) && isset($_GET['id'])) {
    $stmt = db()->prepare("UPDATE maintenance_requests SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $_GET['update']);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    redirect('index.php');
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyId = (int)$_POST['property_id'];
    $unitId = (int)$_POST['unit_id'];
    $tenantId = (int)$_POST['tenant_id'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $priority = $_POST['priority'];
    $assignedTo = sanitize($_POST['assigned_to'] ?? '');
    $estimatedCost = (float)($_POST['estimated_cost'] ?? 0);
    
    $stmt = db()->prepare("INSERT INTO maintenance_requests (property_id, unit_id, tenant_id, title, description, priority, assigned_to, status, estimated_cost) VALUES (:property_id, :unit_id, :tenant_id, :title, :description, :priority, :assigned_to, 'open', :estimated_cost)");
    $stmt->bindParam(':property_id', $propertyId);
    $stmt->bindParam(':unit_id', $unitId);
    $stmt->bindParam(':tenant_id', $tenantId);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':priority', $priority);
    $stmt->bindParam(':assigned_to', $assignedTo);
    $stmt->bindParam(':estimated_cost', $estimatedCost);
    
    if ($stmt->execute()) {
        redirect('index.php');
    }
}

// Summary
$openCount = count(array_filter($requests, fn($r) => $r['status'] === 'open'));
$inProgressCount = count(array_filter($requests, fn($r) => $r['status'] === 'in_progress'));
$completedCount = count(array_filter($requests, fn($r) => $r['status'] === 'completed'));
$estimatedCosts = array_reduce($requests, fn($sum, $r) => $sum + ($r['estimated_cost'] ?? 0), 0);
?>
<?php include '../includes/header.php'; ?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Maintenance</h1>
        <p class="mt-1 text-sm text-gray-500">Manage maintenance requests and repairs</p>
    </div>
    <button onclick="openModal()" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-plus mr-2"></i>
        New Request
    </button>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Open</p>
                <p class="text-2xl font-bold text-yellow-600"><?= $openCount ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">In Progress</p>
                <p class="text-2xl font-bold text-blue-600"><?= $inProgressCount ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-tools text-blue-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Completed</p>
                <p class="text-2xl font-bold text-green-600"><?= $completedCount ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Est. Costs</p>
                <p class="text-2xl font-bold text-purple-600"><?= formatCurrency($estimatedCosts) ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                <i class="fas fa-dollar-sign text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Requests List -->
<div class="space-y-4">
    <?php foreach ($requests as $request): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4">
                <div class="h-12 w-12 rounded-lg flex items-center justify-center <?= $request['priority'] === 'urgent' ? 'bg-red-100' : ($request['priority'] === 'high' ? 'bg-orange-100' : 'bg-blue-100') ?>">
                    <i class="fas fa-tools <?= $request['priority'] === 'urgent' ? 'text-red-600' : ($request['priority'] === 'high' ? 'text-orange-600' : 'text-blue-600') ?>"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($request['title']) ?></h3>
                        <?= getPriorityBadge($request['priority']) ?>
                    </div>
                    <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($request['description']) ?></p>
                    <div class="flex items-center space-x-4 mt-3 text-sm text-gray-600">
                        <div class="flex items-center"><i class="fas fa-user mr-1"></i><?= htmlspecialchars($request['tenant_name']) ?></div>
                        <div><?= htmlspecialchars($request['property_name']) ?> - Unit <?= htmlspecialchars($request['unit_number']) ?></div>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <?= getStatusBadge($request['status']) ?>
                <?php if ($request['status'] !== 'completed'): ?>
                <div class="relative group">
                    <button class="p-2 text-gray-400 hover:text-gray-600"><i class="fas fa-ellipsis-v"></i></button>
                    <div class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg border border-gray-100 hidden group-hover:block z-10">
                        <?php if ($request['status'] === 'open'): ?>
                        <a href="?update=in_progress&id=<?= $request['id'] ?>" class="block px-4 py-2 text-sm hover:bg-gray-50">Start Progress</a>
                        <?php endif; ?>
                        <?php if ($request['status'] === 'in_progress'): ?>
                        <a href="?update=completed&id=<?= $request['id'] ?>" class="block px-4 py-2 text-sm hover:bg-gray-50 text-green-600">Mark Complete</a>
                        <?php endif; ?>
                        <a href="?update=cancelled&id=<?= $request['id'] ?>" class="block px-4 py-2 text-sm hover:bg-gray-50 text-red-600">Cancel</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
            <div class="flex items-center space-x-4 text-sm text-gray-500">
                <div class="flex items-center"><i class="fas fa-calendar mr-1"></i>Created: <?= formatDate($request['created_at']) ?></div>
                <?php if ($request['assigned_to']): ?>
                <div>Assigned: <?= htmlspecialchars($request['assigned_to']) ?></div>
                <?php endif; ?>
            </div>
            <div class="flex items-center space-x-2 text-sm">
                <?php if ($request['estimated_cost']): ?>
                <span class="text-gray-500">Est: <?= formatCurrency($request['estimated_cost']) ?></span>
                <?php endif; ?>
                <?php if ($request['actual_cost']): ?>
                <span class="text-green-600 font-medium">Actual: <?= formatCurrency($request['actual_cost']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($requests)): ?>
<div class="text-center py-12">
    <i class="fas fa-tools text-5xl text-gray-400"></i>
    <h3 class="mt-4 text-lg font-medium text-gray-900">No maintenance requests</h3>
    <p class="mt-1 text-sm text-gray-500">Create your first maintenance request.</p>
</div>
<?php endif; ?>

<!-- Modal -->
<div id="modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Create Maintenance Request</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="Brief description of the issue">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="Detailed description"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Property</label>
                        <select name="property_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <?php foreach ($properties as $property): ?>
                            <option value="<?= $property['property_id'] ?>"><?= htmlspecialchars($property['property_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <select name="unit_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <?php foreach ($units as $unit): ?>
                            <option value="<?= $unit['unit_id'] ?>"><?= htmlspecialchars($unit['unit_number'] . ' — ' . $unit['property_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tenant</label>
                        <select name="tenant_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <?php foreach ($tenants as $tenant): ?>
                            <option value="<?= $tenant['tenant_id'] ?>"><?= htmlspecialchars($tenant['tenant_name'] . ' — ' . $tenant['unit_number'] . ' / ' . $tenant['property_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Cost</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input type="number" step="0.01" name="estimated_cost" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                        <input type="text" name="assigned_to" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="Contractor name">
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }
</script>

<?php include '../includes/footer.php'; ?>
