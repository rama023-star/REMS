export interface User {
  id: string;
  email: string;
  name: string;
  role: 'admin' | 'manager' | 'staff';
  avatar?: string;
}

export interface Property {
  id: string;
  name: string;
  address: string;
  city: string;
  state: string;
  zip: string;
  type: 'apartment' | 'house' | 'commercial' | 'condo';
  units: number;
  occupiedUnits: number;
  image?: string;
  status: 'active' | 'inactive';
}

export interface Unit {
  id: string;
  propertyId: string;
  propertyName: string;
  unitNumber: string;
  type: 'studio' | '1br' | '2br' | '3br' | 'commercial';
  sqft: number;
  rent: number;
  status: 'occupied' | 'vacant' | 'maintenance';
  features: string[];
}

export interface Tenant {
  id: string;
  name: string;
  email: string;
  phone: string;
  propertyId: string;
  unitId: string;
  unitNumber: string;
  propertyName: string;
  leaseStart: string;
  leaseEnd: string;
  status: 'active' | 'inactive';
  avatar?: string;
}

export interface Lease {
  id: string;
  tenantId: string;
  tenantName: string;
  propertyId: string;
  propertyName: string;
  unitId: string;
  unitNumber: string;
  startDate: string;
  endDate: string;
  monthlyRent: number;
  securityDeposit: number;
  status: 'active' | 'expired' | 'pending' | 'terminated';
  document?: string;
}

export interface Payment {
  id: string;
  tenantId: string;
  tenantName: string;
  propertyName: string;
  unitNumber: string;
  amount: number;
  date: string;
  type: 'rent' | 'deposit' | 'fee' | 'other';
  method: 'cash' | 'check' | 'card' | 'bank_transfer';
  status: 'paid' | 'pending' | 'failed' | 'refunded';
  reference?: string;
}

export interface MaintenanceRequest {
  id: string;
  propertyId: string;
  propertyName: string;
  unitId: string;
  unitNumber: string;
  tenantId: string;
  tenantName: string;
  title: string;
  description: string;
  priority: 'low' | 'medium' | 'high' | 'urgent';
  status: 'open' | 'in_progress' | 'completed' | 'cancelled';
  createdAt: string;
  updatedAt: string;
  assignedTo?: string;
  estimatedCost?: number;
  actualCost?: number;
  images?: string[];
}

export interface DashboardStats {
  totalProperties: number;
  totalUnits: number;
  occupiedUnits: number;
  totalTenants: number;
  monthlyRevenue: number;
  pendingPayments: number;
  openMaintenance: number;
  occupancyRate: number;
}
