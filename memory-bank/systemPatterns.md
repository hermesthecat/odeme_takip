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

## Frontend Architecture

### 1. Core Architecture Patterns
- Modular JavaScript organization
- jQuery-based implementation
- Bootstrap for UI components
- SweetAlert2 for notifications
- Event-driven architecture
- Lazy loading for performance optimization

### 2. Data Management
a. Data Loading Pattern:
```javascript
- Initial page load fetches core data
- Subsequent data loaded lazily per component
- Centralized loadData() function coordinates updates
- Component-specific loading functions
```

b. State Management:
- URL-based state for month/year
- Window-level data store
- Component-level state handling

### 3. Frontend Component Structure
a. Data Display Components:
- Income list
- Savings list
- Payments list
- Recurring payments list
- Summary display

b. Interactive Components:
- Month/Year selectors
- Modal forms
- Theme switcher
- Loading spinners

### 4. Form Handling Pattern
a. Form Submission:
```javascript
- Serialization to objects
- Pre-submission validation
- AJAX-based submission
- Modal management
- Automatic data refresh
```

b. Validation System:
- Rule-based validation
- Support for:
  - Required fields
  - Numeric values
  - Date validation
  - Currency validation
  - Frequency validation
  - Min/Max values
  - Date ranges

### 5. Security Patterns
a. Input/Output Security:
- HTML escaping for user input
- XSS prevention
- Safe HTML template system
- Token validation

b. Session Management:
- Token-based authentication
- Session expiry handling
- Auto-redirect on auth failure

### 6. Utility Patterns
a. Data Formatting:
- Number formatting with decimals
- Date formatting
- Currency handling
- Frequency text localization

b. AJAX Handling:
- Centralized request function
- Standardized error handling
- Promise-based implementation
- Validation integration

### 7. Internationalization
- Translation system integration
- Dynamic text loading
- Format localization
- Currency localization

### 8. UI/UX Patterns
a. Loading States:
- Component-specific spinners
- Global loading management
- Visibility toggles

b. Error Handling:
- User-friendly error messages
- Validation feedback
- Session error handling
- Network error handling

c. Navigation:
- URL-based state management
- History API integration
- Parameter management

### 9. Code Style Conventions
- Camel case for function names
- Descriptive variable naming
- jQuery prefix for DOM elements ($)
- Component-based file organization
- Consistent error handling

## API Integration Pattern
- RESTful endpoint structure
- Standardized response format
- Central API request handling
- Error standardization
- Authentication header handling

## Future Considerations
1. Performance Optimization:
   - Bundle optimization
   - Cache strategy
   - Asset loading optimization

2. Code Organization:
   - Module bundling
   - Dependency management
   - Build process

3. Testing Strategy:
   - Unit testing setup
   - Integration testing
   - E2E testing consideration