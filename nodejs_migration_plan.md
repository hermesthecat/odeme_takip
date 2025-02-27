# Node.js Migration Plan for Bütçe Takip Sistemi

This document outlines a detailed plan for rewriting the existing PHP-based Bütçe Takip Sistemi (Budget Tracking System) in Node.js, while maintaining the same design and logic.

## 1. Project Setup and Technology Stack

*   **Initialize a new Node.js project:** Use `npm init` to create a `package.json` file.
*   **Choose a framework:** Express.js is a popular choice for building web applications in Node.js.
*   **Select a database:** We will continue using MySQL for data storage. A JavaScript-based ORM (Object-Relational Mapper) will be used to interact with the database.
*   **Install dependencies:** Install necessary packages like Express, database drivers (e.g., `mysql2`), ORM (e.g., Sequelize, TypeORM), templating engines (e.g., EJS, Handlebars), `node-cron`, and any other utilities.

## 2. Database Migration

*   **Analyze the existing `database.sql` file:** Understand the schema and data relationships in the current MySQL database.
*   **Choose a JavaScript-based ORM:** Select an ORM like Sequelize or TypeORM to define the models and generate the database schema.
*   **Define the data models:** Use the ORM to define the data models based on the existing `database.sql` schema, including tables for users, income, payments, savings, portfolio, etc.
*   **Migrate the data:** Use the ORM to generate the database schema and write scripts to extract data from the existing MySQL database and import it into the new database.

## 3. API Implementation

*   **Replicate the API endpoints:** Create equivalent API endpoints in Node.js using Express.js, mirroring the functionality of the existing PHP API endpoints in the `api/` directory.
*   **Implement business logic:** Translate the PHP business logic from the `api/` directory into JavaScript, including:
    *   User authentication (register, login, logout) from `api/auth.php`
    *   Admin functionality (user management) from `api/admin.php`
    *   Stock portfolio management from `api/borsa.php`
    *   Currency exchange rate fetching from `api/currency.php`
    *   Income management from `api/income.php`
    *   Payment management from `api/payments.php`
    *   Savings management from `api/savings.php`
    *   Financial summary calculation from `api/summary.php`
    *   Transfer of unpaid payments from `api/transfer.php`
    *   User settings updates from `api/user.php`
    *   Data validation from `api/validate.php`
    *   XSS sanitization from `api/xss.php`
*   **Implement data validation and sanitization:** Use appropriate libraries to validate and sanitize user input to prevent security vulnerabilities.
*   **Implement authentication and authorization:** Use middleware to handle user authentication and authorization, including CSRF protection, brute force protection, and strong password policies.

## 4. Frontend Development

*   **Choose a frontend framework (optional):** Consider using a frontend framework like React, Vue.js, or Angular to build a more interactive user interface.
*   **Recreate the UI:** Rebuild the user interface using HTML, CSS, and JavaScript, replicating the look and feel of the existing PHP application, including:
    *   The main application page (`app.php`) with financial summary, income list, savings list, and payment list.
    *   The stock portfolio management page (`borsa.php`).
    *   The admin page (`admin.php`).
    *   The login page (`login.php`).
    *   The registration page (`register.php`).
*   **Connect the frontend to the API:** Use AJAX or Fetch API to communicate with the Node.js API.

## 5. Cron Jobs

*   **Reimplement cron jobs:** Use a Node.js library like `node-cron` to schedule and execute the cron jobs previously handled by `cron_borsa.php` and `cron_borsa_worker.php` for updating stock prices.

## 6. Code Structure and Organization

*   **Follow a modular structure:** Organize the code into modules based on functionality (e.g., models, routes, controllers, middleware).
*   **Use a consistent coding style:** Follow a consistent coding style to improve readability and maintainability.
*   **Write unit tests:** Write unit tests to ensure the code is working correctly.

## 7. Deployment

*   **Choose a deployment platform:** Select a platform for deploying the Node.js application (e.g., Heroku, AWS, Google Cloud).
*   **Configure the environment:** Configure the environment variables and settings for the deployment platform.
*   **Deploy the application:** Deploy the application to the chosen platform.

## Detailed Steps

1.  **Set up the Node.js project:**
    *   Create a new directory named `nodejs` for the project.
    *   Navigate into the `nodejs` directory.
    *   Run `npm init -y` to create a `package.json` file.
    *   Install Express: `npm install express`
    *   Create an `app.js` file as the main entry point.
2.  **Database Setup:**
    *   Choose an ORM (e.g., Sequelize, TypeORM).
    *   Install the appropriate database driver and ORM (e.g., `npm install mysql2 sequelize`).
    *   Define the data models using the ORM based on the existing `database.sql` schema, including models for users, income, payments, savings, portfolio, etc.
3.  **API Implementation (Example: `api/auth.php`):**
    *   Create a route file for authentication (e.g., `routes/auth.js`).
    *   Implement the `register`, `login`, and `logout` endpoints using Express.js.
    *   Use middleware for authentication and authorization, including CSRF protection, brute force protection, and strong password policies.
4.  **Frontend Integration:**
    *   Create a `public` directory to store static files (HTML, CSS, JavaScript).
    *   Create HTML files for the user interface, replicating the existing PHP templates, including the main application page, stock portfolio management page, admin page, login page, and registration page.
    *   Use JavaScript to make API requests to the Node.js backend.
5.  **Cron Jobs:**
    *   Install `node-cron`: `npm install node-cron`
    *   Create a cron job to update stock prices, similar to `cron_borsa.php`.
6.  **Testing:**
    *   Use a testing framework like Jest or Mocha to write unit tests for the API endpoints and other critical functionality.

This is a high-level plan, and the specific steps may vary depending on the chosen technologies and the complexity of the existing application.