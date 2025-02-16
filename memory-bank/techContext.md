# Technical Context

## Technology Stack

### Backend Technologies
1.  **PHP**
    -   Server-side scripting language
2.  **MySQL**
    -   Relational database management system

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

### External APIs
1.  **Exchange Rate API**
    -   Real-time currency conversion

## Development Setup

### Environment Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server
- Composer for dependency management

### Development Tools
- Visual Studio Code
- Git for version control
- MySQL Workbench or similar database management tool
- Browser DevTools for debugging

## Technical Constraints

### Server Requirements
- PHP version compatibility
- MySQL database configuration
- Web server configuration

### API Limitations
- Exchange Rate API:
  - Rate limits
  - Request quotas
  - Response caching requirements

### Performance Requirements
- Fast loading times
- Smooth animations (60fps)
- Responsive interface

## Dependencies

### PHP Packages (Composer)
```json
{
    "require": {
        "ext-pdo": "*",
        "guzzlehttp/guzzle": "^7.0"
    }
}
```

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
```

## Data Models

### Income Model
```php
class Income {
    public $income_id;
    public $user_id;
    public $name;
    public $amount;
    public $currency;
    public $first_income_date;
    public $frequency;
}
```

### Payment Model
```php
class Payment {
    public $payment_id;
    public $user_id;
    public $name;
    public $amount;
    public $currency;
    public $category;
    public $first_payment_date;
    public $frequency;
}
```

### Saving Model
```php
class Saving {
    public $saving_id;
    public $user_id;
    public $name;
    public $target_amount;
    public $current_amount;
    public $currency;
    public $start_date;
    public $target_date;
}
```

### Budget Goals Model
```php
class BudgetGoals {
    public $goal_id;
    public $user_id;
    public $monthly_expense_limit;
    public $category_name;
    public $category_limit;
}
```

## Implementation Notes

### Database Interaction
- PDO for database connections
- Prepared statements for security
- Transactions for data integrity

### Session Management
- PHP sessions for user authentication
- Secure session handling
- Session timeout management

### API Integration
- Guzzle HTTP client for API requests
- Error handling for API responses
- Caching for API data
