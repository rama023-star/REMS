-- REMS Database Performance Indexes
-- Run this SQL to add indexes that significantly improve query performance

USE rems_db;

-- Property indexes
CREATE INDEX idx_properties_status ON properties(status);
CREATE INDEX idx_properties_owner ON properties(owner_id);

-- Unit indexes
CREATE INDEX idx_units_property ON units(property_id);
CREATE INDEX idx_units_status ON units(status);
CREATE INDEX idx_units_property_status ON units(property_id, status);

-- Tenant indexes
CREATE INDEX idx_tenants_property ON tenants(property_id);
CREATE INDEX idx_tenants_unit ON tenants(unit_id);
CREATE INDEX idx_tenants_property_unit ON tenants(property_id, unit_id);
CREATE INDEX idx_tenants_status ON tenants(status);

-- Lease indexes
CREATE INDEX idx_leases_tenant ON leases(tenant_id);
CREATE INDEX idx_leases_property ON leases(property_id);
CREATE INDEX idx_leases_unit ON leases(unit_id);
CREATE INDEX idx_leases_status ON leases(status);
CREATE INDEX idx_leases_dates ON leases(start_date, end_date);

-- Payment indexes
CREATE INDEX idx_payments_tenant ON payments(tenant_id);
CREATE INDEX idx_payments_property ON payments(property_id);
CREATE INDEX idx_payments_unit ON payments(unit_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_date_status ON payments(payment_date, status);

-- Maintenance indexes
CREATE INDEX idx_maintenance_property ON maintenance_requests(property_id);
CREATE INDEX idx_maintenance_unit ON maintenance_requests(unit_id);
CREATE INDEX idx_maintenance_tenant ON maintenance_requests(tenant_id);
CREATE INDEX idx_maintenance_status ON maintenance_requests(status);
CREATE INDEX idx_maintenance_priority ON maintenance_requests(priority);
CREATE INDEX idx_maintenance_priority_status ON maintenance_requests(priority, status);

-- User indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
