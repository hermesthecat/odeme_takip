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

### 1. Component Architecture Pattern
```javascript
// Data Display Pattern
function updateComponentList(data) {
    // 1. Clear existing content
    // 2. Handle empty state
    // 3. Iterate and render items
    // 4. Handle state updates
}

// Interactive Element Pattern
function handleComponentAction(id) {
    // 1. Confirm if needed
    // 2. Make API request
    // 3. Handle response
    // 4. Update UI
}
```

### 2. Data Management Patterns
a. Centralized Data Loading:
```javascript
// Root loading pattern
function loadData() {
    // 1. Show loading states
    // 2. Load core data
    // 3. Update summaries
    // 4. Lazy load component data
}

// Component-specific loading
function loadComponentData() {
    // 1. Fetch specific data
    // 2. Update component
    // 3. Handle errors
}
```

b. State Management:
- URL-based state for navigation
- Window-level data store
- Component-level state
- Progressive UI updates

### 3. AJAX Communication Pattern
```javascript
function ajaxRequest(data) {
    // 1. Input validation
    // 2. Request preparation
    // 3. Error handling
    // 4. Response processing
    // 5. Session management
}
```

### 4. UI Patterns
a. Progressive Loading:
- Loading indicators per component
- Lazy loading of secondary data
- State-based UI updates

b. Modal Management:
```javascript
function openUpdateModal(data) {
    // 1. Populate form data
    // 2. Configure validation
    // 3. Setup event handlers
    // 4. Show modal
}
```

c. Progress Visualization:
```javascript
// Standard progress calculation
const progress = (current / total) * 100;
const progressClass = progress < 25 ? 'bg-danger' :
    progress < 50 ? 'bg-warning' :
    progress < 75 ? 'bg-info' : 'bg-success';
```

### 5. Form Handling Patterns
a. Form Submission:
```javascript
$('.modal form').on('submit', function(e) {
    // 1. Prevent default
    // 2. Serialize data
    // 3. Validate
    // 4. Submit
    // 5. Handle response
    // 6. Update UI
});
```

b. Data Validation:
- Client-side validation rules
- Server-side validation
- Error message handling
- Internationalized messages

### 6. Error Handling Pattern
```javascript
// Consistent error display
Swal.fire({
    icon: 'error',
    title: translations.error_title,
    text: errorMessage
});

// API error handling
ajaxRequest().fail(function(error) {
    // 1. Log error
    // 2. Show user-friendly message
    // 3. Handle specific error types
});
```

### 7. Internationalization Pattern
- Translation key structure
- Dynamic message loading
- Formatted number/date handling
- Currency formatting

### 8. Security Patterns
- XSS Prevention:
  ```javascript
  function escapeHtml(unsafe) {
      return unsafe.replace(/[&<>"']/g, char => htmlEscapeMap[char]);
  }
  ```
- CSRF Protection
- Input Sanitization
- Session Management

### 9. Event Handling Patterns
- Delegated Events
- Event Bubbling Control
- Custom Event Handling
- Modal Event Management

## API Integration
1. Request Structure:
```javascript
{
    action: 'action_name',
    ...parameters
}
```

2. Response Format:
```javascript
{
    status: 'success|error',
    data: {},
    message: 'optional message'
}
```

## Future Considerations
1. State Management:
   - Consider implementing a more robust state management solution
   - Evaluate frontend framework adoption

2. Build Process:
   - Module bundling
   - Asset optimization
   - Code splitting

3. Testing Strategy:
   - Unit testing setup
   - Integration testing
   - E2E testing approach

4. Performance Optimization:
   - Code splitting
   - Lazy loading
   - Cache strategy
   - Asset optimization