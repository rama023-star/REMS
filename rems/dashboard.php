<?php
require_once 'includes/config.php';
requireLogin();

$currentPage = 'dashboard';
$pageTitle = 'Dashboard';
$stats = getDashboardStats();
$properties = getProperties('active');
$recentPayments = array_slice(getPayments(), 0, 5);
$recentMaintenance = array_slice(getMaintenanceRequests(), 0, 5);

// Get monthly revenue for chart
$revenueStmt = db()->query("
    SELECT 
        DATE_FORMAT(payment_date, '%b') as month,
        SUM(amount) as revenue
    FROM payments 
    WHERE status = 'paid' 
    AND payment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY payment_date ASC
");
$revenueData = $revenueStmt->fetchAll();

// Urgent maintenance count
$urgentStmt = db()->query("SELECT COUNT(*) as count FROM maintenance_requests WHERE priority = 'urgent' AND status = 'open'");
$urgentMaintenance = $urgentStmt->fetch()['count'];
?>
<?php include 'includes/header.php'; ?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Welcome back! Here's an overview of your properties.</p>
    </div>
    <div class="mt-4 sm:mt-0">
        <a href="properties/" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Add Property
            <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</div>

<!-- Alert for urgent maintenance -->
<?php if ($urgentMaintenance > 0): ?>
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex items-start space-x-3">
    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
    <div>
        <h3 class="font-medium text-red-800">Urgent Maintenance Required</h3>
        <p class="text-sm text-red-600 mt-1"><?= $urgentMaintenance ?> urgent maintenance request(s) need immediate attention.</p>
    </div>
    <a href="maintenance/" class="ml-auto text-sm font-medium text-red-600 hover:text-red-700">View All</a>
</div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div class="p-3 rounded-lg bg-blue-500">
                <i class="fas fa-building text-white text-xl"></i>
            </div>
            <div class="flex items-center text-sm font-medium text-green-600">
                <i class="fas fa-arrow-up mr-1"></i>+2
            </div>
        </div>
        <div class="mt-4">
            <p class="text-2xl font-bold text-gray-900"><?= $stats['total_properties'] ?></p>
            <p class="text-sm text-gray-500">Total Properties</p>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div class="p-3 rounded-lg bg-green-500">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <div class="flex items-center text-sm font-medium text-green-600">
                <i class="fas fa-arrow-up mr-1"></i>+5
            </div>
        </div>
        <div class="mt-4">
            <p class="text-2xl font-bold text-gray-900"><?= $stats['total_units'] ?></p>
            <p class="text-sm text-gray-500">Total Units</p>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div class="p-3 rounded-lg bg-purple-500">
                <i class="fas fa-users text-white text-xl"></i>
            </div>
            <div class="flex items-center text-sm font-medium text-green-600">
                <i class="fas fa-arrow-up mr-1"></i>+3
            </div>
        </div>
        <div class="mt-4">
            <p class="text-2xl font-bold text-gray-900"><?= $stats['total_tenants'] ?></p>
            <p class="text-sm text-gray-500">Active Tenants</p>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div class="p-3 rounded-lg bg-yellow-500">
                <i class="fas fa-dollar-sign text-white text-xl"></i>
            </div>
            <div class="flex items-center text-sm font-medium text-green-600">
                <i class="fas fa-arrow-up mr-1"></i>+12%
            </div>
        </div>
        <div class="mt-4">
            <p class="text-2xl font-bold text-gray-900"><?= formatCurrency($stats['monthly_revenue']) ?></p>
            <p class="text-sm text-gray-500">Monthly Revenue</p>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Revenue Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend</h2>
        <canvas id="revenueChart" height="250"></canvas>
    </div>
    
    <!-- Occupancy Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Occupancy Rate</h2>
        <div class="flex items-center justify-center">
            <canvas id="occupancyChart" height="250"></canvas>
            <div class="ml-4">
                <div class="text-4xl font-bold text-gray-900"><?= $stats['occupancy_rate'] ?>%</div>
                <p class="text-sm text-gray-500">Occupied</p>
                <p class="text-sm text-gray-500 mt-2"><?= $stats['occupied_units'] ?> of <?= $stats['total_units'] ?> units</p>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Recent Payments -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Recent Payments</h2>
            <a href="payments/" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</a>
        </div>
        <div class="space-y-3">
            <?php foreach ($recentPayments as $payment): ?>
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-green-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($payment['tenant_name']) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($payment['property_name']) ?> - Unit <?= htmlspecialchars($payment['unit_number']) ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-gray-900"><?= formatCurrency($payment['amount']) ?></p>
                    <?= getStatusBadge($payment['status']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Recent Maintenance -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Maintenance Requests</h2>
            <a href="maintenance/" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</a>
        </div>
        <div class="space-y-3">
            <?php foreach ($recentMaintenance as $request): ?>
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full flex items-center justify-center <?= $request['priority'] === 'urgent' ? 'bg-red-100' : ($request['priority'] === 'high' ? 'bg-orange-100' : 'bg-blue-100') ?>">
                        <i class="fas fa-tools <?= $request['priority'] === 'urgent' ? 'text-red-600' : ($request['priority'] === 'high' ? 'text-orange-600' : 'text-blue-600') ?>"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($request['title']) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($request['property_name']) ?> - Unit <?= htmlspecialchars($request['unit_number']) ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <?= getStatusBadge($request['status']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <a href="properties/" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition">
        <i class="fas fa-building text-3xl text-blue-600 mb-2"></i>
        <p class="font-medium text-gray-900">Manage Properties</p>
    </a>
    <a href="tenants/" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition">
        <i class="fas fa-users text-3xl text-purple-600 mb-2"></i>
        <p class="font-medium text-gray-900">Manage Tenants</p>
    </a>
    <a href="payments/" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition">
        <i class="fas fa-dollar-sign text-3xl text-green-600 mb-2"></i>
        <p class="font-medium text-gray-900">Record Payment</p>
    </a>
    <a href="reports/" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition">
        <i class="fas fa-chart-line text-3xl text-yellow-600 mb-2"></i>
        <p class="font-medium text-gray-900">View Reports</p>
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($revenueData, 'month')) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode(array_column($revenueData, 'revenue')) ?>,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => '$' + value.toLocaleString()
                    }
                }
            }
        }
    });
    
    // Occupancy Chart
    const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
    new Chart(occupancyCtx, {
        type: 'doughnut',
        data: {
            labels: ['Occupied', 'Vacant'],
            datasets: [{
                data: [<?= $stats['occupied_units'] ?>, <?= $stats['total_units'] - $stats['occupied_units'] ?>],
                backgroundColor: ['#3B82F6', '#E5E7EB'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
