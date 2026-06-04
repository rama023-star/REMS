<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
require_once '../includes/config.php';
// requireLogin(); // Temporarily disabled for testing

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stats = getDashboardStats();

        // Get revenue data for chart
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
        $revenueData = $revenueStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get occupancy data
        $totalUnits = $stats['total_units'];
        $occupiedUnits = $stats['occupied_units'];
        $vacantUnits = $totalUnits - $occupiedUnits;

        $occupancyData = [
            ['name' => 'Occupied', 'value' => $occupiedUnits, 'color' => '#3B82F6'],
            ['name' => 'Vacant', 'value' => $vacantUnits, 'color' => '#E5E7EB']
        ];

        // Get properties by type
        $typeStmt = db()->query("
            SELECT property_type, COUNT(*) as count
            FROM properties
            WHERE status = 'active'
            GROUP BY property_type
        ");
        $propertiesByType = $typeStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get recent payments
        $paymentsStmt = db()->query("
            SELECT pay.*, usr.fullname as tenant_name, p.property_name, u.unit_number
            FROM payments pay
            JOIN leases l ON pay.lease_id = l.lease_id
            JOIN tenants t ON l.tenant_id = t.tenant_id
            JOIN users usr ON t.user_id = usr.user_id
            JOIN units u ON l.unit_id = u.unit_id
            JOIN properties p ON u.property_id = p.property_id
            ORDER BY pay.payment_date DESC
            LIMIT 5
        ");
        $recentPayments = $paymentsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get recent maintenance
        $maintenanceStmt = db()->query("
            SELECT m.*, p.property_name, u.unit_number, usr.fullname as tenant_name
            FROM maintenance_requests m
            JOIN units u ON m.unit_id = u.unit_id
            JOIN properties p ON u.property_id = p.property_id
            JOIN tenants t ON m.tenant_id = t.tenant_id
            JOIN users usr ON t.user_id = usr.user_id
            ORDER BY m.created_at DESC
            LIMIT 5
        ");
        $recentMaintenance = $maintenanceStmt->fetchAll(PDO::FETCH_ASSOC);

        jsonResponse([
            'stats' => $stats,
            'revenueData' => $revenueData,
            'occupancyData' => $occupancyData,
            'propertiesByType' => $propertiesByType,
            'recentPayments' => $recentPayments,
            'recentMaintenance' => $recentMaintenance
        ]);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
?>