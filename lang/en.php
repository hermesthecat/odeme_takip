<?php
return [
    'language_name' => 'English',
    // Genel
    'site_name' => 'Budget Tracker',
    'site_description' => 'Modern solution that simplifies personal finance management',
    'welcome' => 'Welcome',
    'logout' => 'Logout',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'update' => 'Update',
    'yes' => 'Yes',
    'no' => 'No',
    'confirm' => 'Confirm',
    'go_to_app' => 'Go to App',

    // Giriş/Kayıt
    'username' => 'Username',
    'password' => 'Password',
    'remember_me' => 'Remember Me',
    'login' => [
        'title' => 'Login',
        'error_message' => 'Invalid username or password.',
        'no_account' => 'Don\'t have an account? Create a free account',
        'success' => 'Login successful! Redirecting...',
        'error' => 'An error occurred while logging in.',
        'required' => 'Please enter your username and password.',
        'invalid' => 'Invalid username or password.',
        'locked' => 'Your account is locked. Please try again later.',
        'inactive' => 'Your account is not yet active. Please check your email.',
        'have_account' => 'Have an account? Login'
    ],

    // Footer
    'footer' => [
        'links' => 'Links',
        'contact' => 'Contact',
        'copyright' => 'All rights reserved.'
    ],

    // Ana Sayfa
    'hero' => [
        'title' => 'Manage Your Financial Freedom',
        'description' => 'Easily track your income, expenses, and savings. Reaching your financial goals has never been easier.',
        'cta' => 'Get Started Now'
    ],

    'features' => [
        'title' => 'Features',
        'income_tracking' => [
            'title' => 'Income Tracking',
            'description' => 'Categorize all your income and automatically track your regular income.'
        ],
        'expense_management' => [
            'title' => 'Expense Management',
            'description' => 'Keep your expenses under control and easily manage your payment plans.'
        ],
        'savings_goals' => [
            'title' => 'Savings Goals',
            'description' => 'Set your financial goals and visually track your progress.'
        ]
    ],

    'testimonials' => [
        'title' => 'Testimonials',
        '1' => [
            'text' => '"Thanks to this application, I can control my financial situation much better. Now I know where every penny goes."',
            'name' => 'Ahmet Y.',
            'title' => 'Software Developer'
        ],
        '2' => [
            'text' => '"Tracking my savings goals is now very easy. Visual charts increase my motivation."',
            'name' => 'Ayşe K.',
            'title' => 'Teacher'
        ],
        '3' => [
            'text' => '"I never miss my regular payments anymore. The reminder system really helps me a lot."',
            'name' => 'Mehmet S.',
            'title' => 'Tradesman'
        ]
    ],

    'cta' => [
        'title' => 'Shape Your Financial Future',
        'description' => 'Create a free account now and take financial control.',
        'button' => 'Start Free'
    ],

    // Doğrulama
    'required' => 'This field is required',
    'min_length' => 'Must be at least :min characters',
    'max_length' => 'Must be at most :max characters',
    'email' => 'Please enter a valid email address',
    'match' => 'Passwords do not match',
    'unique' => 'This value is already in use',

    // Kimlik Doğrulama
    'password_confirm' => 'Password Confirm',
    'forgot_password' => 'Forgot My Password',
    'login_success' => 'Login successful!',
    'logout_confirm' => 'Are you sure you want to logout?',
    'logout_success' => 'Successfully logged out',
    'auth' => [
        'invalid_request' => 'Invalid request',
        'username_min_length' => 'Username must be at least 3 characters',
        'password_min_length' => 'Password must be at least 6 characters',
        'password_mismatch' => 'Passwords do not match',
        'username_taken' => 'This username is already in use',
        'register_success' => 'Registration successful!',
        'register_error' => 'An error occurred during registration',
        'database_error' => 'A database error occurred',
        'credentials_required' => 'Username and password are required',
        'login_success' => 'Login successful',
        'invalid_credentials' => 'Invalid username or password',
        'logout_success' => 'Logout successful',
        'session_expired' => 'Your session has expired, please login again',
        'account_locked' => 'Your account is locked, please try again later',
        'account_inactive' => 'Your account is not yet active',
        'remember_me' => 'Remember Me',
        'forgot_password' => 'Forgot My Password',
        'reset_password' => 'Reset Password',
        'reset_password_success' => 'Password reset link sent to your email address',
        'reset_password_error' => 'An error occurred while resetting the password'
    ],

    // Gelirler
    'incomes' => 'Incomes',
    'add_income' => 'Add New Income',
    'edit_income' => 'Edit Income',
    'income_name' => 'Income Name',
    'income_amount' => 'Amount',
    'income_date' => 'First Income Date',
    'income_category' => 'Category',
    'income_note' => 'Note',
    'income_recurring' => 'Recurring Income',
    'income_frequency' => 'Repetition Frequency',
    'income_end_date' => 'End Date',
    'income' => [
        'title' => 'Income',
        'add_success' => 'Income added successfully',
        'add_error' => 'An error occurred while adding income',
        'edit_success' => 'Income updated successfully',
        'edit_error' => 'An error occurred while updating income',
        'delete_success' => 'Income deleted successfully',
        'delete_error' => 'An error occurred while deleting income',
        'delete_confirm' => 'Are you sure you want to delete this income?',
        'mark_received' => [
            'success' => 'Income successfully marked as received',
            'error' => 'Income could not be marked as received',
            'mark_as_received' => 'Mark as received',
            'mark_as_not_received' => 'Mark as not received'
        ],
        'not_found' => 'No income has been added yet',
        'load_error' => 'An error occurred while loading incomes',
        'update_error' => 'An error occurred while updating income',
        'update_children_error' => 'An error occurred while updating related incomes',
        'rate_error' => 'Could not retrieve exchange rate',
        'id' => 'Income ID',
        'name' => 'Income Name',
        'amount' => 'Amount',
        'currency' => 'Currency',
        'date' => 'Date',
        'frequency' => 'Frequency',
        'update_children' => 'Update related incomes',
        'update_children_info_parent' => 'When this option is checked, all incomes related to this income will be updated.',
        'update_children_info_child' => 'When this option is checked, this income and subsequent incomes will be updated.',
        'modal' => [
            'current_rate' => 'Current rate',
            'success_title' => 'Success',
            'success_message' => 'Income updated successfully',
            'error_title' => 'Error',
            'error_message' => 'An error occurred while updating income',
            'error_not_found' => 'Income with ID :id not found'
        ],
        'status' => 'Status',
        'next_date' => 'Next Date',
        'total_amount' => 'Total Amount',
        'remaining_amount' => 'Remaining Amount',
        'received_amount' => 'Received Amount',
        'pending_amount' => 'Pending Amount',
        'recurring_info' => 'Repetition Information',
        'recurring_count' => 'Repetition Count',
        'recurring_total' => 'Total Repetition',
        'recurring_remaining' => 'Remaining Repetition',
        'recurring_completed' => 'Completed Repetition',
        'recurring_next' => 'Next Repetition',
        'recurring_last' => 'Last Repetition'
    ],

    // Ödemeler
    'payments' => 'Payments',
    'add_payment' => 'Add New Payment',
    'edit_payment' => 'Edit Payment',
    'payment_name' => 'Payment Name',
    'payment_amount' => 'Amount',
    'payment_date' => 'Payment Date',
    'payment_category' => 'Category',
    'payment_note' => 'Note',
    'payment_recurring' => 'Recurring Payment',
    'payment_frequency' => 'Repetition Frequency',
    'payment_end_date' => 'End Date',
    'payment' => [
        'title' => 'Payment',
        'add_success' => 'Payment added successfully',
        'add_error' => 'An error occurred while adding payment',
        'add_recurring_error' => 'An error occurred while adding recurring payment',
        'edit_success' => 'Payment updated successfully',
        'edit_error' => 'An error occurred while updating payment',
        'delete_success' => 'Payment deleted successfully',
        'delete_error' => 'An error occurred while deleting payment',
        'delete_confirm' => 'Are you sure you want to delete this payment?',
        'mark_paid' => [
            'success' => 'Payment successfully marked as paid',
            'error' => 'Payment could not be marked as paid'
        ],
        'mark_not_paid' => 'Mark as not paid',
        'not_found' => 'No payment has been added yet',
        'load_error' => 'An error occurred while loading payments',
        'update_error' => 'An error occurred while updating payment',
        'rate_error' => 'Could not retrieve exchange rate',
        'id' => 'Payment ID',
        'name' => 'Payment Name',
        'amount' => 'Amount',
        'currency' => 'Currency',
        'date' => 'Date',
        'frequency' => 'Repetition Frequency',
        'end_date' => 'End Date',
        'status' => 'Status',
        'next_date' => 'Next Date',
        'total_amount' => 'Total Amount',
        'remaining_amount' => 'Remaining Amount',
        'paid_amount' => 'Paid Amount',
        'pending_amount' => 'Pending Amount',
        'recurring_info' => 'Repetition Information',
        'recurring_count' => 'Repetition Count',
        'recurring_total' => 'Total Repetition',
        'recurring_remaining' => 'Remaining Repetition',
        'recurring_completed' => 'Completed Repetition',
        'recurring_next' => 'Next Repetition',
        'recurring_last' => 'Last Repetition',
        'transfer' => 'Transfer to Next Month',
        'recurring' => [
            'total_payment' => 'Total Payment',
            'pending_payment' => 'Pending Payment'
        ],
        'buttons' => [
            'delete' => 'Delete',
            'edit' => 'Edit',
            'mark_paid' => 'Mark as paid',
            'mark_not_paid' => 'Mark as not paid'
        ]
    ],

    // Birikimler
    'savings' => 'Savings',
    'add_saving' => 'Add New Saving',
    'edit_saving' => 'Edit Saving',
    'saving_name' => 'Saving Name',
    'target_amount' => 'Target Amount',
    'current_amount' => 'Current Amount',
    'start_date' => 'Start Date',
    'target_date' => 'Target Date',
    'saving' => [
        'title' => 'Saving',
        'add_success' => 'Saving added successfully',
        'add_error' => 'An error occurred while adding saving',
        'edit_success' => 'Saving updated successfully',
        'edit_error' => 'An error occurred while updating saving',
        'delete_success' => 'Saving deleted successfully',
        'delete_error' => 'An error occurred while deleting saving',
        'delete_confirm' => 'Are you sure you want to delete this saving?',
        'progress' => 'Progress',
        'remaining' => 'Remaining Amount',
        'remaining_days' => 'Remaining Days',
        'monthly_needed' => 'Monthly Amount Needed',
        'completed' => 'Completed',
        'on_track' => 'On Track',
        'behind' => 'Behind',
        'ahead' => 'Ahead',
        'load_error' => 'An error occurred while loading savings',
        'not_found' => 'No saving has been added yet',
        'update_error' => 'An error occurred while updating saving',
        'name' => 'Saving Name',
        'target_amount' => 'Target Amount',
        'current_amount' => 'Current Amount',
        'currency' => 'Currency',
        'start_date' => 'Start Date',
        'target_date' => 'Target Date',
        'exchange_rate' => 'Exchange Rate',
        'status' => 'Status',
        'progress_info' => 'Progress Information',
        'daily_needed' => 'Daily Amount Needed',
        'weekly_needed' => 'Weekly Amount Needed',
        'yearly_needed' => 'Yearly Amount Needed',
        'completion_date' => 'Estimated Completion Date',
        'completion_rate' => 'Completion Rate',
        'days_left' => 'Days Left',
        'days_total' => 'Total Days',
        'days_passed' => 'Days Passed',
        'expected_progress' => 'Expected Progress',
        'actual_progress' => 'Actual Progress',
        'progress_difference' => 'Progress Difference',
        'update_amount' => 'Update Amount',
        'update_details' => 'Update Details'
    ],

    // Para Birimleri
    'currency' => 'Currency',
    'base_currency' => 'Base Currency',
    'exchange_rate' => 'Exchange Rate',
    'update_rate' => 'Update with Current Rate',

    // Sıklık
    'frequency' => [
        'none' => 'One Time',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'bimonthly' => 'Bimonthly',
        'quarterly' => 'Quarterly',
        'fourmonthly' => 'Four Monthly',
        'fivemonthly' => 'Five Monthly',
        'sixmonthly' => 'Six Monthly',
        'yearly' => 'Yearly'
    ],

    // Aylar
    'months' => [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December'
    ],

    // Ayarlar
    'settings_title' => 'User Settings',
    'theme' => 'Theme',
    'theme_light' => 'Light Theme',
    'theme_dark' => 'Dark Theme',
    'language' => 'Language',
    'current_password' => 'Current Password',
    'new_password' => 'New Password',
    'new_password_confirm' => 'New Password Confirm',

    // Hatalar
    'error' => 'Error!',
    'success' => 'Success!',
    'warning' => 'Warning!',
    'info' => 'Info',
    'error_occurred' => 'An error occurred',
    'try_again' => 'Please try again',
    'session_expired' => 'Your session has expired. Please log in again.',
    'not_found' => 'Page not found',
    'unauthorized' => 'Unauthorized access',
    'forbidden' => 'Access denied',

    // Yeni eklenen kısımlar
    'register' => [
        'title' => 'Create Account',
        'error_message' => 'An error occurred while registering.',
        'success' => 'Registration successful! You can log in.',
        'username_taken' => 'This username is already in use.',
        'password_mismatch' => 'Passwords do not match.',
        'invalid_currency' => 'Invalid currency selection.',
        'required' => 'Please fill in all fields.',
    ],

    // Currencies
    'currencies' => [
        'base_info' => 'All calculations will be made with this currency. Don\'t worry, you can change it later.',
        'try' => 'Turkish Lira',
        'usd' => 'American Dollar',
        'eur' => 'Euro',
        'gbp' => 'British Pound'
    ],

    // Ayarlar
    'settings' => [
        'title' => 'User Settings',
        'base_currency' => 'Base Currency',
        'base_currency_info' => 'All calculations will be made with this currency.',
        'theme' => 'Theme',
        'theme_light' => 'Light Theme',
        'theme_dark' => 'Dark Theme',
        'theme_info' => 'Interface color theme selection.',
        'language' => 'Language',
        'language_info' => 'Interface language selection.',
        'save_success' => 'Settings saved successfully',
        'save_error' => 'An error occurred while saving settings',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'new_password_confirm' => 'New Password Confirm',
        'password_success' => 'Password changed successfully',
        'password_error' => 'An error occurred while changing the password',
        'password_mismatch' => 'Current password is incorrect',
        'password_same' => 'New password cannot be the same as the old one',
        'password_requirements' => 'Password must be at least 6 characters'
    ],

    // Uygulama
    'app' => [
        'previous_month' => 'Previous Month',
        'next_month' => 'Next Month',
        'monthly_income' => 'Monthly Income',
        'monthly_expense' => 'Monthly Expense',
        'net_balance' => 'Net Balance',
        'period' => 'Period',
        'next_income' => 'Next Income',
        'next_payment' => 'Next Payment',
        'payment_power' => 'Payment Power',
        'installment_info' => 'Installment Information',
        'total' => 'Total',
        'total_payment' => 'Total Payment',
        'loading' => 'Loading...',
        'no_data' => 'No data found',
        'confirm_delete' => 'Are you sure you want to delete?',
        'yes_delete' => 'Yes, Delete',
        'no_cancel' => 'No, Cancel',
        'operation_success' => 'Operation successful',
        'operation_error' => 'An error occurred during the operation',
        'save_success' => 'Successfully saved',
        'save_error' => 'An error occurred while saving',
        'update_success' => 'Successfully updated',
        'update_error' => 'An error occurred while updating',
        'delete_success' => 'Successfully deleted',
        'delete_error' => 'An error occurred while deleting'
    ],

    // Para birimi işlemleri
    'currency' => [
        'invalid_request' => 'Invalid request',
        'invalid_currency' => 'Invalid currency',
        'update_success' => 'Currency updated successfully',
        'update_error' => 'An error occurred while updating currency',
        'database_error' => 'A database error occurred',
        'currency_required' => 'Currency selection is required',
        'rate_fetched' => 'Exchange rate fetched successfully',
        'rate_fetch_error' => 'Could not retrieve exchange rate',
        'rate_not_found' => 'Exchange rate not found',
        'select_currency' => 'Select Currency',
        'current_rate' => 'Current Rate',
        'conversion_rate' => 'Conversion Rate',
        'last_update' => 'Last Update',
        'auto_update' => 'Automatic Update',
        'manual_update' => 'Manual Update',
        'update_daily' => 'Update Daily',
        'update_weekly' => 'Update Weekly',
        'update_monthly' => 'Update Monthly',
        'update_never' => 'Do Not Update'
    ],

    // Özet
    'summary' => [
        'title' => 'Summary',
        'load_error' => 'An error occurred while loading summary information',
        'user_not_found' => 'User not found',
        'total_income' => 'Total Income',
        'total_expense' => 'Total Expense',
        'net_balance' => 'Net Balance',
        'positive_balance' => 'Positive',
        'negative_balance' => 'Negative',
        'monthly_summary' => 'Monthly Summary',
        'yearly_summary' => 'Yearly Summary',
        'income_vs_expense' => 'Income/Expense Ratio',
        'savings_progress' => 'Savings Progress',
        'payment_schedule' => 'Payment Schedule',
        'upcoming_payments' => 'Upcoming Payments',
        'upcoming_incomes' => 'Upcoming Incomes',
        'expense_percentage' => 'Expense Percentage',
        'savings_percentage' => 'Savings Percentage',
        'monthly_trend' => 'Monthly Trend',
        'yearly_trend' => 'Yearly Trend',
        'balance_trend' => 'Balance Trend',
        'expense_trend' => 'Expense Trend',
        'income_trend' => 'Income Trend',
        'savings_trend' => 'Savings Trend',
        'budget_status' => 'Budget Status',
        'on_budget' => 'On Budget',
        'over_budget' => 'Over Budget',
        'under_budget' => 'Under Budget',
        'budget_warning' => 'Budget Warning',
        'budget_alert' => 'Budget Alert',
        'expense_categories' => 'Expense Categories',
        'income_sources' => 'Income Sources',
        'savings_goals' => 'Savings Goals',
        'payment_methods' => 'Payment Methods',
        'recurring_transactions' => 'Recurring Transactions',
        'financial_goals' => 'Financial Goals',
        'goal_progress' => 'Goal Progress',
        'goal_completion' => 'Goal Completion',
        'goal_status' => 'Goal Status',
        'completed_goals' => 'Completed Goals',
        'active_goals' => 'Active Goals',
        'missed_goals' => 'Missed Goals',
        'goal_history' => 'Goal History'
    ],

    'transfer' => [
        'title' => 'Payment Transfer',
        'confirm' => 'Are you sure you want to transfer unpaid payments to the next month?',
        'transfer_button' => 'Yes, transfer',
        'cancel_button' => 'Cancel',
        'error' => 'An error occurred while transferring payments',
        'success' => 'Payments transferred successfully',
        'no_unpaid_payments' => 'No unpaid payments found to transfer',
        'payment_transferred_from' => '%s (transferred from %s month)',
        'update_error' => 'Payment could not be updated'
    ],

    'validation' => [
        'field_required' => '%s field is required',
        'field_numeric' => '%s field must be numeric',
        'field_date' => '%s field must be a valid date (YYYY-MM-DD)',
        'field_currency' => '%s field must be a valid currency',
        'field_frequency' => '%s field must be a valid repetition frequency',
        'field_min_value' => '%s field must be at least %s',
        'field_exchange_rate' => '%s field must be a valid exchange rate',
        'field_max_value' => '%s field must be at most %s',
        'date_range_error' => 'Start date cannot be greater than end date',
        'invalid_format' => 'Invalid format',
        'invalid_value' => 'Invalid value',
        'required_field' => 'This field is required',
        'min_length' => 'Must be at least %s characters',
        'max_length' => 'Must be at most %s characters',
        'exact_length' => 'Must be exactly %s characters',
        'greater_than' => 'Must be greater than %s',
        'less_than' => 'Must be less than %s',
        'between' => 'Must be between %s and %s',
        'matches' => 'Must match %s',
        'different' => 'Must be different from %s',
        'unique' => 'This value is already in use',
        'valid_email' => 'Please enter a valid email address',
        'valid_url' => 'Please enter a valid URL',
        'valid_ip' => 'Please enter a valid IP address',
        'valid_date' => 'Please enter a valid date',
        'valid_time' => 'Please enter a valid time',
        'valid_datetime' => 'Please enter a valid date and time',
        'alpha' => 'Must contain only letters',
        'alpha_numeric' => 'Must contain only letters and numbers',
        'alpha_dash' => 'Must contain only letters, numbers, dashes, and underscores',
        'numeric' => 'Must contain only numbers',
        'integer' => 'Must be an integer',
        'decimal' => 'Must be a decimal number',
        'natural' => 'Must be a positive integer',
        'natural_no_zero' => 'Must be a positive integer greater than zero',
        'valid_base64' => 'Please enter a valid Base64 value',
        'valid_json' => 'Please enter a valid JSON value',
        'valid_file' => 'Please select a valid file',
        'valid_image' => 'Please select a valid image file',
        'valid_phone' => 'Please enter a valid phone number',
        'valid_credit_card' => 'Please enter a valid credit card number',
        'valid_color' => 'Please enter a valid color code'
    ],

    // Utils
    'utils' => [
        'validation' => [
            'required' => ':field field is required',
            'numeric' => ':field field must be numeric',
            'date' => ':field field must be a valid date',
            'currency' => ':field field must be a valid currency',
            'frequency' => ':field field must be a valid repetition frequency',
            'min_value' => ':field field must be at least :min',
            'max_value' => ':field field must be at most :max',
            'error_title' => 'Validation Error',
            'confirm_button' => 'OK'
        ],
        'session' => [
            'error_title' => 'Session Error',
            'invalid_token' => 'Invalid security token'
        ],
        'frequency' => [
            'none' => 'No Repetition',
            'monthly' => 'Monthly',
            'bimonthly' => '2 Monthly',
            'quarterly' => '3 Monthly',
            'fourmonthly' => '4 Monthly',
            'fivemonthly' => '5 Monthly',
            'sixmonthly' => '6 Monthly',
            'yearly' => 'Yearly'
        ],
        'form' => [
            'income_name' => 'Income name',
            'payment_name' => 'Payment name',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'date' => 'Date',
            'frequency' => 'Repetition frequency',
            'saving_name' => 'Saving name',
            'target_amount' => 'Target amount',
            'current_amount' => 'Current amount',
            'start_date' => 'Start date',
            'target_date' => 'Target date'
        ]
    ],

    'user' => [
        'not_found' => 'User information not found',
        'update_success' => 'User information updated successfully',
        'update_error' => 'User information could not be updated'
    ],
];
