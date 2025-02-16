# Technical Context

## Technology Stack

### Frontend Technologies
1. **Core Web Technologies**
   - HTML5
   - CSS3
   - JavaScript (ES6+)

2. **CSS Frameworks & UI**
   - Bootstrap 5.3.0
   - Bootstrap Icons 1.11.3
   - Custom CSS Variables for theming

3. **JavaScript Libraries**
   - FullCalendar 5.11.3 (Calendar functionality)
   - Chart.js (Data visualization)
   - SweetAlert2 (Modal dialogs and notifications)

### Data Management
1. **Local Storage**
   ```javascript
   const STORAGE_KEYS = {
     PAYMENTS: 'payments',
     INCOMES: 'incomes',
     SAVINGS: 'savings',
     EXCHANGE_RATES: 'exchangeRates',
     LAST_UPDATE: 'lastExchangeUpdate',
     BUDGET_GOALS: 'budgetGoals',
     THEME: 'theme'
   }
   ```

2. **External APIs**
   - Exchange Rate API (api.exchangerate.host)
   - Supabase Client (Future implementation)

## Development Setup

### Environment Requirements
- Modern web browser with IndexedDB support
- Local web server for development
- Internet connection for initial load and API calls

### Development Tools
- Visual Studio Code
- Git for version control
- Python's SimpleHTTPServer for local testing
- Browser DevTools for debugging

## Technical Constraints

### Browser Support
- Modern browsers with ES6+ support
- IndexedDB availability
- Service Workers support for PWA
- Local Storage support

### API Limitations
- Exchange Rate API:
  - Rate limits
  - Request quotas
  - Response caching requirements

### Performance Requirements
- Initial load < 3s
- Offline functionality
- Smooth animations (60fps)
- Responsive interface

## Dependencies

### CDN Resources
```html
<!-- CSS Dependencies -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

<!-- JavaScript Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/tr.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
```

## Data Models

### Income Model
```typescript
interface Income {
  name: string;
  amount: number;
  currency: string;
  firstIncomeDate: string;
  frequency: string;
}
```

### Payment Model
```typescript
interface Payment {
  name: string;
  amount: number;
  currency: string;
  category: string;
  firstPaymentDate: string;
  frequency: string;
}
```

### Saving Model
```typescript
interface Saving {
  name: string;
  targetAmount: number;
  currentAmount: number;
  currency: string;
  startDate: string;
  targetDate: string;
}
```

### Budget Goals Model
```typescript
interface BudgetGoals {
  monthlyExpenseLimit: number;
  categories: {
    name: string;
    limit: number;
  }[];
}
```

## Implementation Notes

### LocalStorage Usage
- Theme preferences
- Exchange rates cache
- Last update timestamps
- Budget goals configuration

### IndexedDB Usage
- Transaction records
- Income records
- Payment records
- Savings records

### PWA Features
- Offline functionality
- App installation
- Background sync
- Push notifications (future)
