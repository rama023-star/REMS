<?php
require_once '../includes/config.php';
requireLogin();

$currentPage = 'reports';
$pageTitle = 'Reports';

$stats = getDashboardStats();

// Monthly revenue data
$revenueStmt = db()->query("
    SELECT 
        DATE_FORMAT(payment_date, '%b') as month,
        SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as revenue,
        SUM(amount * 0.3) as expenses
    FROM payments 
    WHERE payment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY payment_date ASC
");
$revenueData = $revenueStmt->fetchAll();

// Occupancy by property
$occupancyStmt = db()->query("
    SELECT 
        p.property_name,
        SUM(CASE WHEN u.status = 'occupied' THEN 1 ELSE 0 END) as occupied,
        SUM(CASE WHEN u.status = 'vacant' THEN 1 ELSE 0 END) as vacant
    FROM properties p
    LEFT JOIN units u ON p.property_id = u.property_id
    WHERE p.status = 'active'
    GROUP BY p.property_id
    ORDER BY p.property_name
");
$occupancyData = $occupancyStmt->fetchAll();

// Payment distribution
$paymentDistStmt = db()->query("
    SELECT 
        SUM(CASE WHEN status = 'paid' AND DATEDIFF(CURRENT_DATE, payment_date) <= 5 THEN 1 ELSE 0 END) as on_time,
        SUM(CASE WHEN status = 'paid' AND DATEDIFF(CURRENT_DATE, payment_date) > 5 THEN 1 ELSE 0 END) as late,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as outstanding
    FROM payments
    WHERE payment_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
");
$paymentDist = $paymentDistStmt->fetch();

$totalRevenue = array_reduce($revenueData, fn($sum, $r) => $sum + $r['revenue'], 0);
?>
<?php include '../includes/header.php'; ?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
        <p class="mt-1 text-sm text-gray-500">Analytics and insights for your portfolio</p>
    </div>
    <button onclick="window.print()" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-download mr-2"></i>
        Export Report
    </button>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Revenue</p>
                <p class="text-2xl font-bold text-green-600"><?= formatCurrency($stats['monthly_revenue']) ?></p>
                <p class="text-xs text-green-600 mt-1">+12% from last period</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Properties</p>
                <p class="text-2xl font-bold text-blue-600"><?= $stats['total_properties'] ?></p>
                <p class="text-xs text-blue-600 mt-1"><?= $stats['occupied_units'] ?> occupied units</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-building text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Tenants</p>
                <p class="text-2xl font-bold text-purple-600"><?= $stats['total_tenants'] ?></p>
                <p class="text-xs text-purple-600 mt-1">+3 new this month</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                <i class="fas fa-users text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Occupancy Rate</p>
                <p class="text-2xl font-bold text-yellow-600"><?= $stats['occupancy_rate'] ?>%</p>
                <p class="text-xs text-yellow-600 mt-1">+2% from last month</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
                <i class="fas fa-chart-line text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Revenue vs Expenses -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Revenue vs Expenses</h2>
        <canvas id="revenueChart" height="280"></canvas>
    </div>
    
    <!-- Revenue Trend -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend</h2>
        <canvas id="trendChart" height="280"></canvas>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Occupancy by Property -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Occupancy by Property</h2>
        <canvas id="occupancyChart" height="250"></canvas>
    </div>
    
    <!-- Payment Distribution -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Status</h2>
        <canvas id="paymentChart" height="200"></canvas>
    </div>
</div>

<!-- Quick Reports -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Reports</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <button class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-green-100 mr-3"><i class="fas fa-dollar-sign text-green-600"></i></div>
                <span class="text-sm font-medium text-gray-700">Monthly Income Statement</span>
            </div>
            <i class="fas fa-download text-gray-400"></i>
        </button>
        <button class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-blue-100 mr-3"><i class="fas fa-building text-blue-600"></i></div>
                <span class="text-sm font-medium text-gray-700">Occupancy Report</span>
            </div>
            <i class="fas fa-download text-gray-400"></i>
        </button>
        <button class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-purple-100 mr-3"><i class="fas fa-users text-purple-600"></i></div>
                <span class="text-sm font-medium text-gray-700">Tenant Ledger</span>
            </div>
            <i class="fas fa-download text-gray-400"></i>
        </button>
        <button class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-yellow-100 mr-3"><i class="fas fa-tools text-yellow-600"></i></div>
                <span class="text-sm font-medium text-gray-700">Maintenance Summary</span>
            </div>
            <i class="fas fa-download text-gray-400"></i>
        </button>
        <button class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-red-100 mr-3"><i class="fas fa-calendar text-red-600"></i></div>
                <span class="text-sm font-medium text-gray-700">Lease Expiration Report</span>
            </div>
            <i class="fas fa-download text-gray-400"></i>
        </button>
        <button class="flex items-center justify-between p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="p-2 rounded-lg bg-indigo-100 mr-3"><i class="fas fa-chart-pie text-indigo-600"></i></div>
                <span class="text-sm font-medium text-gray-700">Financial Summary</span>
            </div>
            <i class="fas fa-download text-gray-400"></i>
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revenueLabels = <?= json_encode(array_column($revenueData, 'month')) ?>;
    const revenueValues = <?= json_encode(array_column($revenueData, 'revenue')) ?>;
    const expenseValues = <?= json_encode(array_column($revenueData, 'expenses')) ?>;
    const occupancyData = <?= json_encode($occupancyData) ?>;
    
    // Revenue vs Expenses Chart
    new Chart(document.getElementById('revenueChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: revenueLabels,
            datasets: [
                { label: 'Revenue', data: revenueValues, backgroundColor: '#3B82F6', borderRadius: 4 },
                { label: 'Expenses', data: expenseValues, backgroundColor: '#EF4444', borderRadius: 4 }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
    
    // Trend Chart
    new Chart(document.getElementById('trendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{ label: 'Revenue', data: revenueValues, borderColor: '#3B82F6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4 }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
    
    // Occupancy Chart
    new Chart(document.getElementById('occupancyChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: occupancyData.map(d => d.name),
            datasets: [
                { label: 'Occupied', data: occupancyData.map(d => d.occupied), backgroundColor: '#10B981', borderRadius: 4 },
                { label: 'Vacant', data: occupancyData.map(d => d.vacant), backgroundColor: '#E5E7EB', borderRadius: 4 }
            ]
        },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
    
    // Payment Chart
    new Chart(document.getElementById('paymentChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['On-Time', 'Late', 'Outstanding'],
            datasets: [{
                data: [<?= $paymentDist['on_time'] ?? 0 ?>, <?= $paymentDist['late'] ?? 0 ?>, <?= $paymentDist['outstanding'] ?? 0 ?>],
                backgroundColor: ['#10B981', '#F59E0B', '#EF4444']
            }]
        },
        options: { responsive: true, cutout: '60%', plugins: { legend: { position: 'bottom' } } }
    });
</script>

<?php include '../includes/footer.php'; ?>
