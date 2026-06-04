import React, { useState } from 'react';
import { Download, FileText, TrendingUp, DollarSign, Users, Building2, Calendar } from 'lucide-react';
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  PieChart as RechartsPie,
  Pie,
  Cell,
  Legend,
  AreaChart,
  Area,
} from 'recharts';
import { properties, payments, tenants, leases } from '../data/mockData';

const monthlyRevenue = [
  { month: 'Jan', revenue: 45000, expenses: 12000 },
  { month: 'Feb', revenue: 48000, expenses: 11500 },
  { month: 'Mar', revenue: 52000, expenses: 13000 },
  { month: 'Apr', revenue: 49000, expenses: 12800 },
  { month: 'May', revenue: 55000, expenses: 14000 },
  { month: 'Jun', revenue: 58000, expenses: 14500 },
];

const occupancyByProperty = [
  { name: 'Sunrise Apartments', occupied: 21, vacant: 3 },
  { name: 'Oak Hill Residences', occupied: 10, vacant: 2 },
  { name: 'Downtown Commercial', occupied: 6, vacant: 2 },
  { name: 'Palm Gardens', occupied: 32, vacant: 4 },
  { name: 'Mountain View Houses', occupied: 5, vacant: 1 },
];

const paymentDistribution = [
  { name: 'On-Time', value: 85, color: '#10B981' },
  { name: 'Late', value: 10, color: '#F59E0B' },
  { name: 'Outstanding', value: 5, color: '#EF4444' },
];

const leaseStatus = [
  { name: 'Active', value: 5, color: '#10B981' },
  { name: 'Expiring Soon', value: 2, color: '#F59E0B' },
  { name: 'Expired', value: 1, color: '#6B7280' },
];

export const Reports: React.FC = () => {
  const [dateRange, setDateRange] = useState('last6months');

  const totalRevenue = payments.filter(p => p.status === 'paid').reduce((sum, p) => sum + p.amount, 0);
  const activeLeases = leases.filter(l => l.status === 'active').length;
  const totalProperties = properties.length;
  const totalTenants = tenants.length;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Reports</h1>
          <p className="mt-1 text-sm text-gray-500">
            Analytics and insights for your portfolio
          </p>
        </div>
        <div className="flex items-center space-x-3 mt-4 sm:mt-0">
          <select
            value={dateRange}
            onChange={(e) => setDateRange(e.target.value)}
            className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none appearance-none bg-white"
          >
            <option value="last30days">Last 30 Days</option>
            <option value="last3months">Last 3 Months</option>
            <option value="last6months">Last 6 Months</option>
            <option value="lastyear">Last Year</option>
          </select>
          <button className="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <Download className="h-5 w-5 mr-2" />
            Export
          </button>
        </div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Total Revenue</p>
              <p className="text-2xl font-bold text-green-600">
                ${totalRevenue.toLocaleString()}
              </p>
              <p className="text-xs text-green-600 mt-1">+12% from last period</p>
            </div>
            <div className="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
              <DollarSign className="h-6 w-6 text-green-600" />
            </div>
          </div>
        </div>
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Properties</p>
              <p className="text-2xl font-bold text-blue-600">{totalProperties}</p>
              <p className="text-xs text-blue-600 mt-1">{activeLeases} active leases</p>
            </div>
            <div className="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
              <Building2 className="h-6 w-6 text-blue-600" />
            </div>
          </div>
        </div>
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Tenants</p>
              <p className="text-2xl font-bold text-purple-600">{totalTenants}</p>
              <p className="text-xs text-purple-600 mt-1">+3 new this month</p>
            </div>
            <div className="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
              <Users className="h-6 w-6 text-purple-600" />
            </div>
          </div>
        </div>
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-500">Occupancy Rate</p>
              <p className="text-2xl font-bold text-yellow-600">87%</p>
              <p className="text-xs text-yellow-600 mt-1">+2% from last month</p>
            </div>
            <div className="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
              <TrendingUp className="h-6 w-6 text-yellow-600" />
            </div>
          </div>
        </div>
      </div>

      {/* Charts Row 1 */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Revenue vs Expenses */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Revenue vs Expenses</h2>
            <select className="text-sm border border-gray-300 rounded-lg px-3 py-1">
              <option>Monthly</option>
              <option>Quarterly</option>
              <option>Yearly</option>
            </select>
          </div>
          <ResponsiveContainer width="100%" height={280}>
            <BarChart data={monthlyRevenue}>
              <CartesianGrid strokeDasharray="3 3" stroke="#E5E7EB" />
              <XAxis dataKey="month" stroke="#6B7280" />
              <YAxis stroke="#6B7280" />
              <Tooltip
                contentStyle={{
                  backgroundColor: '#fff',
                  border: '1px solid #E5E7EB',
                  borderRadius: '8px',
                }}
              />
              <Legend />
              <Bar dataKey="revenue" fill="#3B82F6" radius={[4, 4, 0, 0]} />
              <Bar dataKey="expenses" fill="#EF4444" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* Revenue Trend */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Revenue Trend</h2>
          </div>
          <ResponsiveContainer width="100%" height={280}>
            <AreaChart data={monthlyRevenue}>
              <CartesianGrid strokeDasharray="3 3" stroke="#E5E7EB" />
              <XAxis dataKey="month" stroke="#6B7280" />
              <YAxis stroke="#6B7280" />
              <Tooltip
                contentStyle={{
                  backgroundColor: '#fff',
                  border: '1px solid #E5E7EB',
                  borderRadius: '8px',
                }}
              />
              <Area
                type="monotone"
                dataKey="revenue"
                stroke="#3B82F6"
                fill="#93C5FD"
                fillOpacity={0.5}
              />
            </AreaChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Charts Row 2 */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Occupancy by Property */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Occupancy by Property</h2>
          </div>
          <ResponsiveContainer width="100%" height={250}>
            <BarChart data={occupancyByProperty} layout="vertical">
              <CartesianGrid strokeDasharray="3 3" stroke="#E5E7EB" />
              <XAxis type="number" stroke="#6B7280" />
              <YAxis dataKey="name" type="category" stroke="#6B7280" width={100} />
              <Tooltip
                contentStyle={{
                  backgroundColor: '#fff',
                  border: '1px solid #E5E7EB',
                  borderRadius: '8px',
                }}
              />
              <Legend />
              <Bar dataKey="occupied" fill="#10B981" radius={[0, 4, 4, 0]} />
              <Bar dataKey="vacant" fill="#E5E7EB" radius={[0, 4, 4, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* Payment Distribution */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Payment Status</h2>
          <ResponsiveContainer width="100%" height={200}>
            <RechartsPie>
              <Pie
                data={paymentDistribution}
                cx="50%"
                cy="50%"
                innerRadius={50}
                outerRadius={80}
                paddingAngle={5}
                dataKey="value"
              >
                {paymentDistribution.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip />
            </RechartsPie>
          </ResponsiveContainer>
          <div className="flex justify-center space-x-4 mt-4">
            {paymentDistribution.map((item) => (
              <div key={item.name} className="flex items-center">
                <div
                  className="w-3 h-3 rounded-full mr-2"
                  style={{ backgroundColor: item.color }}
                />
                <span className="text-sm text-gray-600">{item.name}</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Lease Status */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Lease Status Overview</h2>
          <ResponsiveContainer width="100%" height={200}>
            <RechartsPie>
              <Pie
                data={leaseStatus}
                cx="50%"
                cy="50%"
                innerRadius={60}
                outerRadius={90}
                paddingAngle={5}
                dataKey="value"
              >
                {leaseStatus.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip />
              <Legend />
            </RechartsPie>
          </ResponsiveContainer>
        </div>

        {/* Quick Reports */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Quick Reports</h2>
          <div className="space-y-3">
            {[
              { name: 'Monthly Income Statement', icon: DollarSign, color: 'bg-green-100 text-green-600' },
              { name: 'Occupancy Report', icon: Building2, color: 'bg-blue-100 text-blue-600' },
              { name: 'Tenant Ledger', icon: Users, color: 'bg-purple-100 text-purple-600' },
              { name: 'Maintenance Summary', icon: FileText, color: 'bg-yellow-100 text-yellow-600' },
              { name: 'Lease Expiration Report', icon: Calendar, color: 'bg-red-100 text-red-600' },
            ].map((report) => (
              <button
                key={report.name}
                className="w-full flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
              >
                <div className="flex items-center">
                  <div className={`p-2 rounded-lg ${report.color}`}>
                    <report.icon className="h-5 w-5" />
                  </div>
                  <span className="ml-3 text-sm font-medium text-gray-700">{report.name}</span>
                </div>
                <Download className="h-4 w-4 text-gray-400" />
              </button>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
