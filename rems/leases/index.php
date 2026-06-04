<?php
require_once '../includes/config.php';
requireLogin();

$currentPage = 'leases';
$pageTitle = 'Leases';

$leases = getLeases();
$properties = getProperties();
$tenants = getTenants('active');

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = db()->prepare("DELETE FROM leases WHERE lease_id = :lease_id");
    $stmt->bindParam(':lease_id', $_GET['delete']);
    if ($stmt->execute()) {
        redirect('index.php');
    }
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenantId = (int)$_POST['tenant_id'];
    $unitId = (int)$_POST['unit_id'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $monthlyRent = (float)$_POST['monthly_rent'];
    $depositAmount = (float)$_POST['deposit_amount'];
    $status = $_POST['status'];
    
    $stmt = db()->prepare("INSERT INTO leases (tenant_id, unit_id, start_date, end_date, monthly_rent, deposit_amount, status) VALUES (:tenant_id, :unit_id, :start_date, :end_date, :monthly_rent, :deposit_amount, :status)");
    $stmt->bindParam(':tenant_id', $tenantId);
    $stmt->bindParam(':unit_id', $unitId);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->bindParam(':monthly_rent', $monthlyRent);
    $stmt->bindParam(':deposit_amount', $depositAmount);
    $stmt->bindParam(':status', $status);
    
    if ($stmt->execute()) {
        redirect('index.php');
    }
}

// Get days remaining
function getDaysRemaining($endDate) {
    $end = new DateTime($endDate);
    $today = new DateTime();
    return $end->diff($today)->days * ($end > $today ? 1 : -1);
}
?>
<?php include '../includes/header.php'; ?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Leases</h1>
        <p class="mt-1 text-sm text-gray-500">Manage lease agreements</p>
    </div>
    <button onclick="openModal()" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-plus mr-2"></i>
        New Lease
    </button>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Leases</p>
                <p class="text-2xl font-bold text-green-600"><?= count(array_filter($leases, fn($l) => $l['status'] === 'active')) ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-file-contract text-green-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Expiring Soon</p>
                <p class="text-2xl font-bold text-yellow-600"><?= count(array_filter($leases, fn($l) => $l['status'] === 'active' && getDaysRemaining($l['end_date']) <= 30 && getDaysRemaining($l['end_date']) > 0)) ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                <i class="fas fa-calendar text-yellow-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Expired</p>
                <p class="text-2xl font-bold text-gray-600"><?= count(array_filter($leases, fn($l) => $l['status'] === 'expired')) ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                <i class="fas fa-file-contract text-gray-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Monthly Revenue</p>
                <p class="text-2xl font-bold text-blue-600"><?= formatCurrency(array_reduce(array_filter($leases, fn($l) => $l['status'] === 'active'), fn($sum, $l) => $sum + $l['monthly_rent'], 0)) ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-dollar-sign text-blue-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Leases Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property / Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly Rent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($leases as $lease): ?>
                <?php $daysRemaining = getDaysRemaining($lease['end_date']); ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-sm font-medium text-blue-600"><?= strtoupper(substr($lease['tenant_name'], 0, 1) . substr(strstr($lease['tenant_name'], ' '), 1, 1)) ?></span>
                            </div>
                            <div class="ml-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($lease['tenant_name']) ?></div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($lease['property_name']) ?></div>
                        <div class="text-sm text-gray-500">Unit <?= htmlspecialchars($lease['unit_number']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= formatDate($lease['start_date']) ?> - <?= formatDate($lease['end_date']) ?></div>
                        <?php if ($lease['status'] === 'active' && $daysRemaining <= 30 && $daysRemaining > 0): ?>
                        <div class="text-xs text-yellow-600 mt-1">Expires in <?= $daysRemaining ?> days</div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= formatCurrency($lease['monthly_rent']) ?>/mo</div>
                        <div class="text-xs text-gray-500">Deposit: <?= formatCurrency($lease['deposit_amount'] ?? 0) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= getStatusBadge($lease['status']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="viewLease(<?= htmlspecialchars(json_encode($lease)) ?>)" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-eye"></i></button>
                        <a href="?delete=<?= $lease['lease_id'] ?>" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6">
            <h2 id="modalTitle" class="text-xl font-bold text-gray-900 mb-4">Create New Lease</h2>
            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tenant</label>
                        <select name="tenant_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="">Select tenant</option>
                            <?php foreach ($tenants as $tenant): ?>
                            <option value="<?= $tenant['tenant_id'] ?>"><?= htmlspecialchars($tenant['tenant_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Property</label>
                        <select name="property_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="">Select property</option>
                            <?php foreach ($properties as $property): ?>
                            <option value="<?= $property['property_id'] ?>"><?= htmlspecialchars($property['property_name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <input type="hidden" name="unit_id" value="1">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Rent</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input type="number" step="0.01" name="monthly_rent" required class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Security Deposit</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input type="number" step="0.01" name="deposit_amount" required class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create Lease</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeViewModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Lease Details</h2>
            <div id="leaseDetails" class="space-y-4"></div>
            <div class="mt-6 flex justify-end">
                <button onclick="closeViewModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }
    function closeViewModal() { document.getElementById('viewModal').classList.add('hidden'); }
    
    function viewLease(lease) {
        document.getElementById('viewModal').classList.remove('hidden');
        document.getElementById('leaseDetails').innerHTML = `
            <div class="bg-blue-50 rounded-lg p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Tenant</p>
                    <p class="text-xl font-semibold text-blue-600">${lease.tenant_name}</p>
                </div>
                ${getStatusBadge(lease.status)}
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-sm text-gray-500">Property</p><p class="font-medium">${lease.property_name}</p></div>
                <div><p class="text-sm text-gray-500">Unit</p><p class="font-medium">${lease.unit_number}</p></div>
                <div><p class="text-sm text-gray-500">Start Date</p><p class="font-medium">${lease.start_date}</p></div>
                <div><p class="text-sm text-gray-500">End Date</p><p class="font-medium">${lease.end_date}</p></div>
                <div><p class="text-sm text-gray-500">Monthly Rent</p><p class="font-medium text-green-600">$${parseFloat(lease.monthly_rent).toLocaleString()}</p></div>
                <div><p class="text-sm text-gray-500">Security Deposit</p><p class="font-medium">$${parseFloat(lease.deposit_amount || 0).toLocaleString()}</p></div>
            </div>
        `;
    }
</script>

<?php include '../includes/footer.php'; ?>
