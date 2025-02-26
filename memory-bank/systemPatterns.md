# System Patterns and Conventions

## Backend Architecture

### 1. API Structure Pattern
```php
// Central API Router (api.php)
switch ($action) {
    case 'action_name':
        try {
            if (actionHandler()) {
                $response = ['status' => 'success', ...];
            }
        } catch (Exception $e) {
            $response = ['status' => 'error', ...];
        }
        break;
}
```

### 2. Feature Module Pattern
```php
// Feature-specific modules (api/*.php)
function featureOperation() {
    global $pdo, $user_id;
    
    // 1. Validation
    // 2. Database operations
    // 3. Transaction handling
    // 4. Response formatting
}
```

### 3. Data Access Patterns
a. Database Operations:
```php
// Transaction Pattern
$pdo->beginTransaction();
try {
    // Operations
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}

// Prepared Statements Pattern
$stmt = $pdo->prepare("SQL_QUERY");
$stmt->execute([params]);
```

b. Query Patterns:
- Parent-child relationships
- Aggregate calculations
- Status tracking
- Currency conversion handling
- History tracking with timestamps

### 4. Validation Pattern
```php
// Input validation chain
$value = validateRequired($input, $fieldName);
$value = validateNumeric($value, $fieldName);
$value = validateMinValue($value, 0, $fieldName);
```

### 5. Security Patterns
a. Authentication:
```php
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}
```

b. Data Sanitization:
- Input validation
- Output escaping
- XSS prevention
- CSRF protection

### 6. Internationalization Pattern
```php
// Language Management
$lang = Language::getInstance();
$lang->setLanguage($language);

// Translation Usage
t('key.path', [params])
```

### 7. Configuration Management
```php
// Global Configuration
define('CONSTANT_NAME', 'value');

// Database Configuration
$pdo = new PDO(connection_string);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

### 8. Error Handling Pattern
```php
try {
    // Operation
} catch (Exception $e) {
    // 1. Rollback if in transaction
    // 2. Log error
    // 3. Format user-friendly response
}
```

### 9. Response Format Pattern
```php
$response = [
    'status' => 'success|error',
    'message' => 'translated_message',
    'data' => [
        'formatted_data'
    ]
];
```

### 10. History Tracking Pattern
```php
// Save history record
function saveHistory($table, $record_id, $action, $data) {
    $stmt = $pdo->prepare("INSERT INTO {$table}_history (...) VALUES (...)");
    $stmt->execute([...]);
}

// Retrieve history
function getHistory($table, $record_id) {
    $stmt = $pdo->prepare("SELECT * FROM {$table}_history WHERE record_id = ?");
    return $stmt->execute([$record_id]);
}
```

## Frontend Architecture

### 1. Module Organization
```javascript
// Feature modules
const featureModule = {
    init() {
        this.bindEvents();
        this.loadInitialData();
    },
    bindEvents() {
        // Event handlers
    },
    loadInitialData() {
        // Initial data loading
    }
};
```

### 2. API Integration
```javascript
// Standard API request
ajaxRequest({
    action: 'action_name',
    data: requestData,
    success: (response) => {
        // Handle success
    },
    error: (error) => {
        // Handle error
    }
});
```

### 3. UI Components
```javascript
// Modal management
function showModal(modalId, data) {
    // Populate and show modal
}

// Form handling
function handleForm(formId, submitCallback) {
    // Form validation and submission
}
```

## Code Organization
```
/
├── api/          # API endpoints and handlers
│   ├── auth.php
│   ├── payments.php
│   ├── income.php
│   ├── utils.php
│   └── validate.php
├── classes/      # Core classes
│   └── Language.php
├── js/          # Frontend JavaScript
├── lang/        # Language files
└── modals/      # Modal components
```

## Database Schema Patterns
1. Base Tables:
   - users
   - exchange_rates
   - logs

2. Feature Tables:
   - payments
   - income
   - savings
   - portfolio

3. Common Patterns:
   - user_id foreign key
   - currency support
   - created_at timestamp
   - parent-child relationships
   - status tracking
   - history tracking

## Security Patterns

### 1. Authentication
- Session-based authentication
- Remember-me functionality
- Login state verification

### 2. Data Protection
- Prepared statements
- Input validation
- Output sanitization
- XSS prevention
- CSRF protection

### 3. Session Management
- Secure session handling
- Session validation
- Token management

## Future Considerations

### 1. Backend Evolution
- API versioning
- Rate limiting
- Cache implementation
- Background job handling
- Enhanced history tracking

### 2. Database Optimization
- Index optimization
- Query optimization
- Connection pooling
- Sharding strategy
- History table partitioning

### 3. Security Enhancements
- API authentication
- Request signing
- Rate limiting
- IP filtering