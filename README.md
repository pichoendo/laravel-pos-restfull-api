# POS RESTful API

This project is a personal learning endeavor focused on developing a RESTful API using Laravel.

## Overview

This project implements a robust RESTful API for a Point of Sale (POS) system, designed to streamline retail management operations. Key features include:

Authentication & Authorization: Supports roles for supervisors, admins, and cashiers.
User Management: CRUD operations for user roles and permissions.
Category & Item Management: Organize inventory efficiently with category and item CRUD operations.
Coupon Management: Create, apply, and manage discounts seamlessly.
Sales Order Processing: Handle transactions and calculate commissions based on employee roles.
Commission & Salary Integration: Commissions are included in monthly salary calculations.
Member Benefits: Customers earn 1% of their sales as credit. credit can be used for next order.
This API project showcases my learning journey in building a comprehensive POS system using Laravel, focusing on key aspects of RESTful API development and advanced features integration.




## Technologies Used

- **Laravel**: PHP framework for building web applications, providing MVC architecture and robust features.
- **MySQL**: Relational database management system used for data storage.
- **RESTful API**: Architectural style for designing networked applications, leveraging HTTP protocols.
- **Git & GitHub**: Version control system and repository hosting service for collaborative development.

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your_username/pos-restful-api.git
   cd pos-restful-api
   php artisan migration
   php artisan db:seed
