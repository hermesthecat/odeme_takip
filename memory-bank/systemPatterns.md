# System Patterns and Conventions

## Code Organization
```
/
├── api/          # API endpoints
├── classes/      # Core classes
├── js/          # Frontend JavaScript
├── lang/        # Language files
└── modals/      # Modal components
```

## Identified Patterns

### 1. API Structure
- RESTful endpoints in api/ directory
- Dedicated files per resource type
- Common utilities separated (utils.php, validate.php)

### 2. Frontend Organization
- Module-based JS files
- Separate files for different features:
  - auth.js: Authentication handling
  - borsa.js: Stock market functionality
  - theme.js: Theme management
  - utils.js: Common utilities

### 3. Multi-language Support
- Dedicated language files in lang/
- Supporting 20+ languages
- Language selection mechanism

### 4. Modal Components
- Separate directory for modal definitions
- Feature-specific modals:
  - income_modal.php
  - payment_modal.php
  - savings_modal.php
  - user_settings_modal.php

### 5. Data Management
- Recurring transaction pattern:
  - Parent-child relationship
  - Frequency-based scheduling
  - Status tracking

- Multi-currency support pattern:
  - Base currency per user
  - Exchange rate tracking
  - Currency conversion handling

### 6. Authentication
- Session-based authentication
- Remember-me functionality
- Role-based access control

## Naming Conventions
1. Files:
   - Snake case for PHP files
   - Camel case for JS files
   - All lowercase for language files

2. Database:
   - Snake case for table names
   - Snake case for column names
   - Explicit foreign key naming

## Security Patterns
1. Input Validation:
   - Centralized validation (validate.php)
   - XSS prevention (xss.php)
   - Authentication checks (auth.php)

2. Database:
   - Prepared statements
   - Foreign key constraints
   - Proper indexing

## UI Patterns
1. Theme Support:
   - Light/dark mode
   - User preference storage
   - Dynamic theme switching

2. Modal Usage:
   - Consistent modal structure
   - Feature-specific implementations
   - Reusable components

## Future Pattern Considerations
1. Caching Strategy
2. Error Handling
3. Logging Standards
4. API Response Format
5. Performance Optimization