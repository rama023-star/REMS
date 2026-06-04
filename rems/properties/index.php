<?php
require_once '../includes/config.php';
requireLogin();

$currentPage = 'properties';
$pageTitle = 'Properties';

$properties = getProperties();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = db()->prepare("DELETE FROM properties WHERE property_id = :id");
    $stmt->bindParam(':id', $_GET['delete']);
    if ($stmt->execute()) {
        redirect('index.php');
    }
}

// Handle add/edit
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = sanitize($_POST['property_name']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $zip = sanitize($_POST['zip']);
    $type = $_POST['property_type'];
    $totalUnits = (int)$_POST['total_units'];
    $status = $_POST['status'];
    
    if ($id) {
        $stmt = db()->prepare("UPDATE properties SET property_name = :name, address = :address, city = :city, state = :state, zip = :zip, property_type = :type, total_units = :total_units, status = :status WHERE property_id = :id");
        $stmt->bindParam(':id', $id);
    } else {
        $stmt = db()->prepare("INSERT INTO properties (property_name, address, city, state, zip, property_type, total_units, status, owner_id) VALUES (:name, :address, :city, :state, :zip, :type, :total_units, :status, :owner_id)");
        $stmt->bindParam(':owner_id', $_SESSION['user_id']);
    }
    
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':state', $state);
    $stmt->bindParam(':zip', $zip);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':total_units', $totalUnits);
    $stmt->bindParam(':status', $status);
    
    if ($stmt->execute()) {
        redirect('index.php');
    }
}
?>
<?php include '../includes/header.php'; ?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Properties</h1>
        <p class="mt-1 text-sm text-gray-500">Manage your real estate portfolio</p>
    </div>
    <button onclick="openModal()" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-plus mr-2"></i>
        Add Property
    </button>
</div>

<!-- Properties Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($properties as $property): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
        <div class="relative h-48 bg-gray-200">
            <img src="<?= htmlspecialchars($property['property_image'] ?? 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=400') ?>" 
                alt="<?= htmlspecialchars($property['property_name']) ?>" class="w-full h-full object-cover">
            <div class="absolute top-3 left-3">
                <span class="px-3 py-1 text-xs font-medium rounded-full <?= $property['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                    <?= ucfirst($property['status']) ?>
                </span>
            </div>
            <div class="absolute top-3 right-3">
                <div class="relative group">
                    <button class="p-2 bg-white rounded-lg shadow hover:bg-gray-50">
                        <i class="fas fa-ellipsis-v text-gray-600"></i>
                    </button>
                    <div class="absolute right-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-100 hidden group-hover:block z-10">
                        <button onclick="editProperty(<?= htmlspecialchars(json_encode($property)) ?>)" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </button>
                        <button onclick="confirmDelete(<?= $property['property_id'] ?>)" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 text-red-600 flex items-center">
                            <i class="fas fa-trash mr-2"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-3 left-3">
                <div class="flex items-center space-x-1 text-white bg-black/50 px-2 py-1 rounded">
                    <i class="fas fa-building"></i>
                    <span class="text-sm capitalize"><?= $property['property_type'] ?></span>
                </div>
            </div>
        </div>
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($property['property_name']) ?></h3>
            <div class="flex items-center text-sm text-gray-500 mt-1">
                <i class="fas fa-map-marker-alt mr-1"></i>
                <?= htmlspecialchars($property['address']) ?>, <?= htmlspecialchars($property['city']) ?>, <?= htmlspecialchars($property['state']) ?> <?= htmlspecialchars($property['zip']) ?>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Units</p>
                    <p class="text-lg font-semibold text-gray-900"><?= $property['occupied_units'] ?>/<?= $property['total_units'] ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Occupancy</p>
                    <div class="flex items-center space-x-2">
                        <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full <?= ($property['total_units'] > 0 ? ($property['occupied_units'] / $property['total_units']) * 100 : 0) >= 90 ? 'bg-green-500' : (($property['total_units'] > 0 ? ($property['occupied_units'] / $property['total_units']) * 100 : 0) >= 70 ? 'bg-yellow-500' : 'bg-red-500') ?>" 
                                style="width: <?= $property['total_units'] > 0 ? round(($property['occupied_units'] / $property['total_units']) * 100) : 0 ?>%"></div>
                        </div>
                        <span class="text-sm font-medium"><?= $property['total_units'] > 0 ? round(($property['occupied_units'] / $property['total_units']) * 100) : 0 ?>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($properties)): ?>
<div class="text-center py-12">
    <i class="fas fa-building text-5xl text-gray-400"></i>
    <h3 class="mt-4 text-lg font-medium text-gray-900">No properties found</h3>
    <p class="mt-1 text-sm text-gray-500">Get started by adding your first property.</p>
</div>
<?php endif; ?>

<!-- Modal -->
<div id="modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <h2 id="modalTitle" class="text-xl font-bold text-gray-900 mb-4">Add New Property</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="id" id="property_id">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Property Name</label>
                    <input type="text" name="property_name" id="property_name" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" 
                        placeholder="Enter property name">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="property_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="industrial">Industrial</option>
                            <option value="land">Land</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Units</label>
                        <input type="number" name="total_units" id="total_units" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" 
                            placeholder="0">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" name="address" id="address" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" 
                        placeholder="Enter street address">
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" name="city" id="city" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" 
                            placeholder="City">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                        <input type="text" name="state" id="state" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" 
                            placeholder="State">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ZIP</label>
                        <input type="text" name="zip" id="zip" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" 
                            placeholder="ZIP">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Property</button>
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
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Property?</h3>
                <p class="text-sm text-gray-500 mb-4">This action cannot be undone. All units and related data will be deleted.</p>
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
        document.getElementById('modalTitle').textContent = 'Add New Property';
        document.getElementById('property_id').value = '';
        document.getElementById('property_name').value = '';
        document.getElementById('address').value = '';
        document.getElementById('city').value = '';
        document.getElementById('state').value = '';
        document.getElementById('zip').value = '';
        document.getElementById('total_units').value = '';
    }
    
    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }
    
    function editProperty(property) {
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = 'Edit Property';
        document.getElementById('property_id').value = property.property_id;
        document.getElementById('property_name').value = property.property_name;
        document.getElementById('address').value = property.address;
        document.getElementById('city').value = property.city;
        document.getElementById('state').value = property.state;
        document.getElementById('zip').value = property.zip;
        document.getElementById('total_units').value = property.total_units;
        document.querySelector('select[name="property_type"]').value = property.property_type;
        document.querySelector('select[name="status"]').value = property.status;
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
