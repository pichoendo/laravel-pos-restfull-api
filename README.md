# SIMPLE POS REST API WITH LARAVEL 11

This project is a **Simple POS** system that adheres to SOLID principles while implementing several advanced features for a robust and maintainable codebase. The application is designed with three distinct user roles:

- **Supervisor**: Has the highest level of access, capable of performing all operations within the system.
- **Admin**: Manages inventory, including items and stock.
- **Cashier**: Handles sales and customer interactions at the point of sale.

## Features

- **Laravel sanctum**: For robust authentication.
- **Spatie Permission**: For role-based access control (RBAC).
- **Laravel Jobs**: To handle email processing asynchronously.
- **Swagger**: Provides comprehensive API documentation.
- **Cache**: Improves performance by caching frequently accessed data, reducing database load and speeding up response times.


You can access the API documentation at `/api/docs`.

This project not only adheres to SOLID principles but also demonstrates their practical application in building a simple yet effective POS system.

## Installation

```bash
git clone https://github.com/your_username/pos-restful-api.git
cd pos-restful-api
composer install
cp .env.example .env .env.testing
php artisan key:generate
php artisan migrate
php artisan db:seed
