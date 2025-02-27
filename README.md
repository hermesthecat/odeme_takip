# Bütçe Takip Sistemi (Budget Tracking System)

A modern web application for managing personal finances.

## Overview

This project is a PHP-based web application designed to help users manage their personal finances effectively.

## Goals

The primary goals of this application are to:

-   Provide a user-friendly platform for tracking income and expenses.
-   Enable users to manage their savings and investments.
-   Facilitate budgeting and financial planning.
-   Monitor currency values and stock market data (if applicable).

## System Design

The application follows a three-tier architecture:

-   **Presentation Tier:** Built with PHP, HTML, CSS, and JavaScript, this tier handles user interaction and data display.
-   **Application Tier:** The API, located in the `api/` directory, manages business logic, data processing, and communication with the database.
-   **Data Tier:** A MySQL database stores user data, financial transactions, and other relevant information.