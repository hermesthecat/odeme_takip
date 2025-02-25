# System Patterns

**System Architecture:**

The application follows a traditional Model-View-Controller (MVC) architecture:

*   **Model:** Represents the data and business logic of the application. This includes classes for managing users, income, expenses, savings, and investments. The database schema is defined in `database.sql`.
*   **View:** Represents the user interface of the application. This includes PHP files for rendering HTML pages, such as `index.php`, `login.php`, `register.php`, and `app.php`.
*   **Controller:** Handles user requests and interacts with the model to retrieve and update data. This includes PHP files for handling API requests, such as `api.php`, and CRON scripts for automating tasks, such as `cron_borsa.php`.

**Key Technical Decisions:**

*   Using PHP and MySQL for the backend development.
*   Using Bootstrap and jQuery for the frontend development.
*   Using SweetAlert2 for displaying alerts and notifications.
*   Using a custom language class for multi-language support.
*   Using a custom log class for logging system events.

**Design Patterns in Use:**

*   **Singleton:** The `Language` class uses the singleton pattern to ensure that only one instance of the class is created.
*   **MVC:** The application follows the MVC pattern to separate concerns and improve maintainability.

**Component Relationships:**

*   The `index.php` file includes the `header.php`, `navbar.php`, `footer_body.php`, and `footer.php` files to render the main landing page.
*   The `app.php` file includes the `header.php`, `navbar.php`, `modals.php`, `footer_body.php`, and `footer.php` files to render the main application page.
*   The `api.php` file includes various API files to handle user requests.
*   The `cron_borsa.php` file uses the `cron_borsa_worker.php` file to update stock prices in parallel.