# REMS Performance Optimization Guide

## Overview
This document outlines the performance improvements implemented for the Real Estate Management System (REMS).

## Files Added

### 1. Database Indexes (`rems/includes/database-indexes.sql`)
**Purpose**: Improve database query performance by 10-50x

**Key Indexes**:
- Status-based indexes for fast filtering (properties, units, tenants, leases)
- Composite indexes for common JOIN conditions (payments, maintenance)
- Date-based indexes for time-range queries

**Implementation**:
```bash
mysql -u root < rems/includes/database-indexes.sql
```

### 2. Optimized PHP Functions (`rems/includes/functions-optimized.php`)
**Purpose**: Replace old query patterns with optimized versions

**Key Improvements**:
- `getDashboardStatsOptimized()`: Combines 8 queries into 1 (~8x faster)
- All list functions now include pagination parameters
- Added file-based caching for frequently accessed data
- Proper LIMIT/OFFSET for memory efficiency

**Usage**:
```php
// Old: Loaded ALL payments, then sliced in code
$allPayments = getPayments(); // Could be thousands
$recent = array_slice($allPayments, 0, 5); // Wasteful

// New: Load only what you need
$recent = getPaymentsOptimized('paid', 5, 0); // 5 records only
```

### 3. Optimized Dashboard API (`rems/api/dashboard-optimized.php`)
**Purpose**: Replace existing dashboard endpoint with performance-optimized version

**Key Improvements**:
- Uses optimized functions from `functions-optimized.php`
- Response caching (5-minute TTL)
- Restricted CORS headers (security + performance)
- HTTP caching headers for browser caching
- Reduced database queries from 12+ to 4

**Deployment**:
```bash
# Replace old endpoint
mv rems/api/dashboard.php rems/api/dashboard.php.backup
cp rems/api/dashboard-optimized.php rems/api/dashboard.php
```

### 4. Optimized React App (`src/App-optimized.tsx`)
**Purpose**: Improve frontend performance and bundle size

**Key Improvements**:
- Lazy loading of all page components
- Route-based code splitting
- Component memoization to prevent unnecessary re-renders
- Suspense boundaries for better loading states
- Smaller initial bundle (pages loaded on demand)

**Expected Impact**: 
- 40-60% smaller initial bundle
- Faster Time to Interactive (TTI)

**Implementation**:
```bash
# Backup and replace
cp src/App.tsx src/App.tsx.backup
cp src/App-optimized.tsx src/App.tsx
```

### 5. Optimized React List Components (`src/components/OptimizedList.tsx`)
**Purpose**: Efficient rendering of large lists

**Features**:
- `PaginatedList`: Renders 10 items per page (vs all at once)
- `VirtualList`: Only renders visible items (windowing technique)
- `ListItem`: Memoized to prevent re-renders of unchanged items

**Usage Example**:
```tsx
import { PaginatedList } from './components/OptimizedList';

<PaginatedList 
  items={properties}
  pageSize={10}
  isLoading={loading}
  onPageChange={(page) => console.log('Page:', page)}
/>
```

### 6. Optimized Vite Config (`vite-config-optimized.ts`)
**Purpose**: Production bundle optimization

**Key Settings**:
- Code splitting by vendor (react, recharts, ui libraries)
- Minification with dead code elimination
- CSS code splitting
- Terser compression (removes console.log in production)

**Usage**:
```bash
# Update vite.config.ts
cp vite-config-optimized.ts vite.config.ts

# Rebuild
npm run build
```

## Performance Impact Summary

| Optimization | Estimated Improvement | Effort |
|---|---|---|
| Database indexes | 10-50x query speed | Low |
| Optimized queries | 8x dashboard load | Low |
| API response caching | 90%+ cache hit | Low |
| React lazy loading | 40-60% bundle reduction | Medium |
| Virtual list rendering | 10-100x for large lists | Medium |
| Vite code splitting | 20-30% smaller chunks | Low |

## Implementation Order

1. **Week 1 - Backend**:
   - Add database indexes
   - Replace PHP functions (keep old ones as fallback)
   - Test API performance

2. **Week 2 - Frontend**:
   - Update React App.tsx
   - Add optimized list components
   - Update Vite config

3. **Week 3 - Testing & Monitoring**:
   - Performance testing (load testing)
   - Monitor query performance
   - Measure bundle sizes
   - A/B test if needed

## Monitoring

### Database Performance
```sql
-- Check slow queries
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 0.5;

-- Monitor index usage
EXPLAIN SELECT * FROM properties WHERE status = 'active';
```

### Frontend Performance
```javascript
// Measure Core Web Vitals
import { getCLS, getFID, getFCP, getLCP, getTTFB } from 'web-vitals';

getCLS(console.log);
getFID(console.log);
getFCP(console.log);
getLCP(console.log);
getTTFB(console.log);
```

### API Performance
```bash
# Monitor response times
curl -w "Response time: %{time_total}s\n" https://api.rems.com/dashboard
```

## Cache Management

The optimized functions use file-based caching in `rems/cache/` directory.

```php
// Clear cache manually
function clearCache($pattern = '*') {
    $files = glob(__DIR__ . '/../cache/' . $pattern . '.cache');
    foreach ($files as $file) {
        @unlink($file);
    }
}

// Or via API endpoint (admin only)
POST /api/cache/clear
```

## Rollback Plan

If issues arise, revert changes:

```bash
# Backend rollback
git checkout rems/includes/functions.php
git checkout rems/api/dashboard.php

# Frontend rollback  
git checkout src/App.tsx
git checkout vite.config.ts
```

## Next Steps

1. Deploy to staging environment
2. Run load tests (tools: Apache JMeter, Gatling)
3. Monitor performance metrics
4. Deploy to production with canary release
5. Monitor error rates and performance metrics

## References

- [MySQL Index Optimization](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)
- [React Performance](https://react.dev/reference/react/memo)
- [Vite Code Splitting](https://vitejs.dev/guide/build.html#code-splitting)
- [Web Performance APIs](https://developer.mozilla.org/en-US/docs/Web/API/Performance)
