import React, { memo, useMemo, useCallback, Suspense, lazy } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import { Layout } from './components/Layout';

// Lazy load page components for better code splitting
const Login = lazy(() => import('./pages/Login'));
const Register = lazy(() => import('./pages/Register'));
const Dashboard = lazy(() => import('./pages/Dashboard'));
const Properties = lazy(() => import('./pages/Properties'));
const Units = lazy(() => import('./pages/Units'));
const Tenants = lazy(() => import('./pages/Tenants'));
const Leases = lazy(() => import('./pages/Leases'));
const Payments = lazy(() => import('./pages/Payments'));
const Maintenance = lazy(() => import('./pages/Maintenance'));
const Reports = lazy(() => import('./pages/Reports'));

// Loading spinner component
const LoadingSpinner = memo(() => (
  <div className="min-h-screen flex items-center justify-center bg-gray-50">
    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
  </div>
));

LoadingSpinner.displayName = 'LoadingSpinner';

// Memoized protected route component
const ProtectedRoute = memo(({ children }) => {
  const { user, isLoading } = useAuth();

  if (isLoading) {
    return <LoadingSpinner />;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  return <>{children}</>;
});

ProtectedRoute.displayName = 'ProtectedRoute';

// Memoized public route component
const PublicRoute = memo(({ children }) => {
  const { user, isLoading } = useAuth();

  if (isLoading) {
    return <LoadingSpinner />;
  }

  if (user) {
    return <Navigate to="/dashboard" replace />;
  }

  return <>{children}</>;
});

PublicRoute.displayName = 'PublicRoute';

// Memoized app routes
const AppRoutes = memo(() => {
  const routes = useMemo(() => [
    {
      path: '/login',
      element: (
        <PublicRoute>
          <Suspense fallback={<LoadingSpinner />}>
            <Login />
          </Suspense>
        </PublicRoute>
      ),
    },
    {
      path: '/register',
      element: (
        <PublicRoute>
          <Suspense fallback={<LoadingSpinner />}>
            <Register />
          </Suspense>
        </PublicRoute>
      ),
    },
    {
      path: '/',
      element: (
        <ProtectedRoute>
          <Layout />
        </ProtectedRoute>
      ),
      children: [
        {
          path: '/dashboard',
          element: (
            <Suspense fallback={<LoadingSpinner />}>
              <Dashboard />
            </Suspense>
          ),
        },
        {
          path: '/properties',
          element: (
            <Suspense fallback={<LoadingSpinner />}>
              <Properties />
            </Suspense>
          ),
        },
        {
          path: '/units',
          element: (
            <Suspense fallback={<LoadingSpinner />}>
              <Units />
            </Suspense>
          ),
        },
        {
          path: '/tenants',
          element: (
            <Suspense fallback={<LoadingSpinner />}>
              <Tenants />
            </Suspense>
          ),
        },
        {
          path: '/leases',
          element: (
            <Suspense fallback={<LoadingSpinner />}>
              <Leases />
            </Suspense>
          ),
        },
        {
          path: '/payments',
          element: (
            <Suspense fallback={<LoadingSpinner />}>
              <Payments />
            </Suspense>
          ),
        },
        {
          path: '/maintenance',
          element: (
            <Suspense fallback={<LoadingSpinner />}>
              <Maintenance />
            </Suspense>
          ),
        },
        {
          path: '/reports',
          element: (
            <Suspense fallback={<LoadingSpinner />}>
              <Reports />
            </Suspense>
          ),
        },
      ],
    },
    {
      path: '*',
      element: <Navigate to="/dashboard" replace />,
    },
  ], []);

  return (
    <Routes>
      {routes.map((route, index) => (
        <Route key={index} {...route} />
      ))}
    </Routes>
  );
});

AppRoutes.displayName = 'AppRoutes';

// Main App component
const App = memo(() => {
  const basename = useMemo(() => import.meta.env.BASE_URL, []);

  return (
    <BrowserRouter basename={basename}>
      <AuthProvider>
        <AppRoutes />
      </AuthProvider>
    </BrowserRouter>
  );
});

App.displayName = 'App';

export default App;
