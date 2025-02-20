<?php
return [
    // General
    'site_name' => 'Budget Tracker',
    'site_description' => 'Modern solution for personal finance management',
    'welcome' => 'Welcome',
    'logout' => 'Logout',
    'settings' => 'Settings',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'update' => 'Update',
    'yes' => 'Yes',
    'no' => 'No',
    'confirm' => 'Confirm',
    'go_to_app' => 'Go to App',

    // Login/Register
    'username' => 'Username',
    'password' => 'Password',
    'remember_me' => 'Remember Me',
    'login' => [
        'title' => 'Login',
        'error_message' => 'Invalid username or password.',
        'no_account' => 'Don\'t have an account? Create one for free',
        'success' => 'Login successful! Redirecting...',
        'error' => 'An error occurred while logging in.',
        'required' => 'Please enter your username and password.',
        'invalid' => 'Invalid username or password.',
        'locked' => 'Your account has been locked. Please try again later.',
        'inactive' => 'Your account is not active yet. Please check your email.',
        'have_account' => 'Have an account? Login'
    ],

    // Footer
    'footer' => [
        'links' => 'Links',
        'contact' => 'Contact',
        'copyright' => 'All rights reserved.'
    ],

    // Home Page
    'hero' => [
        'title' => 'Manage Your Financial Freedom',
        'description' => 'Easily track your income, expenses, and savings. Reaching your financial goals has never been easier.',
        'cta' => 'Get Started Now'
    ],

    'features' => [
        'title' => 'Features',
        'income_tracking' => [
            'title' => 'Income Tracking',
            'description' => 'Categorize all your income and automatically track your regular earnings.'
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
            'text' => '"Thanks to this app, I can control my financial situation much better. Now I know where every penny goes."',
            'name' => 'John D.',
            'title' => 'Software Developer'
        ],
        '2' => [
            'text' => '"Tracking my savings goals is now so easy. The visual graphs boost my motivation."',
            'name' => 'Sarah M.',
            'title' => 'Teacher'
        ],
        '3' => [
            'text' => '"I never miss my regular payments anymore. The reminder system really works for me."',
            'name' => 'Mike R.',
            'title' => 'Business Owner'
        ]
    ],

    'cta' => [
        'title' => 'Shape Your Financial Future',
        'description' => 'Create a free account now and take control of your finances.',
        'button' => 'Start Free'
    ],

    // Validation
    'required' => 'This field is required',
    'min_length' => 'Must be at least :min characters',
    'max_length' => 'Must be at most :max characters',
    'email' => 'Please enter a valid email address',
    'match' => 'Passwords do not match',
    'unique' => 'This value is already in use',

    // Authentication
    'password_confirm' => 'Confirm Password',
    'forgot_password' => 'Forgot Password',
    'register_success' => 'Registration successful! You can now login.',
    'logout_confirm' => 'Are you sure you want to logout?',
    'logout_success' => 'Successfully logged out',

    // Income
    'incomes' => 'Incomes',
    'add_income' => 'Add New Income',
    'edit_income' => 'Edit Income',
    'income_name' => 'Income Name',
    'income_amount' => 'Amount',
    'income_date' => 'First Income Date',
    'income_category' => 'Category',
    'income_note' => 'Note',
    'income_recurring' => 'Recurring Income',
    'income_frequency' => 'Repeat Frequency',
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
        'mark_received' => 'Mark as Received',
        'mark_received_success' => 'Income marked as received',
        'mark_received_error' => 'An error occurred while marking income as received'
    ],

    // Payments
    'payments' => 'Payments',
    'add_payment' => 'Add New Payment',
    'edit_payment' => 'Edit Payment',
    'payment_name' => 'Payment Name',
    'payment_amount' => 'Amount',
    'payment_date' => 'Payment Date',
    'payment_category' => 'Category',
    'payment_note' => 'Note',
    'payment_recurring' => 'Recurring Payment',
    'payment_frequency' => 'Repeat Frequency',
    'payment_end_date' => 'End Date',
    'payment' => [
        'title' => 'Payment',
        'add_success' => 'Payment added successfully',
        'add_error' => 'An error occurred while adding payment',
        'edit_success' => 'Payment updated successfully',
        'edit_error' => 'An error occurred while updating payment',
        'delete_success' => 'Payment deleted successfully',
        'delete_error' => 'An error occurred while deleting payment',
        'delete_confirm' => 'Are you sure you want to delete this payment?',
        'mark_paid' => 'Mark as Paid',
        'mark_paid_success' => 'Payment marked as paid',
        'mark_paid_error' => 'An error occurred while marking payment as paid',
        'transfer_success' => 'Payments transferred successfully',
        'transfer_error' => 'An error occurred while transferring payments',
        'transfer_confirm' => 'Are you sure you want to transfer unpaid payments?'
    ],

    // Savings
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
        'behind' => 'Behind Schedule',
        'ahead' => 'Ahead of Schedule'
    ],

    // Currency
    'currency' => 'Currency',
    'base_currency' => 'Base Currency',
    'exchange_rate' => 'Exchange Rate',
    'update_rate' => 'Update with Current Rate',

    // Frequency
    'frequency' => [
        'none' => 'One Time',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'bimonthly' => 'Bimonthly',
        'quarterly' => 'Quarterly',
        'fourmonthly' => '4 Monthly',
        'fivemonthly' => '5 Monthly',
        'sixmonthly' => '6 Monthly',
        'yearly' => 'Yearly'
    ],

    // Months
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

    // Settings
    'settings_title' => 'User Settings',
    'theme' => 'Theme',
    'theme_light' => 'Light Theme',
    'theme_dark' => 'Dark Theme',
    'language' => 'Language',
    'current_password' => 'Current Password',
    'new_password' => 'New Password',
    'new_password_confirm' => 'Confirm New Password',

    // Errors
    'error' => 'Error!',
    'success' => 'Success!',
    'warning' => 'Warning!',
    'info' => 'Info',
    'error_occurred' => 'An error occurred',
    'try_again' => 'Please try again',
    'session_expired' => 'Your session has expired. Please login again.',
    'not_found' => 'Page not found',
    'unauthorized' => 'Unauthorized access',
    'forbidden' => 'Access forbidden',

    // Registration
    'register' => [
        'title' => 'Register',
        'error_message' => 'An error occurred during registration.',
        'success' => 'Registration successful! You can now login.',
        'username_taken' => 'This username is already taken.',
        'password_mismatch' => 'Passwords do not match.',
        'invalid_currency' => 'Invalid currency selection.',
        'required' => 'Please fill in all fields.',
    ],

    // Currencies
    'currencies' => [
        'base_info' => 'All calculations will be based on this currency. Don\'t worry, you can change it later.',
        'try' => 'Turkish Lira',
        'usd' => 'US Dollar',
        'eur' => 'Euro',
        'gbp' => 'British Pound'
    ],
];
