import { Property, Unit, Tenant, Lease, Payment, MaintenanceRequest } from '../types';

export const properties: Property[] = [
  {
    id: '1',
    name: 'Sunrise Apartments',
    address: '123 Main Street',
    city: 'Los Angeles',
    state: 'CA',
    zip: '90001',
    type: 'apartment',
    units: 24,
    occupiedUnits: 21,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=400'
  },
  {
    id: '2',
    name: 'Oak Hill Residences',
    address: '456 Oak Avenue',
    city: 'San Francisco',
    state: 'CA',
    zip: '94102',
    type: 'condo',
    units: 12,
    occupiedUnits: 10,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400'
  },
  {
    id: '3',
    name: 'Downtown Commercial Center',
    address: '789 Business Blvd',
    city: 'San Diego',
    state: 'CA',
    zip: '92101',
    type: 'commercial',
    units: 8,
    occupiedUnits: 6,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400'
  },
  {
    id: '4',
    name: 'Palm Gardens',
    address: '321 Palm Drive',
    city: 'Miami',
    state: 'FL',
    zip: '33101',
    type: 'apartment',
    units: 36,
    occupiedUnits: 32,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1567496898669-ee935f5f647a?w=400'
  },
  {
    id: '5',
    name: 'Mountain View Houses',
    address: '555 Highland Road',
    city: 'Denver',
    state: 'CO',
    zip: '80201',
    type: 'house',
    units: 6,
    occupiedUnits: 5,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1564013799919-ab6000fcffc6?w=400'
  }
];

export const units: Unit[] = [
  { id: '1', propertyId: '1', propertyName: 'Sunrise Apartments', unitNumber: '101', type: 'studio', sqft: 450, rent: 1200, status: 'occupied', features: ['Parking', 'Laundry'] },
  { id: '2', propertyId: '1', propertyName: 'Sunrise Apartments', unitNumber: '102', type: '1br', sqft: 650, rent: 1500, status: 'vacant', features: ['Parking', 'Balcony', 'Laundry'] },
  { id: '3', propertyId: '1', propertyName: 'Sunrise Apartments', unitNumber: '201', type: '2br', sqft: 850, rent: 1900, status: 'occupied', features: ['Parking', 'Balcony', 'Gym'] },
  { id: '4', propertyId: '2', propertyName: 'Oak Hill Residences', unitNumber: 'A1', type: '1br', sqft: 700, rent: 2200, status: 'occupied', features: ['Pool', 'Gym', 'Concierge'] },
  { id: '5', propertyId: '2', propertyName: 'Oak Hill Residences', unitNumber: 'B2', type: '2br', sqft: 950, rent: 2800, status: 'vacant', features: ['Pool', 'Gym', 'Balcony'] },
  { id: '6', propertyId: '3', propertyName: 'Downtown Commercial Center', unitNumber: 'C1', type: 'commercial', sqft: 1500, rent: 4500, status: 'occupied', features: ['Parking', 'Security'] },
  { id: '7', propertyId: '4', propertyName: 'Palm Gardens', unitNumber: '301', type: '3br', sqft: 1200, rent: 2500, status: 'occupied', features: ['Pool', 'Parking', 'Garden'] },
  { id: '8', propertyId: '5', propertyName: 'Mountain View Houses', unitNumber: 'H1', type: '3br', sqft: 1800, rent: 3200, status: 'occupied', features: ['Garage', 'Garden', 'Fireplace'] },
];

export const tenants: Tenant[] = [
  { id: '1', name: 'John Smith', email: 'john.smith@email.com', phone: '(555) 123-4567', propertyId: '1', unitId: '1', unitNumber: '101', propertyName: 'Sunrise Apartments', leaseStart: '2024-01-15', leaseEnd: '2025-01-14', status: 'active' },
  { id: '2', name: 'Sarah Johnson', email: 'sarah.j@email.com', phone: '(555) 234-5678', propertyId: '1', unitId: '3', unitNumber: '201', propertyName: 'Sunrise Apartments', leaseStart: '2024-03-01', leaseEnd: '2025-02-28', status: 'active' },
  { id: '3', name: 'Michael Brown', email: 'm.brown@email.com', phone: '(555) 345-6789', propertyId: '2', unitId: '4', unitNumber: 'A1', propertyName: 'Oak Hill Residences', leaseStart: '2023-06-01', leaseEnd: '2024-05-31', status: 'active' },
  { id: '4', name: 'Emily Davis', email: 'emily.d@email.com', phone: '(555) 456-7890', propertyId: '3', unitId: '6', unitNumber: 'C1', propertyName: 'Downtown Commercial Center', leaseStart: '2024-02-01', leaseEnd: '2026-01-31', status: 'active' },
  { id: '5', name: 'Robert Wilson', email: 'r.wilson@email.com', phone: '(555) 567-8901', propertyId: '4', unitId: '7', unitNumber: '301', propertyName: 'Palm Gardens', leaseStart: '2023-11-01', leaseEnd: '2024-10-31', status: 'active' },
  { id: '6', name: 'Lisa Anderson', email: 'lisa.a@email.com', phone: '(555) 678-9012', propertyId: '5', unitId: '8', unitNumber: 'H1', propertyName: 'Mountain View Houses', leaseStart: '2024-04-01', leaseEnd: '2025-03-31', status: 'active' },
];

export const leases: Lease[] = [
  { id: '1', tenantId: '1', tenantName: 'John Smith', propertyId: '1', propertyName: 'Sunrise Apartments', unitId: '1', unitNumber: '101', startDate: '2024-01-15', endDate: '2025-01-14', monthlyRent: 1200, securityDeposit: 2400, status: 'active' },
  { id: '2', tenantId: '2', tenantName: 'Sarah Johnson', propertyId: '1', propertyName: 'Sunrise Apartments', unitId: '3', unitNumber: '201', startDate: '2024-03-01', endDate: '2025-02-28', monthlyRent: 1900, securityDeposit: 3800, status: 'active' },
  { id: '3', tenantId: '3', tenantName: 'Michael Brown', propertyId: '2', propertyName: 'Oak Hill Residences', unitId: '4', unitNumber: 'A1', startDate: '2023-06-01', endDate: '2024-05-31', monthlyRent: 2200, securityDeposit: 4400, status: 'expired' },
  { id: '4', tenantId: '4', tenantName: 'Emily Davis', propertyId: '3', propertyName: 'Downtown Commercial Center', unitId: '6', unitNumber: 'C1', startDate: '2024-02-01', endDate: '2026-01-31', monthlyRent: 4500, securityDeposit: 9000, status: 'active' },
  { id: '5', tenantId: '5', tenantName: 'Robert Wilson', propertyId: '4', propertyName: 'Palm Gardens', unitId: '7', unitNumber: '301', startDate: '2023-11-01', endDate: '2024-10-31', monthlyRent: 2500, securityDeposit: 5000, status: 'active' },
  { id: '6', tenantId: '6', tenantName: 'Lisa Anderson', propertyId: '5', propertyName: 'Mountain View Houses', unitId: '8', unitNumber: 'H1', startDate: '2024-04-01', endDate: '2025-03-31', monthlyRent: 3200, securityDeposit: 6400, status: 'active' },
];

export const payments: Payment[] = [
  { id: '1', tenantId: '1', tenantName: 'John Smith', propertyName: 'Sunrise Apartments', unitNumber: '101', amount: 1200, date: '2024-05-01', type: 'rent', method: 'bank_transfer', status: 'paid', reference: 'TXN001' },
  { id: '2', tenantId: '2', tenantName: 'Sarah Johnson', propertyName: 'Sunrise Apartments', unitNumber: '201', amount: 1900, date: '2024-05-01', type: 'rent', method: 'card', status: 'paid', reference: 'TXN002' },
  { id: '3', tenantId: '3', tenantName: 'Michael Brown', propertyName: 'Oak Hill Residences', unitNumber: 'A1', amount: 2200, date: '2024-05-03', type: 'rent', method: 'check', status: 'pending' },
  { id: '4', tenantId: '4', tenantName: 'Emily Davis', propertyName: 'Downtown Commercial Center', unitNumber: 'C1', amount: 4500, date: '2024-05-01', type: 'rent', method: 'bank_transfer', status: 'paid', reference: 'TXN003' },
  { id: '5', tenantId: '5', tenantName: 'Robert Wilson', propertyName: 'Palm Gardens', unitNumber: '301', amount: 2500, date: '2024-05-02', type: 'rent', method: 'card', status: 'paid', reference: 'TXN004' },
  { id: '6', tenantId: '6', tenantName: 'Lisa Anderson', propertyName: 'Mountain View Houses', unitNumber: 'H1', amount: 3200, date: '2024-05-05', type: 'rent', method: 'bank_transfer', status: 'pending' },
  { id: '7', tenantId: '1', tenantName: 'John Smith', propertyName: 'Sunrise Apartments', unitNumber: '101', amount: 100, date: '2024-05-10', type: 'fee', method: 'cash', status: 'paid' },
];

export const maintenanceRequests: MaintenanceRequest[] = [
  {
    id: '1',
    propertyId: '1',
    propertyName: 'Sunrise Apartments',
    unitId: '1',
    unitNumber: '101',
    tenantId: '1',
    tenantName: 'John Smith',
    title: 'Leaking Faucet',
    description: 'Kitchen faucet has been dripping constantly for the past 2 days.',
    priority: 'medium',
    status: 'open',
    createdAt: '2024-05-10',
    updatedAt: '2024-05-10',
    estimatedCost: 150
  },
  {
    id: '2',
    propertyId: '2',
    propertyName: 'Oak Hill Residences',
    unitId: '4',
    unitNumber: 'A1',
    tenantId: '3',
    tenantName: 'Michael Brown',
    title: 'AC Not Working',
    description: 'Air conditioning unit stopped working. Temperature is very high.',
    priority: 'high',
    status: 'in_progress',
    createdAt: '2024-05-08',
    updatedAt: '2024-05-09',
    assignedTo: 'Mike\'s HVAC Services',
    estimatedCost: 500
  },
  {
    id: '3',
    propertyId: '4',
    propertyName: 'Palm Gardens',
    unitId: '7',
    unitNumber: '301',
    tenantId: '5',
    tenantName: 'Robert Wilson',
    title: 'Broken Window Lock',
    description: 'Window lock in bedroom is broken and window won\'t close properly.',
    priority: 'low',
    status: 'completed',
    createdAt: '2024-05-01',
    updatedAt: '2024-05-05',
    actualCost: 75
  },
  {
    id: '4',
    propertyId: '5',
    propertyName: 'Mountain View Houses',
    unitId: '8',
    unitNumber: 'H1',
    tenantId: '6',
    tenantName: 'Lisa Anderson',
    title: 'Water Heater Issue',
    description: 'No hot water coming from taps. Urgent repair needed.',
    priority: 'urgent',
    status: 'open',
    createdAt: '2024-05-12',
    updatedAt: '2024-05-12',
    estimatedCost: 800
  },
  {
    id: '5',
    propertyId: '3',
    propertyName: 'Downtown Commercial Center',
    unitId: '6',
    unitNumber: 'C1',
    tenantId: '4',
    tenantName: 'Emily Davis',
    title: 'Electrical Outlet Not Working',
    description: 'One of the electrical outlets in the main office area is not functioning.',
    priority: 'medium',
    status: 'in_progress',
    createdAt: '2024-05-09',
    updatedAt: '2024-05-10',
    assignedTo: 'ElectroPro Services',
    estimatedCost: 200
  }
];
