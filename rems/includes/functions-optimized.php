<?php
// Optimized Functions with Combined Queries and Pagination Support

/**
 * Get optimized dashboard statistics in a single query
 * Instead of 8 separate queries, this uses subqueries for ~8x speed improvement
 */
function getDashboardStatsOptimized() {
    $sql = "
        SELECT 
            (SELECT COUNT(*) FROM properties WHERE status = 'active') as total_properties,
            (SELECT COUNT(*) FROM units) as total_units,
            (SELECT COUNT(*) FROM units WHERE status = 'occupied') as occupied_units,
            (SELECT COUNT(*) FROM tenants WHERE status = 'active') as total_tenants,
            COALESCE((SELECT SUM(amount) FROM payments WHERE status = 'paid' AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())), 0) as monthly_revenue,
            (SELECT COUNT(*) FROM payments WHERE status = 'pending') as pending_payments,
            (SELECT COUNT(*) FROM maintenance_requests WHERE status = 'open') as open_maintenance
    ";
    
    $stmt = db()->query($sql);
    $result = $stmt->fetch();
    
    // Calculate occupancy rate
    $result['occupancy_rate'] = $result['total_units'] > 0 
        ? round(($result['occupied_units'] / $result['total_units']) * 100) 
        : 0;
    
    return $result;
}

/**
 * Get properties with pagination
 */
function getPropertiesOptimized($status = null, $limit = 50, $offset = 0) {
    $sql = "SELECT p.*, 
            COUNT(CASE WHEN u.status = 'occupied' THEN 1 END) as occupied_units
            FROM properties p 
            LEFT JOIN units u ON p.property_id = u.property_id";
    
    if ($status) {
        $sql .= " WHERE p.status = :status";
    }
    
    $sql .= " GROUP BY p.property_id ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = db()->prepare($sql);
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Get units with pagination and filtering
 */
function getUnitsOptimized($propertyId = null, $limit = 50, $offset = 0) {
    $sql = "SELECT u.*, p.property_name FROM units u 
            JOIN properties p ON u.property_id = p.property_id";
    
    if ($propertyId) {
        $sql .= " WHERE u.property_id = :property_id";
    }
    
    $sql .= " ORDER BY u.unit_number ASC LIMIT :limit OFFSET :offset";
    
    $stmt = db()->prepare($sql);
    if ($propertyId) {
        $stmt->bindParam(':property_id', $propertyId);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Get tenants with pagination - optimized joins
 */
function getTenantsOptimized($status = null, $limit = 50, $offset = 0) {
    $sql = "SELECT t.*, u.unit_number, p.property_name, 
                   l.start_date as lease_start, l.end_date as lease_end, l.status as lease_status
            FROM tenants t 
            LEFT JOIN units u ON t.unit_id = u.unit_id 
            LEFT JOIN properties p ON t.property_id = p.property_id
            LEFT JOIN leases l ON t.id = l.tenant_id AND l.status = 'active'";
    
    if ($status) {
        $sql .= " WHERE t.status = :status";
    }
    
    $sql .= " ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = db()->prepare($sql);
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Get leases with pagination
 */
function getLeasesOptimized($status = null, $limit = 50, $offset = 0) {
    $sql = "SELECT l.*, usr.name as tenant_name, p.property_name, u.unit_number 
            FROM leases l 
            JOIN tenants t ON l.tenant_id = t.id 
            JOIN users usr ON t.id = usr.id 
            JOIN units u ON l.unit_id = u.unit_id 
            JOIN properties p ON l.property_id = p.property_id";
    
    if ($status) {
        $sql .= " WHERE l.status = :status";
    }
    
    $sql .= " ORDER BY l.created_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = db()->prepare($sql);
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Get payments with pagination and filtering
 */
function getPaymentsOptimized($status = null, $limit = 50, $offset = 0) {
    $sql = "SELECT p.*, t.name as tenant_name, pr.property_name, u.unit_number 
            FROM payments p 
            JOIN leases l ON p.id = l.id 
            JOIN tenants t ON l.tenant_id = t.id 
            JOIN units u ON p.unit_id = u.unit_id 
            JOIN properties pr ON p.property_id = pr.property_id";
    
    if ($status) {
        $sql .= " WHERE p.status = :status";
    }
    
    $sql .= " ORDER BY p.payment_date DESC LIMIT :limit OFFSET :offset";
    
    $stmt = db()->prepare($sql);
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Get maintenance requests with pagination and priority sorting
 */
function getMaintenanceRequestsOptimized($status = null, $limit = 50, $offset = 0) {
    $sql = "SELECT m.*, p.property_name, u.unit_number, t.name as tenant_name 
            FROM maintenance_requests m 
            JOIN units u ON m.unit_id = u.unit_id 
            JOIN properties p ON m.property_id = p.property_id 
            JOIN tenants t ON m.tenant_id = t.id";
    
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
            m.created_at DESC 
            LIMIT :limit OFFSET :offset";
    
    $stmt = db()->prepare($sql);
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Get revenue data with caching support
 */
function getRevenueDataOptimized($months = 6) {
    // Try to get from cache first
    $cacheKey = 'revenue_data_' . $months . '_months';
    $cached = getCachedData($cacheKey);
    
    if ($cached !== null) {
        return $cached;
    }
    
    $sql = "
        SELECT 
            DATE_FORMAT(payment_date, '%b') as month,
            DATE_FORMAT(payment_date, '%Y-%m') as month_key,
            SUM(amount) as revenue
        FROM payments 
        WHERE status = 'paid' 
        AND payment_date >= DATE_SUB(CURRENT_DATE, INTERVAL :months MONTH)
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY payment_date ASC
    ";
    
    $stmt = db()->prepare($sql);
    $stmt->bindParam(':months', $months, PDO::PARAM_INT);
    $stmt->execute();
    $revenueData = $stmt->fetchAll();
    
    // Cache for 1 hour
    setCachedData($cacheKey, $revenueData, 3600);
    
    return $revenueData;
}

/**
 * Simple file-based caching for data
 */
function getCachedData($key) {
    $cacheDir = __DIR__ . '/../cache/';
    $cacheFile = $cacheDir . md5($key) . '.cache';
    
    if (file_exists($cacheFile)) {
        $data = @unserialize(file_get_contents($cacheFile));
        if ($data !== false && isset($data['expires']) && $data['expires'] > time()) {
            return $data['value'];
        }
        @unlink($cacheFile);
    }
    
    return null;
}

function setCachedData($key, $value, $ttl = 3600) {
    $cacheDir = __DIR__ . '/../cache/';
    
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . md5($key) . '.cache';
    $data = [
        'value' => $value,
        'expires' => time() + $ttl
    ];
    
    @file_put_contents($cacheFile, serialize($data));
}

/**
 * Count total records for pagination
 */
function countRecords($table, $whereClause = '') {
    $sql = "SELECT COUNT(*) as count FROM $table";
    if ($whereClause) {
        $sql .= " WHERE $whereClause";
    }
    $stmt = db()->query($sql);
    return $stmt->fetch()['count'];
}
?>
