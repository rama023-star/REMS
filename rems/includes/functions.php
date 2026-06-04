<?php
// Helper Functions

/**
 * Sanitize input data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate random string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    if ($amount === null) {
        $amount = 0;
    }
    return '$' . number_format((float)$amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $colors = [
        'active' => 'bg-green-100 text-green-700',
        'inactive' => 'bg-gray-100 text-gray-700',
        'occupied' => 'bg-green-100 text-green-700',
        'vacant' => 'bg-blue-100 text-blue-700',
        'maintenance' => 'bg-yellow-100 text-yellow-700',
        'paid' => 'bg-green-100 text-green-700',
        'pending' => 'bg-yellow-100 text-yellow-700',
        'failed' => 'bg-red-100 text-red-700',
        'refunded' => 'bg-gray-100 text-gray-700',
        'open' => 'bg-yellow-100 text-yellow-700',
        'in_progress' => 'bg-blue-100 text-blue-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-gray-100 text-gray-700',
        'expired' => 'bg-gray-100 text-gray-700',
        'terminated' => 'bg-red-100 text-red-700',
    ];
    
    $color = $colors[$status] ?? 'bg-gray-100 text-gray-700';
    return "<span class=\"px-2 py-1 text-xs font-medium rounded-full {$color}\">" . ucfirst(str_replace('_', ' ', $status)) . "</span>";
}

/**
 * Get priority badge HTML
 */
function getPriorityBadge($priority) {
    $colors = [
        'low' => 'bg-green-100 text-green-700',
        'medium' => 'bg-yellow-100 text-yellow-700',
        'high' => 'bg-orange-100 text-orange-700',
        'urgent' => 'bg-red-100 text-red-700',
    ];
    
    $color = $colors[$priority] ?? 'bg-gray-100 text-gray-700';
    return "<span class=\"px-2 py-1 text-xs font-medium rounded-full {$color}\">" . ucfirst($priority) . "</span>";
}

/**
 * Upload file
 */
function uploadFile($file, $directory = 'uploads') {
    $uploadDir = __DIR__ . '/../assets/' . $directory . '/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $directory . '/' . $fileName;
    }
    
    return false;
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: {$url}");
    exit();
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Get all properties
 */
function getProperties($status = null) {
    $sql = "SELECT p.*, 
            COUNT(CASE WHEN u.status = 'occupied' THEN 1 END) as occupied_units
            FROM properties p 
            LEFT JOIN units u ON p.property_id = u.property_id";
    if ($status) {
        $sql .= " WHERE p.status = :status";
    }
    $sql .= " GROUP BY p.property_id ORDER BY p.created_at DESC";
    
    $stmt = db()->prepare($sql);
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get property by ID
 */
function getProperty($id) {
    $stmt = db()->prepare("SELECT * FROM properties WHERE property_id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Get units by property
 */
function getUnits($propertyId = null) {
    $sql = "SELECT u.*, p.property_name as property_name FROM units u 
            JOIN properties p ON u.property_id = p.property_id";
    if ($propertyId) {
        $sql .= " WHERE u.property_id = :property_id";
    }
    $sql .= " ORDER BY u.unit_number ASC";
    
    $stmt = db()->prepare($sql);
    if ($propertyId) {
        $stmt->bindParam(':property_id', $propertyId);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get tenants
 */
function getTenants($status = null) {
    $sql = "SELECT t.*, u.fullname as tenant_name, u.email as tenant_email, un.unit_number, un.unit_id, un.property_id, p.property_name, l.start_date as lease_start, l.end_date as lease_end, l.status as lease_status 
            FROM tenants t 
            JOIN users u ON t.user_id = u.user_id 
            LEFT JOIN leases l ON t.tenant_id = l.tenant_id 
            LEFT JOIN units un ON l.unit_id = un.unit_id 
            LEFT JOIN properties p ON un.property_id = p.property_id";
    $sql .= " ORDER BY t.created_at DESC";
    
    $stmt = db()->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get leases
 */
function getLeases($status = null) {
    $sql = "SELECT l.*, usr.fullname as tenant_name, p.property_name as property_name, u.unit_number 
            FROM leases l 
            JOIN tenants t ON l.tenant_id = t.tenant_id 
            JOIN users usr ON t.user_id = usr.user_id 
            JOIN units u ON l.unit_id = u.unit_id 
            JOIN properties p ON u.property_id = p.property_id";
    if ($status) {
        $sql .= " WHERE l.status = :status";
    }
    $sql .= " ORDER BY l.created_at DESC";
    
    $stmt = db()->prepare($sql);
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get payments
 */
function getPayments($status = null) {
    $sql = "SELECT pay.*, pay.payment_method as method, pay.receipt_number as reference, 'Payment' as type, usr.fullname as tenant_name, p.property_name as property_name, u.unit_number 
            FROM payments pay 
            JOIN leases l ON pay.lease_id = l.lease_id 
            JOIN tenants t ON l.tenant_id = t.tenant_id 
            JOIN users usr ON t.user_id = usr.user_id 
            JOIN units u ON l.unit_id = u.unit_id 
            JOIN properties p ON u.property_id = p.property_id";
    if ($status) {
        $sql .= " WHERE pay.status = :status";
    }
    $sql .= " ORDER BY pay.payment_date DESC";
    
    $stmt = db()->prepare($sql);
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get maintenance requests
 */
function getMaintenanceRequests($status = null) {
    $sql = "SELECT m.*, p.property_name as property_name, u.unit_number, usr.fullname as tenant_name 
            FROM maintenance_requests m 
            JOIN units u ON m.unit_id = u.unit_id 
            JOIN properties p ON u.property_id = p.property_id 
            JOIN tenants t ON m.tenant_id = t.tenant_id 
            JOIN users usr ON t.user_id = usr.user_id";
    if ($status) {
        $sql .= " WHERE m.status = :status";
    }
    $sql .= " ORDER BY 
            CASE m.priority 
                WHEN 'urgent' THEN 1 
                WHEN 'high' THEN 2 
                WHEN 'medium' THEN 3 
                WHEN 'low' THEN 4 
            END, 
            m.created_at DESC";
    
    $stmt = db()->prepare($sql);
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get dashboard statistics
 */
function getDashboardStats() {
    $stats = [];
    
    // Total properties
    $stmt = db()->query("SELECT COUNT(*) as count FROM properties WHERE status = 'active'");
    $stats['total_properties'] = $stmt->fetch()['count'];
    
    // Total units
    $stmt = db()->query("SELECT COUNT(*) as count FROM units");
    $stats['total_units'] = $stmt->fetch()['count'];
    
    // Occupied units
    $stmt = db()->query("SELECT COUNT(*) as count FROM units WHERE status = 'occupied'");
    $stats['occupied_units'] = $stmt->fetch()['count'];
    
    // Total tenants
    $stmt = db()->query("SELECT COUNT(*) as count FROM tenants");
    $stats['total_tenants'] = $stmt->fetch()['count'];
    
    // Monthly revenue
    $stmt = db()->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'paid' AND MONTH(payment_date) = MONTH(CURRENT_DATE())");
    $stats['monthly_revenue'] = $stmt->fetch()['total'];
    
    // Pending payments count
    $stmt = db()->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'");
    $stats['pending_payments'] = $stmt->fetch()['count'];
    
    // Open maintenance requests
    $stmt = db()->query("SELECT COUNT(*) as count FROM maintenance_requests WHERE status = 'open'");
    $stats['open_maintenance'] = $stmt->fetch()['count'];
    
    // Occupancy rate
    $stats['occupancy_rate'] = $stats['total_units'] > 0 
        ? round(($stats['occupied_units'] / $stats['total_units']) * 100) 
        : 0;
    
    return $stats;
}
?>
