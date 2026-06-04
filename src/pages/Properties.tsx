import React, { useState, useEffect } from 'react';
import { Plus, Search, Filter, MapPin, Home, Building, Building2, MoreVertical, Eye, Edit, Trash2 } from 'lucide-react';
import { getProperties } from '../utils/api';
import { Property } from '../types';

export const Properties = () => {
  const [properties, setProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterType, setFilterType] = useState<string>('all');
  const [filterStatus, setFilterStatus] = useState<string>('all');
  const [selectedProperty, setSelectedProperty] = useState<Property | null>(null);
  const [showModal, setShowModal] = useState(false);

  useEffect(() => {
    const fetchProperties = async () => {
      const response = await getProperties();
      if (response.error) {
        setError(response.error);
      } else if (response.data) {
        // Map DB fields to interface
        const mappedProperties = response.data.map((prop: any) => ({
          id: prop.id.toString(),
          name: prop.name,
          address: prop.address,
          city: prop.city,
          state: prop.state,
          zip: prop.zip,
          type: prop.type,
          units: prop.total_units,
          occupiedUnits: prop.occupied_units,
          status: prop.status,
        }));
        setProperties(mappedProperties);
      }
      setLoading(false);
    };
    fetchProperties();
  }, []);

  const filteredProperties = properties.filter((property) => {
    const matchesSearch =
      property.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      property.address.toLowerCase().includes(searchTerm.toLowerCase()) ||
      property.city.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesType = filterType === 'all' || property.type === filterType;
    const matchesStatus = filterStatus === 'all' || property.status === filterStatus;
    return matchesSearch && matchesType && matchesStatus;
  });

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'apartment':
        return <Building className="h-5 w-5" />;
      case 'house':
        return <Home className="h-5 w-5" />;
      case 'commercial':
        return <Building2 className="h-5 w-5" />;
      case 'condo':
        return <Building className="h-5 w-5" />;
      default:
        return <Building className="h-5 w-5" />;
    }
  };

  const getOccupancyColor = (occupied: number, total: number) => {
    const rate = (occupied / total) * 100;
    if (rate >= 90) return 'bg-green-500';
    if (rate >= 70) return 'bg-yellow-500';
    return 'bg-red-500';
  };

  return (
    <div className="space-y-6">
      {loading && <div className="text-center py-8">Loading properties...</div>}
      {error && <div className="text-center py-8 text-red-600">Error: {error}</div>}
      {!loading && !error && (
        <div>
          {/* Header */}
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Properties</h1>
              <p className="mt-1 text-sm text-gray-500">
                Manage your real estate portfolio
              </p>
            </div>
            <button
              onClick={() => setShowModal(true)}
              className="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
              <Plus className="h-5 w-5 mr-2" />
              Add Property
            </button>
          </div>

          {/* Filters */}
          <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div className="flex flex-col sm:flex-row gap-4">
              <div className="flex-1 relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input
                  type="text"
                  placeholder="Search properties..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                />
              </div>
              <div className="flex gap-4">
                <div className="relative">
                  <Filter className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                  <select
                    value={filterType}
                    onChange={(e) => setFilterType(e.target.value)}
                    className="pl-10 pr-8 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none appearance-none bg-white"
                  >
                    <option value="all">All Types</option>
                    <option value="apartment">Apartment</option>
                    <option value="house">House</option>
                    <option value="condo">Condo</option>
                    <option value="commercial">Commercial</option>
                  </select>
                </div>
                <select
                  value={filterStatus}
                  onChange={(e) => setFilterStatus(e.target.value)}
                  className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none appearance-none bg-white"
                >
                  <option value="all">All Status</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>

          {/* Properties Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {filteredProperties.map((property) => (
              <div
                key={property.id}
                className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition"
              >
                <div className="relative h-48">
                  <img
                    src={property.image}
                    alt={property.name}
                    className="w-full h-full object-cover"
                  />
                  <div className="absolute top-3 left-3">
                    <span
                      className={`px-3 py-1 text-xs font-medium rounded-full ${
                        property.status === 'active'
                          ? 'bg-green-100 text-green-700'
                          : 'bg-red-100 text-red-700'
                      }`}
                    >
                      {property.status}
                    </span>
                  </div>
                  <div className="absolute top-3 right-3">
                    <div className="relative group">
                      <button className="p-2 bg-white rounded-lg shadow hover:bg-gray-50">
                        <MoreVertical className="h-4 w-4 text-gray-600" />
                      </button>
                      <div className="absolute right-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-100 hidden group-hover:block z-10">
                        <button
                          onClick={() => {
                            setSelectedProperty(property);
                            setShowModal(true);
                          }}
                          className="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center"
                        >
                          <Eye className="h-4 w-4 mr-2" /> View
                        </button>
                        <button className="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center">
                          <Edit className="h-4 w-4 mr-2" /> Edit
                        </button>
                        <button className="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 text-red-600 flex items-center">
                          <Trash2 className="h-4 w-4 mr-2" /> Delete
                        </button>
                      </div>
                    </div>
                  </div>
                  <div className="absolute bottom-3 left-3">
                    <div className="flex items-center space-x-1 text-white bg-black/50 px-2 py-1 rounded">
                      {getTypeIcon(property.type)}
                      <span className="text-sm capitalize">{property.type}</span>
                    </div>
                  </div>
                </div>
                <div className="p-4">
                  <h3 className="text-lg font-semibold text-gray-900">{property.name}</h3>
                  <div className="flex items-center text-sm text-gray-500 mt-1">
                    <MapPin className="h-4 w-4 mr-1" />
                    {property.address}, {property.city}, {property.state} {property.zip}
                  </div>
                  <div className="mt-4 flex items-center justify-between">
                    <div>
                      <p className="text-sm text-gray-500">Units</p>
                      <p className="text-lg font-semibold text-gray-900">
                        {property.occupiedUnits}/{property.units}
                      </p>
                    </div>
                    <div>
                      <p className="text-sm text-gray-500">Occupancy</p>
                      <div className="flex items-center space-x-2">
                        <div className="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                          <div
                            className={`h-full ${getOccupancyColor(
                              property.occupiedUnits,
                              property.units
                            )}`}
                            style={{
                              width: `${(property.occupiedUnits / property.units) * 100}%`,
                            }}
                          />
                        </div>
                        <span className="text-sm font-medium">
                          {Math.round((property.occupiedUnits / property.units) * 100)}%
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Empty State */}
          {filteredProperties.length === 0 && (
            <div className="text-center py-12">
              <Building2 className="h-12 w-12 mx-auto text-gray-400" />
              <h3 className="mt-4 text-lg font-medium text-gray-900">No properties found</h3>
              <p className="mt-1 text-sm text-gray-500">
                Try adjusting your search or filter to find what you're looking for.
              </p>
            </div>
          )}

          {/* Modal */}
          {showModal && (
            <div className="fixed inset-0 z-50 overflow-y-auto">
              <div className="flex items-center justify-center min-h-screen px-4">
                <div
                  className="fixed inset-0 bg-black bg-opacity-50"
                  onClick={() => {
                    setShowModal(false);
                    setSelectedProperty(null);
                  }}
                />
                <div className="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                  <h2 className="text-xl font-bold text-gray-900 mb-4">
                    {selectedProperty ? 'Property Details' : 'Add New Property'}
                  </h2>
                  {selectedProperty ? (
                    <div className="space-y-4">
                      <img
                        src={selectedProperty.image}
                        alt={selectedProperty.name}
                        className="w-full h-48 object-cover rounded-lg"
                      />
                      <div className="grid grid-cols-2 gap-4">
                        <div>
                          <p className="text-sm text-gray-500">Name</p>
                          <p className="font-medium">{selectedProperty.name}</p>
                        </div>
                        <div>
                          <p className="text-sm text-gray-500">Type</p>
                          <p className="font-medium capitalize">{selectedProperty.type}</p>
                        </div>
                        <div>
                          <p className="text-sm text-gray-500">Address</p>
                          <p className="font-medium">{selectedProperty.address}</p>
                        </div>
                        <div>
                          <p className="text-sm text-gray-500">City</p>
                          <p className="font-medium">{selectedProperty.city}</p>
                        </div>
                        <div>
                          <p className="text-sm text-gray-500">Total Units</p>
                          <p className="font-medium">{selectedProperty.units}</p>
                        </div>
                        <div>
                          <p className="text-sm text-gray-500">Occupied</p>
                          <p className="font-medium">{selectedProperty.occupiedUnits}</p>
                        </div>
                      </div>
                    </div>
                  ) : (
                    <form className="space-y-4">
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                          Property Name
                        </label>
                        <input
                          type="text"
                          className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                          placeholder="Enter property name"
                        />
                      </div>
                      <div className="grid grid-cols-2 gap-4">
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-1">
                            Type
                          </label>
                          <select className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <option>Apartment</option>
                            <option>House</option>
                            <option>Condo</option>
                            <option>Commercial</option>
                          </select>
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-1">
                            Total Units
                          </label>
                          <input
                            type="number"
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                            placeholder="0"
                          />
                        </div>
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                          Address
                        </label>
                        <input
                          type="text"
                          className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                          placeholder="Enter street address"
                        />
                      </div>
                      <div className="grid grid-cols-3 gap-4">
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-1">
                            City
                          </label>
                          <input
                            type="text"
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                            placeholder="City"
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-1">
                            State
                          </label>
                          <input
                            type="text"
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                            placeholder="State"
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium text-gray-700 mb-1">
                            ZIP
                          </label>
                          <input
                            type="text"
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                            placeholder="ZIP"
                          />
                        </div>
                      </div>
                    </form>
                  )}
                  <div className="mt-6 flex justify-end space-x-3">
                    <button
                      onClick={() => {
                        setShowModal(false);
                        setSelectedProperty(null);
                      }}
                      className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                    >
                      Close
                    </button>
                    {!selectedProperty && (
                      <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Add Property
                      </button>
                    )}
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
};
