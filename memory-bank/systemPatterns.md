# System Patterns and Conventions

## Registration Patterns

### 1. Form Validation Pattern
```html
<form id="registerForm" novalidate>
    <input type="text"
           pattern="[a-zA-Z0-9_]{3,}"
           title="<?php echo t('requirements'); ?>"
           required>
    <div class="form-text">Requirements</div>
</form>
```

### 2. Password Requirements Pattern
```javascript
// Real-time Password Validation
passwordInput.addEventListener('input', function() {
    const password = this.value;
    document.querySelector('.length-check')
        .classList.toggle('text-success',
            password.length >= 8);
});
```

### 3. Security Pattern
```php
// CSRF Protection and Rate Limiting
<input type="hidden" name="csrf_token"
       value="<?php echo $_SESSION['csrf_token']; ?>">

if (Date.now() - lastAttempt < 2000) {
    alert(t('auth.wait_before_retry'));
    return;
}
```

### 4. Visual Feedback Pattern
```css
.password-requirements li::before {
    content: '❌';
}
.password-requirements li.text-success::before {
    content: '✅';
}
```

## Navigation Patterns

### 1. Responsive Navigation Pattern
```html
<nav class="navbar navbar-expand-lg">
    <button class="navbar-toggler" data-bs-toggle="collapse">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse">
        <!-- Navigation items -->
    </div>
</nav>
```

### 2. State-based Rendering Pattern
```php
<?php if ($is_logged_in): ?>
    <!-- Authenticated user content -->
<?php else: ?>
    <!-- Guest user content -->
<?php endif; ?>
```

### 3. Language Selection Pattern
```php
<li class="nav-item dropdown">
    <a class="dropdown-toggle">
        <?php echo $lang->getLanguageName($code); ?>
    </a>
    <ul class="dropdown-menu">
        <?php foreach ($lang->getAvailableLanguages() as $code): ?>
            <!-- Language options -->
        <?php endforeach; ?>
    </ul>
</li>
```

## Modal Management Patterns

### 1. Modal Organization Pattern
```php
// Centralized Modal Loading
require_once __DIR__ . '/modals/[feature]_modal.php';
```

### 2. Modal Event Pattern
```javascript
// Event-based Modal Management
document.getElementById('modalId')
    .addEventListener('show.bs.modal', function() {
        // Initialize modal state
    });
```

### 3. Form Validation Pattern
```javascript
// Dynamic Form Validation
element.addEventListener('change', function() {
    const input = document.querySelector('input[name="field"]');
    if (condition) {
        input.setAttribute('required', 'required');
    } else {
        input.removeAttribute('required');
    }
});
```

### 4. Date Management Pattern
```javascript
// Date Handling
function getTodayDate() {
    return new Date().toISOString().split('T')[0];
}
```

## Authentication Patterns

### 1. Security Implementation Pattern
```php
<!-- CSRF Protection -->
<input type="hidden" name="csrf_token"
       value="<?php echo $_SESSION['csrf_token']; ?>">

<!-- Rate Limiting -->
if (Date.now() - lastAttempt < 2000) {
    alert(t('auth.wait_before_retry'));
    return;
}
```

### 2. Login Form Pattern
```html
<!-- Authentication Form -->
<form id="loginForm" autocomplete="off" novalidate>
    <input type="text" name="username"
           pattern="[a-zA-Z0-9_]{3,}" required>
    <input type="password" name="password" required>
    <input type="checkbox" name="remember_me">
</form>
```

### 3. Authentication Flow Pattern
```javascript
// Authentication Process
fetch('api/auth.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.status === 'success') {
        window.location.href = 'index.php';
    }
});
```

## Logging System Patterns

### 1. Log Query Pattern
```php
// Dynamic Query Building
$where_conditions = [];
$params = [];

if (!empty($filter)) {
    $where_conditions[] = "column = :param";
    $params[':param'] = $value;
}

$where_clause = !empty($where_conditions)
    ? ' WHERE ' . implode(' AND ', $where_conditions)
    : '';
```

### 2. Log Display Pattern
```php
// Visual Log Classification
$log_type_classes = [
    'info' => 'text-info',
    'error' => 'text-danger',
    'warning' => 'text-warning'
];

// Log Row Rendering
<tr data-type="<?php echo $type; ?>">
    <td><?php echo $log['message']; ?></td>
</tr>
```

### 3. Log Filtering Pattern
```php
// Filter Form Structure
<form method="GET">
    <select name="log_type">
        <option value="">All Types</option>
        <!-- Log type options -->
    </select>
    <input type="text" name="search" />
    <input type="date" name="date_range" />
</form>
```

## Landing Page Patterns

### 1. Page Structure Pattern
```php
<!-- Modular Include Pattern -->
require_once __DIR__ . '/[component].php';

<!-- Section-based Organization -->
<section class="[section-name]">
    <div class="container">
        <!-- Section content -->
    </div>
</section>
```

### 2. Content Organization Pattern
```html
<!-- Hierarchical Content Structure -->
<section class="hero">
    <h1>Main Message</h1>
    <p class="lead">Supporting Text</p>
    <a class="btn">Call to Action</a>
</section>
```

### 3. Grid Layout Pattern
```html
<!-- Bootstrap Grid System -->
<div class="row">
    <div class="col-md-4">
        <!-- Feature card -->
    </div>
</div>
```

### 4. Component Pattern
```html
<!-- Reusable Component Structure -->
<div class="card h-100">
    <div class="card-body">
        <!-- Component content -->
    </div>
</div>
```

## Document Head Patterns

### 1. Meta Information Pattern
```html
<!-- SEO-optimized meta structure -->
<meta name="description" content="<?php echo $site_description; ?>">
<meta name="keywords" content="<?php echo $site_keywords; ?>">
<meta name="author" content="<?php echo $site_author; ?>">
<meta name="robots" content="index, follow">
```

### 2. Resource Loading Pattern
```html
<!-- External CDN Resources -->
<link href="https://cdn.jsdelivr.net/npm/[library]@[version]/[file]" rel="stylesheet">

<!-- Local Resources -->
<link rel="stylesheet" href="[style].css">
```

### 3. Document Configuration Pattern
```html
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('site_name'); ?> - <?php echo $site_slogan; ?></title>
</head>
```

## Frontend Resource Management Patterns

### 1. Script Loading Pattern
```html
<!-- External Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/[library]@[version]/dist/[file]"></script>

<!-- Application Scripts -->
<script src="js/[module].js"></script>
```

### 2. Client-Side Translation Pattern
```javascript
// Structured Translation Object
const translations = {
    module: {
        action: {
            state: '<?php echo t("key.path"); ?>'
        }
    }
};
```

### 3. Resource Organization Pattern
```html
<!-- Third-party Libraries -->
<!-- Global Configurations -->
<!-- Application Modules -->
```

## Frontend Component Patterns

### 1. Reusable Component Pattern
```php
<!-- Modular Footer Component -->
<footer class="footer">
    <!-- Content organized in grid system -->
    <div class="container">
        <div class="row">
            <!-- Responsive columns -->
        </div>
    </div>
</footer>
```

### 2. Internationalization Pattern
```php
<!-- Translation integration -->
<h5><?php echo t('footer.links'); ?></h5>
<p><?php echo t('site_description'); ?></p>
```

### 3. Layout Structure Pattern
```php
<!-- Bootstrap Grid System -->
<div class="row">
    <div class="col-md-6">
        <!-- Main content -->
    </div>
    <div class="col-md-3">
        <!-- Secondary content -->
    </div>
</div>
```

### 4. Content Organization Pattern
```php
<!-- Semantic Structure -->
<footer>
    <!-- Site information -->
    <!-- Navigation links -->
    <!-- Contact information -->
    <!-- Copyright notice -->
</footer>
```

## Database Architecture Patterns

### 1. Multi-Currency Pattern
```sql
CREATE TABLE `payments` (
    `amount` decimal(10,2) NOT NULL,
    `currency` varchar(3) DEFAULT 'TRY',
    `exchange_rate` decimal(10,4) DEFAULT NULL
);
```

### 2. Parent-Child Relationship Pattern
```sql
CREATE TABLE `payments` (
    `id` int(11) NOT NULL,
    `parent_id` int(11) DEFAULT NULL,
    CONSTRAINT `payments_ibfk_2`
    FOREIGN KEY (`parent_id`)
    REFERENCES `payments` (`id`)
    ON DELETE CASCADE
);
```

### 3. Status Tracking Pattern
```sql
CREATE TABLE `portfolio` (
    `durum` enum('aktif','satildi','kismi_satildi') DEFAULT 'aktif',
    `status` enum('pending','paid') DEFAULT 'pending'
);
```

### 4. History Tracking Pattern
```sql
CREATE TABLE `logs` (
    `log_method` text NOT NULL,
    `log_text` text NOT NULL,
    `type` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp()
);
```

## Worker Process Patterns

### 1. Worker Initialization Pattern
```php
// Command-line argument handling
if ($argc < 2) {
    die("Usage: php worker.php [data_file]\n");
}

// Data file processing
$data = json_decode(file_get_contents($data_file), true);
```

### 2. Worker Communication Pattern
```php
// Result reporting
$result = [
    'updated' => 0,
    'failed' => 0
];
file_put_contents($result_file, json_encode($result));
```

### 3. Batch Processing Pattern
```php
// Chunked processing with rate limiting
$chunks = array_chunk($items, 10);
foreach ($chunks as $chunk) {
    process($chunk);
    usleep(500000); // Rate limiting
}
```

### 4. Error Recovery Pattern
```php
try {
    $pdo->beginTransaction();
    // Process batch
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    saveLog("Error: " . $e->getMessage());
}
```

## Background Process Patterns

### 1. Multi-Threading Pattern
```php
// Process Distribution
$hisse_gruplari = array_chunk($hisseler, ceil(count($hisseler) / $thread_count));
foreach ($hisse_gruplari as $grup) {
    $temp_file = sys_get_temp_dir() . "/{$islem_id}.json";
    exec("$php_path $script_path $temp_file > /dev/null 2>&1 &");
}
```

### 2. Process Synchronization Pattern
```php
// Process Management
while ($tamamlanan < count($islemler) && time() < $timeout) {
    foreach ($islemler as $islem) {
        if (file_exists($islem['result_file'])) {
            // Process result handling
        }
    }
    usleep(500000); // Rate limiting
}
```

### 3. Batch Processing Pattern
```php
// Batch Data Processing
function topluVeritabaniGuncelle($fiyatlar, $hisse_isimleri) {
    $pdo->beginTransaction();
    try {
        foreach ($fiyatlar as $sembol => $fiyat) {
            // Batch update operations
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}
```

### 4. External API Integration Pattern
```php
// API Request Pattern
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        "content-type: application/json",
        "User-Agent: Mozilla/5.0..."
    ]
]);
```

## Configuration Patterns

### 1. Database Connection Pattern
```php
// PDO Connection with Error Handling
try {
    $pdo = new PDO($connection_string, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
```

### 2. Language Management Pattern
```php
// Singleton Pattern for Language
$lang = Language::getInstance();

// Language Selection Chain
if (isset($_GET['lang'])) {
    // URL parameter
} elseif (isset($_SESSION['lang'])) {
    // Session storage
} elseif (isset($_COOKIE['lang'])) {
    // Cookie storage
} else {
    // Browser default
}
```

### 3. Class Autoloading Pattern
```php
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
```

### 4. Global Configuration Pattern
```php
// Configuration Constants
define('CONSTANT_NAME', 'value');

// Global Settings
$supported_currencies = [
    'currency_code' => 'display_name'
];

// Global Helper Functions
function t($key, $params = []) {
    return Language::t($key, $params);
}
```

## Frontend Application Patterns

### 1. Layout Structure Pattern
```php
// Component-based organization
<div class="container">
    <!-- Navigation Component -->
    <!-- Summary Component -->
    <!-- Data Tables Component -->
    <!-- Modal Components -->
</div>
```

### 2. Internationalization Pattern
```javascript
// Translation System
const translations = {
    module: {
        feature: {
            action: '<?php echo t("key.path"); ?>'
        }
    }
};
```

### 3. Data Loading Pattern
```html
<!-- Loading State Management -->
<div id="componentLoadingSpinner" class="text-center">
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
```

### 4. Module Organization Pattern
```javascript
// Frontend Modules
- utils.js: Shared utilities
- income.js: Income management
- savings.js: Savings tracking
- payments.js: Payment handling
- summary.js: Data aggregation
- theme.js: Theme management
- app.js: Core application
- auth.js: Authentication
```

## API Architecture Patterns

### 1. Central Router Pattern
```php
// Action-based routing with modular structure
$action = $_POST['action'] ?? '';
switch ($action) {
    case 'action_name':
        if (actionHandler()) {
            $response = ['status' => 'success', ...];
        }
        break;
}
```

### 2. Response Format Pattern
```php
// Standardized JSON Response
$response = [
    'status' => 'success|error',
    'message' => sanitizeOutput($translated_message),
    'data' => sanitizeOutput($data)
];
```

### 3. Data Formatting Pattern
```php
// Consistent number formatting
foreach ($response['data']['items'] as &$item) {
    $item['amount'] = formatNumber($item['amount']);
    if (isset($item['exchange_rate'])) {
        $item['exchange_rate'] = formatNumber($item['exchange_rate']);
    }
}
```

### 4. Error Handling Pattern
```php
try {
    // Operation logic
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}
```

## Admin Interface Patterns

### 1. Security Implementation
```php
// Session Verification Pattern
checkLogin();
if ($_SESSION['is_admin'] != 1) {
    header("Location: app.php");
    exit;
}
```

### 2. Data Access Pattern
```php
// Filter Construction Pattern
$where_conditions = [];
$params = [];
if (!empty($filter_value)) {
    $where_conditions[] = "column LIKE :param";
    $params[':param'] = '%' . $filter_value . '%';
}
```

### 3. Pagination Pattern
```php
// Standard Pagination Implementation
$offset = ($current_page - 1) * $items_per_page;
$query = "SELECT * FROM table $where_clause ORDER BY id DESC LIMIT :limit OFFSET :offset";
```

### 4. Frontend Integration Pattern
```javascript
// CRUD Operations Pattern
function crudOperation(id) {
    fetch('api/endpoint.php', {
        method: 'POST',
        body: new URLSearchParams({
            action: 'operation_name',
            id: id
        })
    })
    .then(response => response.json())
    .then(data => handleResponse(data));
}
```

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