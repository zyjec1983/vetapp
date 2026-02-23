VetApp 🐾 - Professional Veterinary Management System

VetApp is a robust backend-focused system built with Vanilla PHP 8.1+, designed with a strong emphasis on Software Engineering principles. It leverages an MVC + Repository + Middleware architecture to ensure scalability, security, and clean code.
🚀 Key Architectural Features

Unlike traditional "spaghetti" PHP projects, VetApp follows modern standards used in frameworks like Laravel or Spring Boot:

    Repository Pattern: Decouples business logic from data access, making the system database-agnostic.

    Custom Middleware Pipeline: Handles Authentication and RBAC (Role-Based Access Control) before requests reach the controllers.

    Domain Entities: Uses rich models with strict typing and business rule validation.

    Centralized Bootstrap & Autoloading: Implements a custom PSR-4 inspired autoloader to manage dependencies efficiently.

    Singleton Pattern: Optimized database connection management.

🛠️ Tech Stack & Security

    Backend: PHP 8.1+ (Strict types enabled)

    Database: MySQL / MariaDB (PDO with Prepared Statements)

    Frontend: HTML5, CSS3, Bootstrap 5

    Security: * One-way password hashing using bcrypt.

        XSS Protection via output escaping.

        CSRF-ready architecture.

        Protected /app directory (Logic is not exposed to the web).

📁 Project Structure
Plaintext

vetapp/
├── app/                # Core Application Logic
│   ├── config/         # System & DB Configuration
│   ├── controllers/    # Request Handling
│   ├── middleware/     # Auth & Role Guards
│   ├── models/         # Domain Entities (Business Rules)
│   ├── repositories/   # Data Access Layer (SQL)
│   ├── helpers/        # Global Utility Functions
│   └── views/          # UI Templates (HTML/PHP)
├── public/             # Entry Point (Front Controller & Assets)
├── database/           # SQL Migrations/Scripts
└── storage/            # Logs & File Uploads

🔧 Installation & Setup

    Clone the repository: git clone https://github.com/zyjec1983/vetapp

    Database: Import database/vetapp.sql into your MySQL server.

    Configuration:

        Update your credentials in app/config/Database.php.

        Set your BASE_URL in app/config/config.php.

    Run: Point your web server to the public/ folder.

📈 Roadmap

    [x] Core Architecture & Autoloading

    [x] Authentication System (Login/Logout)

    [x] Role-Based Access Control (Admin/Vet/Pharmacy)

    [ ] Patient & Medical Records Module

    [ ] Appointment Scheduling System

    [ ] REST API Integration for Mobile App

👨‍💻 Author

Christian Rodríguez Software Developer focused on Clean Code and Backend Architecture.
---------------------------------------------------------
---------------------------------------------------------
--------------------------------------------------------- 

TRADUCCIÓN AL ESPAÑOL
# VetApp 🐾

Sistema de gestión veterinaria desarrollado en PHP puro siguiendo una arquitectura **MVC + Repository + Middleware**, con énfasis en buenas prácticas, separación de responsabilidades y seguridad.

-----------------------------------------------------------

## Objetivo del proyecto

VetApp es un proyecto educativo–profesional cuyo objetivo es:

- Aprender MVC de forma **correcta**
- Evitar acoplamiento entre capas
- Facilitar mantenimiento y escalabilidad
- Preparar la base para frameworks como Laravel o Symfony

-----------------------------------------------------------

## Arquitectura

El proyecto utiliza las siguientes capas:

- **Controllers**: Orquestan la lógica de la aplicación
- **Models**: Representan entidades del dominio
- **Repositories**: Acceso a datos (PDO, SQL)
- **Middleware**: Autenticación y control de roles
- **Views**: Presentación (HTML + Bootstrap)
- **Helpers**: Funciones reutilizables
- **Public**: Punto de entrada del sistema

-----------------------------------------------------------

## Estructura del proyecto
vetapp/
├── app/
│ ├── config/
│ ├── controllers/
│ ├── middleware/
│ ├── models/
│ ├── views/
│ └── helpers/
├── repositories/
├── database/
├── public/
├── storage/
└── README.md


-----------------------------------------------------------

## Seguridad

- Contraseñas con `password_hash()` / `password_verify()`
- Middleware de autenticación
- Middleware de roles
- Acceso a datos mediante PDO (prepared statements)

-----------------------------------------------------------

## Requisitos

- PHP >= 8.1
- MySQL / MariaDB
- Servidor Apache (XAMPP / Laragon recomendado)

-----------------------------------------------------------

## Instalación

1. Clonar el repositorio
2. Crear la base de datos usando `database/vetapp.sql`
3. Configurar credenciales en `app/config/Database.php`
4. Ajustar `BASE_URL` en `app/config/config.php`
5. Acceder desde: http://localhost/vetapp/public


---

## Estado del proyecto

🚧 En desarrollo  
Actualmente se está implementando el módulo de **autenticación y usuarios**.

---

## Autor

Proyecto desarrollado por **Christian Rodríguez**  
Como ejercicio de arquitectura MVC y buenas prácticas en PHP.
