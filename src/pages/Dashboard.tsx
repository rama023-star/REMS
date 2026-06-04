import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  Building2,
  Home,
  Users,
  DollarSign,
  Wrench,
  TrendingUp,
  TrendingDown,
  ArrowRight,
  AlertTriangle,
} from 'lucide-react';
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell,
} from 'recharts';
import { apiRequest } from '../utils/api';

interface DashboardData {
  stats: {
    total_properties: number;
    total_units: number;
    occupied_units: number;
    total_tenants: number;
    monthly_revenue: number;
    occupancy_rate: number;
    pending_payments: number;
    open_maintenance: number;
  };
  revenueData: Array<{ month: string; revenue: number }>;
  occupancyData: Array<{ name: string; value: number; color: string }>;
  propertiesByType: Array<{ property_type: string; count: number }>;
  recentPayments: Array<any>;
  recentMaintenance: Array<any>;
}

export const Dashboard: React.FC = () => {
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const response = await apiRequest<DashboardData>('/dashboard.php');
        if (response.error) {
          throw new Error(response.error);
        }
        if (!response.data) {
          throw new Error('No dashboard data returned');
        }
        setData(response.data);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to load dashboard data');
        console.error('Dashboard error:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (error || !data) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <AlertTriangle className="mx-auto h-12 w-12 text-red-500" />
          <h3 className="mt-2 text-sm font-medium text-gray-900">Error loading dashboard</h3>
          <p className="mt-1 text-sm text-gray-500">{error}</p>
        </div>
      </div>
    );
  }

  const stats = [
    {
      name: 'Total Properties',
      value: data.stats.total_properties,
      icon: Building2,
      change: '+2',
      changeType: 'increase' as const,
      color: 'bg-blue-500',
    },
    {
      name: 'Total Units',
      value: data.stats.total_units,
      icon: Home,
      change: '+5',
      changeType: 'increase' as const,
      color: 'bg-green-500',
    },
    {
      name: 'Active Tenants',
      value: data.stats.total_tenants,
      icon: Users,
      change: '+3',
      changeType: 'increase' as const,
      color: 'bg-purple-500',
    },
    {
      name: 'Monthly Revenue',
      value: `$${data.stats.monthly_revenue.toLocaleString()}`,
      icon: DollarSign,
      change: '+12%',
      changeType: 'increase' as const,
      color: 'bg-yellow-500',
    },
  ];

  const urgentMaintenance = data.recentMaintenance.filter(
    (m) => m.priority === 'urgent' && m.status === 'open'
  );

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
          <p className="mt-1 text-sm text-gray-500">
            Welcome back! Here's an overview of your properties.
          </p>
        </div>
        <div className="mt-4 sm:mt-0">
          <Link
            to="/properties"
            className="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            Add Property
            <ArrowRight className="ml-2 h-4 w-4" />
          </Link>
        </div>
      </div>

      {/* Alert for urgent maintenance */}
      {urgentMaintenance.length > 0 && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4 flex items-start space-x-3">
          <AlertTriangle className="h-5 w-5 text-red-500 mt-0.5" />
          <div>
            <h3 className="font-medium text-red-800">Urgent Maintenance Required</h3>
            <p className="text-sm text-red-600 mt-1">
              {urgentMaintenance.length} urgent maintenance request(s) need immediate attention.
            </p>
          </div>
          <Link
            to="/maintenance"
            className="ml-auto text-sm font-medium text-red-600 hover:text-red-700"
          >
            View All
          </Link>
        </div>
      )}

      {/* Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((stat) => (
          <div
            key={stat.name}
            className="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
          >
            <div className="flex items-center justify-between">
              <div className={`${stat.color} p-3 rounded-lg`}>
                <stat.icon className="h-6 w-6 text-white" />
              </div>
              <div
                className={`flex items-center text-sm font-medium ${
                  stat.changeType === 'increase' ? 'text-green-600' : 'text-red-600'
                }`}
              >
                {stat.changeType === 'increase' ? (
                  <TrendingUp className="h-4 w-4 mr-1" />
                ) : (
                  <TrendingDown className="h-4 w-4 mr-1" />
                )}
                {stat.change}
              </div>
            </div>
            <div className="mt-4">
              <p className="text-2xl font-bold text-gray-900">{stat.value}</p>
              <p className="text-sm text-gray-500">{stat.name}</p>
            </div>
          </div>
        ))}
      </div>

      {/* Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Revenue Chart */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Revenue Trend</h2>
          <ResponsiveContainer width="100%" height={250}>
            <LineChart data={data.revenueData}>
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
              <Line
                type="monotone"
                dataKey="revenue"
                stroke="#3B82F6"
                strokeWidth={3}
                dot={{ fill: '#3B82F6', strokeWidth: 2 }}
              />
            </LineChart>
          </ResponsiveContainer>
        </div>

        {/* Occupancy Pie Chart */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Occupancy Rate</h2>
          <div className="flex items-center justify-center">
            <ResponsiveContainer width="100%" height={250}>
              <PieChart>
                <Pie
                  data={data.occupancyData}
                  cx="50%"
                  cy="50%"
                  innerRadius={60}
                  outerRadius={100}
                  paddingAngle={5}
                  dataKey="value"
                >
                  {data.occupancyData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
            <div className="ml-4">
              <div className="text-4xl font-bold text-gray-900">{data.stats.occupancy_rate}%</div>
              <p className="text-sm text-gray-500">Occupied</p>
              <p className="text-sm text-gray-500 mt-2">
                {data.stats.occupied_units} of {data.stats.total_units} units
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Properties by Type */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Properties by Type</h2>
        <ResponsiveContainer width="100%" height={200}>
          <BarChart data={data.propertiesByType.map(item => ({ type: item.property_type, count: item.count }))}>
            <CartesianGrid strokeDasharray="3 3" stroke="#E5E7EB" />
            <XAxis dataKey="type" stroke="#6B7280" />
            <YAxis stroke="#6B7280" />
            <Tooltip
              contentStyle={{
                backgroundColor: '#fff',
                border: '1px solid #E5E7EB',
                borderRadius: '8px',
              }}
            />
            <Bar dataKey="count" fill="#3B82F6" radius={[4, 4, 0, 0]} />
          </BarChart>
        </ResponsiveContainer>
      </div>

      {/* Bottom Row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Recent Payments */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Recent Payments</h2>
            <Link
              to="/payments"
              className="text-sm text-blue-600 hover:text-blue-700 font-medium"
            >
              View All
            </Link>
          </div>
          <div className="space-y-3">
            {data.recentPayments.map((payment) => (
              <div
                key={payment.payment_id}
                className="flex items-center justify-between py-3 border-b border-gray-100 last:border-0"
              >
                <div className="flex items-center space-x-3">
                  <div className="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                    <DollarSign className="h-5 w-5 text-green-600" />
                  </div>
                  <div>
                    <p className="font-medium text-gray-900">{payment.tenant_name}</p>
                    <p className="text-sm text-gray-500">
                      {payment.property_name} - Unit {payment.unit_number}
                    </p>
                  </div>
                </div>
                <div className="text-right">
                  <p className="font-semibold text-gray-900">
                    ${parseFloat(payment.amount).toLocaleString()}
                  </p>
                  <span
                    className={`text-xs px-2 py-1 rounded-full ${
                      payment.status === 'paid'
                        ? 'bg-green-100 text-green-700'
                        : payment.status === 'pending'
                        ? 'bg-yellow-100 text-yellow-700'
                        : 'bg-gray-100 text-gray-700'
                    }`}
                  >
                    {payment.status}
                  </span>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Recent Maintenance */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Maintenance Requests</h2>
            <Link
              to="/maintenance"
              className="text-sm text-blue-600 hover:text-blue-700 font-medium"
            >
              View All
            </Link>
          </div>
          <div className="space-y-3">
            {data.recentMaintenance.map((request) => (
              <div
                key={request.request_id}
                className="flex items-center justify-between py-3 border-b border-gray-100 last:border-0"
              >
                <div className="flex items-center space-x-3">
                  <div
                    className={`h-10 w-10 rounded-full flex items-center justify-center ${
                      request.priority === 'urgent'
                        ? 'bg-red-100'
                        : request.priority === 'high'
                        ? 'bg-orange-100'
                        : 'bg-blue-100'
                    }`}
                  >
                    <Wrench
                      className={`h-5 w-5 ${
                        request.priority === 'urgent'
                          ? 'text-red-600'
                          : request.priority === 'high'
                          ? 'text-orange-600'
                          : 'text-blue-600'
                      }`}
                    />
                  </div>
                  <div>
                    <p className="font-medium text-gray-900">{request.subject}</p>
                    <p className="text-sm text-gray-500">
                      {request.property_name} - Unit {request.unit_number}
                    </p>
                  </div>
                </div>
                <div className="text-right">
                  <span
                    className={`text-xs px-2 py-1 rounded-full ${
                      request.status === 'open'
                        ? 'bg-yellow-100 text-yellow-700'
                        : request.status === 'in_progress'
                        ? 'bg-blue-100 text-blue-700'
                        : 'bg-green-100 text-green-700'
                    }`}
                  >
                    {request.status.replace('_', ' ')}
                  </span>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <Link
          to="/properties"
          className="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition"
        >
          <Building2 className="h-8 w-8 mx-auto text-blue-600" />
          <p className="mt-2 font-medium text-gray-900">Manage Properties</p>
        </Link>
        <Link
          to="/tenants"
          className="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition"
        >
          <Users className="h-8 w-8 mx-auto text-purple-600" />
          <p className="mt-2 font-medium text-gray-900">Manage Tenants</p>
        </Link>
        <Link
          to="/payments"
          className="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition"
        >
          <DollarSign className="h-8 w-8 mx-auto text-green-600" />
          <p className="mt-2 font-medium text-gray-900">Record Payment</p>
        </Link>
        <Link
          to="/reports"
          className="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition"
        >
          <TrendingUp className="h-8 w-8 mx-auto text-yellow-600" />
          <p className="mt-2 font-medium text-gray-900">View Reports</p>
        </Link>
      </div>
    </div>
  );
};
