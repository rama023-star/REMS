<?php
require_once '../includes/config.php';
requireLogin();

$currentPage = 'payments';
$pageTitle = 'Payments';

$payments = getPayments();
$properties = getProperties();
$tenants = getTenants('active');

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenantId = (int)$_POST['tenant_id'];
    $amount = (float)$_POST['amount'];
    $type = $_POST['type'];
    $method = $_POST['method'];
    $paymentDate = $_POST['payment_date'];
    $reference = sanitize($_POST['reference'] ?? '');
    $status = $_POST['status'];
    
    // Get an active lease for the selected tenant
    $leaseStmt = db()->prepare("SELECT lease_id FROM leases WHERE tenant_id = :tenant_id AND status = 'active' ORDER BY lease_id DESC LIMIT 1");
    $leaseStmt->bindParam(':tenant_id', $tenantId);
    $leaseStmt->execute();
    $lease = $leaseStmt->fetch();
    
    $dueDate = $paymentDate;
    $stmt = db()->prepare("INSERT INTO payments (lease_id, amount, payment_date, due_date, payment_method, receipt_number, status) VALUES (:lease_id, :amount, :payment_date, :due_date, :payment_method, :receipt_number, :status)");
    $stmt->bindParam(':lease_id', $lease['lease_id']);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':payment_date', $paymentDate);
    $stmt->bindParam(':due_date', $dueDate);
    $stmt->bindParam(':payment_method', $method);
    $stmt->bindParam(':receipt_number', $reference);
    $stmt->bindParam(':status', $status);
    
    if ($stmt->execute()) {
        redirect('index.php');
    }
}

// Summary
$totalPaid = array_reduce(array_filter($payments, fn($p) => $p['status'] === 'paid'), fn($sum, $p) => $sum + $p['amount'], 0);
$totalPending = array_reduce(array_filter($payments, fn($p) => $p['status'] === 'pending'), fn($sum, $p) => $sum + $p['amount'], 0);
?>
<?php include '../includes/header.php'; ?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
        <p class="mt-1 text-sm text-gray-500">Track and manage payment transactions</p>
    </div>
    <button onclick="openModal()" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-plus mr-2"></i>
        Record Payment
    </button>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Collected</p>
                <p class="text-2xl font-bold text-green-600"><?= formatCurrency($totalPaid) ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending</p>
                <p class="text-2xl font-bold text-yellow-600"><?= formatCurrency($totalPending) ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Transactions</p>
                <p class="text-2xl font-bold text-blue-600"><?= count($payments) ?></p>
            </div>
            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-receipt text-blue-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property / Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($payments as $payment): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 capitalize"><?= $payment['type'] ?></div>
                                <div class="text-sm text-gray-500"><?= formatDate($payment['payment_date']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($payment['tenant_name']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= htmlspecialchars($payment['property_name']) ?></div>
                        <div class="text-sm text-gray-500">Unit <?= htmlspecialchars($payment['unit_number']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-gray-900"><?= formatCurrency($payment['amount']) ?></div>
                        <?php if ($payment['reference']): ?>
                        <div class="text-xs text-gray-500">Ref: <?= htmlspecialchars($payment['reference']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-<?= $payment['method'] === 'card' ? 'credit-card' : ($payment['method'] === 'bank_transfer' ? 'university' : ($payment['method'] === 'check' ? 'money-check' : 'money-bill')) ?> mr-2"></i>
                            <span class="capitalize"><?= str_replace('_', ' ', $payment['method']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= getStatusBadge($payment['status']) ?></td>
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
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Record Payment</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tenant</label>
                    <select name="tenant_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                        <option value="">Select tenant</option>
                        <?php foreach ($tenants as $tenant): ?>
                        <option value="<?= $tenant['tenant_id'] ?>"><?= htmlspecialchars($tenant['tenant_name']) ?> - Unit <?= htmlspecialchars($tenant['unit_number']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input type="number" step="0.01" name="amount" required class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="rent">Rent</option>
                            <option value="deposit">Deposit</option>
                            <option value="fee">Fee</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
                        <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Method</label>
                        <select name="method" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference (Optional)</label>
                        <input type="text" name="reference" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" placeholder="TXN001">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Record Payment</button>
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
