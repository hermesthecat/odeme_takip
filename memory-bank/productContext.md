# Bütçe Takip Sistemi (Budget Tracking System)

## Project Overview
A comprehensive personal finance management web application that helps users track and manage their financial life across multiple dimensions.

## Core Features
1. Multi-currency Financial Management
   - Support for different currencies with real-time exchange rates
   - Base currency preference per user
   - Currency conversion across features

2. Income Tracking
   - Recurring income entries
   - Multi-currency support
   - Status tracking (pending/received)
   - Exchange rate history

3. Payment Management
   - Recurring payment scheduling
   - Multi-currency support
   - Payment status tracking (pending/paid)
   - Payment history tracking

4. Stock Portfolio Management
   - Real-time stock tracking
   - Purchase and sale recording
   - Partial sale support
   - Price history tracking
   - Performance analytics

5. Savings Goals
   - Target amount setting
   - Progress tracking
   - Multi-currency support
   - Timeline management
   - History tracking of contributions
   - Exchange rate integration (in progress)

6. System Features
   - Multi-language support (20+ languages)
   - Theme customization (light/dark)
   - User authentication and authorization
   - Activity logging
   - Admin capabilities
   - Comprehensive history tracking

## Technical Stack
- Backend: PHP (7.4.33)
- Database: MariaDB (10.11)
- Frontend: JavaScript
- Authentication: Custom implementation with remember-me functionality
- API Structure: RESTful endpoints
- Character Set: UTF-8 (utf8mb4_turkish_ci)

## Memory Bank Structure
Core files and their purposes:
- productContext.md (this file): Project overview, features, and technical context
- activeContext.md: Current development session state and focus
- progress.md: Development progress tracking and next steps
- decisionLog.md: Architecture and implementation decisions
- systemPatterns.md: Identified patterns and conventions

## Constraints
1. Database: MariaDB 10.11
   - Full UTF-8 support required
   - Transaction support needed
   - History table requirements

2. PHP Version: 7.4.33
   - Must maintain compatibility
   - Error handling requirements
   - Security considerations

3. Browser Support
   - Modern browsers
   - Responsive design
   - Mobile compatibility

4. Performance Requirements
   - Quick page loads
   - Efficient data processing
   - Responsive UI

## Integration Requirements
1. External Services
   - Exchange rate providers
   - Stock market data sources
   - Future payment gateway integration

2. Data Exchange
   - JSON API responses
   - Secure data transmission
   - Rate limiting considerations

## Security Requirements
1. Authentication
   - Secure session management
   - Password policies
   - Remember-me functionality

2. Data Protection
   - Input validation
   - Output sanitization
   - CSRF protection
   - XSS prevention

3. Access Control
   - Role-based permissions
   - Feature access control
   - Admin capabilities