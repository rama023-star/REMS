<?php 
$pageTitle = $pageTitle ?? 'Dashboard';
$currentPage = $currentPage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - REMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-link.active {
            background-color: #eff6ff;
            color: #1d4ed8;
            border-right: 4px solid #1d4ed8;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile sidebar backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 hidden lg:hidden" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="flex items-center h-16 px-4 border-b border-gray-200">
                <a href="<?= APP_URL ?>/dashboard.php" class="flex items-center space-x-2">
                    <i class="fas fa-building text-2xl text-blue-600"></i>
                    <span class="text-xl font-bold text-gray-900">REMS</span>
                </a>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <a href="<?= APP_URL ?>/dashboard.php" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium <?= $currentPage === 'dashboard' ? 'active' : 'text-gray-600 hover:bg-gray-50' ?>">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                    Dashboard
                </a>
                <a href="<?= APP_URL ?>/properties/" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium <?= $currentPage === 'properties' ? 'active' : 'text-gray-600 hover:bg-gray-50' ?>">
                    <i class="fas fa-building w-5 mr-3"></i>
                    Properties
                </a>
                <a href="<?= APP_URL ?>/units/" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium <?= $currentPage === 'units' ? 'active' : 'text-gray-600 hover:bg-gray-50' ?>">
                    <i class="fas fa-home w-5 mr-3"></i>
                    Units
                </a>
                <a href="<?= APP_URL ?>/tenants/" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium <?= $currentPage === 'tenants' ? 'active' : 'text-gray-600 hover:bg-gray-50' ?>">
                    <i class="fas fa-users w-5 mr-3"></i>
                    Tenants
                </a>
                <a href="<?= APP_URL ?>/leases/" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium <?= $currentPage === 'leases' ? 'active' : 'text-gray-600 hover:bg-gray-50' ?>">
                    <i class="fas fa-file-contract w-5 mr-3"></i>
                    Leases
                </a>
                <a href="<?= APP_URL ?>/payments/" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium <?= $currentPage === 'payments' ? 'active' : 'text-gray-600 hover:bg-gray-50' ?>">
                    <i class="fas fa-dollar-sign w-5 mr-3"></i>
                    Payments
                </a>
                <a href="<?= APP_URL ?>/maintenance/" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium <?= $currentPage === 'maintenance' ? 'active' : 'text-gray-600 hover:bg-gray-50' ?>">
                    <i class="fas fa-tools w-5 mr-3"></i>
                    Maintenance
                </a>
                <a href="<?= APP_URL ?>/reports/" class="sidebar-link flex items-center px-4 py-3 text-sm font-medium <?= $currentPage === 'reports' ? 'active' : 'text-gray-600 hover:bg-gray-50' ?>">
                    <i class="fas fa-chart-bar w-5 mr-3"></i>
                    Reports
                </a>
            </nav>
            
            <!-- User info -->
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium">
                        <?= getUserInitials($_SESSION['user_name'] ?? 'U') ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></p>
                        <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
                    </div>
                    <a href="<?= APP_URL ?>/logout.php" class="text-gray-400 hover:text-gray-600" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </aside>
    
    <!-- Main content wrapper -->
    <div class="lg:pl-64">
        <!-- Top bar -->
        <header class="sticky top-0 z-10 flex h-16 flex-shrink-0 bg-white border-b border-gray-200">
            <button type="button" class="px-4 text-gray-500 focus:outline-none lg:hidden" onclick="toggleSidebar()">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <div class="flex flex-1 justify-between px-4">
                <div class="flex flex-1"></div>
                <div class="ml-4 flex items-center space-x-4">
                    <button class="text-gray-400 hover:text-gray-600 relative">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-red-500 text-white text-xs flex items-center justify-center">3</span>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-cog text-xl"></i>
                    </button>
                </div>
            </div>
        </header>
        
        <!-- Page content -->
        <main class="flex-1">
            <div class="py-6">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
