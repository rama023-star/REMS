<?php
require_once '../includes/config.php';
requireLogin();

$currentPage = 'units';
$pageTitle = 'Units';

$properties = getProperties();
$units = getUnits();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = db()->prepare("DELETE FROM units WHERE unit_id = :unit_id");
    $stmt->bindParam(':unit_id', $_GET['delete']);
    if ($stmt->execute()) {
        redirect('index.php');
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $propertyId = (int)$_POST['property_id'];
    $unitNumber = sanitize($_POST['unit_number']);
    $type = $_POST['type'];
    $bedrooms = (int)str_replace(['studio', 'br', 'commercial'], ['0', '', '0'], $type);
    $sqft = (float)$_POST['sqft'];
    $monthlyRent = (float)$_POST['rent'];
    $status = $_POST['status'];
    $features = sanitize($_POST['features'] ?? '');
    
    if ($id) {
        $stmt = db()->prepare("UPDATE units SET property_id = :property_id, unit_number = :unit_number, bedrooms = :bedrooms, sqft = :sqft, monthly_rent = :monthly_rent, status = :status, features = :features WHERE unit_id = :unit_id");
        $stmt->bindParam(':unit_id', $id);
    } else {
        $stmt = db()->prepare("INSERT INTO units (property_id, unit_number, bedrooms, sqft, monthly_rent, status, features) VALUES (:property_id, :unit_number, :bedrooms, :sqft, :monthly_rent, :status, :features)");
    }
    
    $stmt->bindParam(':property_id', $propertyId);
    $stmt->bindParam(':unit_number', $unitNumber);
    $stmt->bindParam(':bedrooms', $bedrooms);
    $stmt->bindParam(':sqft', $sqft);
    $stmt->bindParam(':monthly_rent', $monthlyRent);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':features', $features);
    
    if ($stmt->execute()) {
        redirect('index.php');
    }
}

function getTypeLabel($type) {
    $labels = ['studio' => 'Studio', '1br' => '1 Bedroom', '2br' => '2 Bedroom', '3br' => '3 Bedroom', 'commercial' => 'Commercial'];
    return $labels[$type] ?? $type;
}
?>
<?php include '../includes/header.php'; ?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Units</h1>
        <p class="mt-1 text-sm text-gray-500">Manage property units and availability</p>
    </div>
    <button onclick="openModal()" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-plus mr-2"></i>
        Add Unit
    </button>
</div>

<!-- Units Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($units as $unit): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-home text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">Unit <?= htmlspecialchars($unit['unit_number']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($unit['property_name']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= getTypeLabel($unit['bedrooms'] == 0 ? 'studio' : $unit['bedrooms'] . 'br') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= number_format($unit['sqft']) ?> sq ft</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= formatCurrency($unit['monthly_rent']) ?>/mo</td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= getStatusBadge($unit['status']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="editUnit(<?= htmlspecialchars(json_encode($unit)) ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="confirmDelete(<?= $unit['unit_id'] ?>)" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (empty($units)): ?>
<div class="text-center py-12">
    <i class="fas fa-home text-5xl text-gray-400"></i>
    <h3 class="mt-4 text-lg font-medium text-gray-900">No units found</h3>
    <p class="mt-1 text-sm text-gray-500">Get started by adding your first unit.</p>
</div>
<?php endif; ?>

<!-- Modal -->
<div id="modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <h2 id="modalTitle" class="text-xl font-bold text-gray-900 mb-4">Add New Unit</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="id" id="unit_id">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Property</label>
                        <select name="property_id" id="property_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <?php foreach ($properties as $property): ?>
                            <option value="<?= $property['property_id'] ?>"><?= htmlspecialchars($property['property_name'] ?? 'Unknown') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Number</label>
                        <input type="text" name="unit_number" id="unit_number" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="e.g., 101">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" id="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="studio">Studio</option>
                            <option value="1br">1 Bedroom</option>
                            <option value="2br">2 Bedroom</option>
                            <option value="3br">3 Bedroom</option>
                            <option value="commercial">Commercial</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Size (sq ft)</label>
                        <input type="number" name="sqft" id="sqft" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="0">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Rent</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input type="number" step="0.01" name="rent" id="rent" required class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="vacant">Vacant</option>
                            <option value="occupied">Occupied</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Features (comma separated)</label>
                    <input type="text" name="features" id="features" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="e.g., Parking, Balcony, Gym">
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Unit</button>
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
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Unit?</h3>
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
        document.getElementById('modalTitle').textContent = 'Add New Unit';
        document.getElementById('unit_id').value = '';
        document.getElementById('property_id').value = '';
        document.getElementById('unit_number').value = '';
        document.getElementById('type').value = 'studio';
        document.getElementById('sqft').value = '';
        document.getElementById('rent').value = '';
        document.getElementById('status').value = 'vacant';
        document.getElementById('features').value = '';
    }
    
    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }
    
    function editUnit(unit) {
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = 'Edit Unit';
        document.getElementById('unit_id').value = unit.unit_id;
        document.getElementById('property_id').value = unit.property_id;
        document.getElementById('unit_number').value = unit.unit_number;
        document.getElementById('type').value = unit.bedrooms == 0 ? 'studio' : unit.bedrooms + 'br';
        document.getElementById('sqft').value = unit.sqft;
        document.getElementById('rent').value = unit.monthly_rent;
        document.getElementById('status').value = unit.status;
        document.getElementById('features').value = unit.features || '';
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
