# CineVue - Cinema Ticket Reservation System

**Author:** Mădălina-Ioana Palade  
**University:** National University of Science and Technology Politehnica Bucharest  
**Faculty:** Automatic Control and Computer Science  
**Course:** Database Systems Project (2026)

## 📝 Project Overview
CineVue is a full-stack web application designed for managing movie theater operations and online ticket bookings. The project demonstrates the implementation of a relational database in a real-world scenario, covering everything from complex SQL queries to a modern reactive user interface.

## 🚀 Tech Stack

### Frontend
- **Vue.js 3** (Composition API) - For a dynamic and responsive UI.
- **Vue Router** - Managing Single Page Application (SPA) navigation.
- **Vite** - Next-generation frontend tooling.
- **Axios** - Handling asynchronous API requests.

### Backend
- **Laravel 10** (PHP) - Providing a robust RESTful API.
- **Laravel Sanctum** - Secure token-based authentication.
- **Eloquent ORM** - For intuitive database interactions.

### Database
- **MySQL 8.0** - Core relational database management system.
- **MySQL Workbench** - Used for E-R diagram design and schema modeling.


## 📊 Database Architecture
The application is built on a normalized relational schema to ensure data consistency and referential integrity.

### Key Entities:
- **Movies (`filme`):** Detailed information including duration and poster paths.
- **Projections (`proiectii`):** Linking movies to specific halls, dates, and pricing.
- **Tickets (`bilete`):** Managing seat reservations and client associations.
- **Users:** Role-based system (Admin vs. Client).


## ✨ Key Features

### 👤 For Customers:
* **Browse Movies:** View the current lineup with high-quality posters and details.
* **Smart Booking:** Real-time seat availability check and ticket purchasing.
* **Account Management:** Access to personal booking history and profile details.

### 🔐 For Administrators:
* **Content Management:** Full CRUD operations for movies, halls, and projections.
* **Advanced Analytics:** SQL-driven reports on ticket sales and popular screenings.
* **System Integrity:** Foreign key constraints to prevent orphaned data (e.g., blocking movie deletion if active projections exist).
