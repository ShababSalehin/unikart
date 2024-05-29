# Unikart E-Commerce Project

## Introduction
Welcome to Unikart, an elegant and comprehensive E-Commerce platform dedicated to Makeup, Skin Care, and Hair Care products. Designed for the vibrant online shopping community in Bangladesh, Unikart offers a seamless shopping experience for customers and efficient management tools for admins and sellers.

## Table of Contents
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Installation](#installation)
- [Modules](#modules)
  - [Admin Module](#admin-module)
  - [Seller Module](#seller-module)
  - [Customer Module](#customer-module)
- [API Documentation](#api-documentation)

## Features
- **User-Friendly Interface**: Responsive and intuitive design using Bootstrap 5.
- **Admin Module**: Complete control over the platform including user management, product management, order tracking, and analytics.
- **Seller Module**: Tools for managing inventory, processing orders, and tracking sales performance.
- **Customer Module**: Smooth shopping experience with features like product search, reviews, order tracking, and secure checkout.
- **Restful APIs**: Efficient data handling and interaction between frontend and backend.
- **Ajax Integration**: Dynamic content loading for a faster and smoother user experience.
- **Secure Authentication**: Robust login and registration system with validation.

## Technologies Used
- **Backend**: Laravel
- **Frontend**: JavaScript ES6, HTML5, Bootstrap 5, jQuery, AJAX
- **Database**: MySQL
- **APIs**: RESTful APIs

## Installation

### Prerequisites
Before you begin, ensure you have met the following requirements:
- PHP >= 7.3
- Composer
- Node.js & npm
- MySQL
- Git

### Steps

1. **Clone the Repository**
    ```bash
    git clone https://github.com/yourusername/unikart.git
    cd unikart
    ```

2. **Install Dependencies**
    ```bash
    composer install
    npm install
    ```

3. **Database Migration**
    Create a database named `unikart` and run migrations.
    ```bash
    php artisan migrate
    php artisan db:seed
    ```

4. **Run the Application**
    ```bash
    php artisan serve
    ```

## Modules

### Admin Module
The Admin Module provides comprehensive control over the entire platform. Admins can manage users, products, orders, and view detailed analytics.

**Features:**
- User Management: Add, edit, delete, and view users.
- Product Management: Add, edit, delete, and view products.
- Order Management: View, process, and track orders.
- Analytics: View sales and performance reports.

### Seller Module
The Seller Module allows sellers to manage their inventory and track their sales performance.

**Features:**
- Inventory Management: Add, edit, delete, and view products.
- Order Management: View and process orders.
- Sales Tracking: Monitor sales and performance metrics.

### Customer Module
The Customer Module provides a seamless shopping experience for users.

**Features:**
- Product Browsing: Search and view products.
- Reviews: Leave and read product reviews.
- Order Tracking: Track the status of orders.
- Secure Checkout: Safe and secure checkout process.

## API Documentation
The Unikart project includes a set of RESTful APIs for interacting with the platform. Below is a brief overview of the available endpoints.

### Authentication
- Register: `POST /api/register`
- Login: `POST /api/login`
- Logout: `POST /api/logout`

### Products
- Get All Products: `GET /api/products`
- Get Single Product: `GET /api/products/{id}`
- Create Product: `POST /api/products` (Admin/Seller only)
- Update Product: `PUT /api/products/{id}` (Admin/Seller only)
- Delete Product: `DELETE /api/products/{id}` (Admin/Seller only)

### Orders
- Get All Orders: `GET /api/orders` (Admin/Seller only)
- Get Single Order: `GET /api/orders/{id}`
- Create Order: `POST /api/orders`
- Update Order: `PUT /api/orders/{id}` (Admin only)
- Delete Order: `DELETE /api/orders/{id}` (Admin only)

### Users
- Get All Users: `GET /api/users` (Admin only)
- Get Single User: `GET /api/users/{id}` (Admin only)
- Update User: `PUT /api/users/{id}` (Admin only)
- Delete User: `DELETE /api/users/{id}` (Admin only)

For detailed API documentation, please refer to the `api.php` file in the `routes` directory.
