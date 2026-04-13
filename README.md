VetApp 🐾 - Professional Veterinary Management System

VetApp is a robust backend-focused system built with Vanilla PHP 8.1+, designed with a strong emphasis on Software Engineering principles. It leverages an MVC + Repository + Middleware architecture to ensure scalability, security, and clean code.
🚀 Key Architectural Features

Unlike traditional "spaghetti" PHP projects, VetApp follows modern standards used in frameworks like Laravel or Spring Boot:

    Repository Pattern: Decouples business logic from data access, making the system database-agnostic.

    Custom Middleware Pipeline: Handles Authentication and RBAC (Role-Based Access Control) before requests reach the controllers.

    Domain Entities: Uses rich models with strict typing and business rule validation.

    Centralized Bootstrap & Autoloading: Implements a custom PSR-4 inspired autoloader to manage dependencies efficiently.

    Singleton Pattern: Optimized database connection management.

    CSRF Protection & Input Sanitization: Built-in security helpers to prevent XSS and CSRF attacks.

🛠️ Tech Stack & Security

    Backend: PHP 8.1+ (Strict types enabled)

    Database: MySQL / MariaDB (PDO with Prepared Statements)

    Frontend: HTML5, CSS3, Bootstrap 5, SweetAlert2, Chart.js

    Security:

        One-way password hashing using bcrypt.

        XSS Protection via output escaping.

        CSRF tokens on all forms.

        Input sanitization and type casting.

        Session regeneration to prevent fixation.

        Protected /app directory (Logic is not exposed to the web).

📁 Project Structure
text

vetapp/
├── app/                     # Core Application Logic
│   ├── config/              # System & DB Configuration
│   ├── controllers/         # Request Handling (Auth, Clients, Pets, Consultations, Medications, Sales, Services, Users)
│   ├── middleware/          # Auth & Role Guards
│   ├── models/              # Domain Entities (Business Rules)
│   ├── repositories/        # Data Access Layer (SQL)
│   ├── helpers/             # Global Utility Functions (auth, csrf, sanitize, redirect, alert)
│   └── views/               # UI Templates (HTML/PHP)
├── public/                  # Entry Point (Front Controller & Assets)
├── database/                # SQL Migrations/Scripts
└── storage/                 # Logs & File Uploads

🔧 Installation & Setup

    Clone the repository
    git clone https://github.com/zyjec1983/vetapp

    Database
    Import database/vetapp.sql into your MySQL server.

    Configuration

        Update credentials in app/config/Database.php (or define constants in app/config/config.php).

        Set BASE_URL in app/config/config.php (e.g., http://localhost/vetapp/public/).

    Run
    Point your web server to the public/ folder.

✅ Current Features (Sprints 1–9)
Module	Features
Authentication	Login, logout, session management, role-based access (admin, veterinarian, pharmacy).
Users	CRUD, soft delete (activate/deactivate), role assignment, SweetAlert2 confirmations.
Clients	CRUD, soft delete, search by name/identification, linked to pets.
Pets	CRUD, image upload, client selection, soft delete, medical record view.
Consultations	CRUD, service selection (predefined prices), weight, temperature, diagnosis, treatment, next visit, observations, reminders.
Medications	CRUD with batch management (FIFO), stock calculation, IVA toggle (taxable), low-stock alerts, product deactivation/reactivation.
Services	CRUD for predefined veterinary services (consultation, grooming, etc.), used in consultations.
Sales	Shopping cart, client/pet search, discount, IVA exemption toggle, payment methods (cash, card, transfer, credit). PDF generation, WhatsApp sharing, cancellation with SweetAlert2.
Dashboard	Summary cards (clients, pets, today consultations, today sales), today reminders (with WhatsApp), low stock alerts, annual sales chart.
Security	CSRF tokens, input sanitization, session regeneration, password hashing, prepared statements.
📈 Roadmap

    Core Architecture & Autoloading

    Authentication System (Login/Logout)

    Role-Based Access Control (Admin/Vet/Pharmacy)

    Clients & Pets Management

    Medical Consultations with Reminders

    Medications Inventory (Batches, FIFO, Stock)

    Sales Module (PDF, WhatsApp, Exempt IVA)

    Services Management

    Security Enhancements (CSRF, Sanitization)

    Electronic Invoicing (SRI Ecuador) – pending study

    Reports (Sales, Consultations, Inventory)

    REST API for mobile app (future)

👨‍💻 Author

Christian Rodríguez
Software Developer focused on Clean Code and Backend Architecture.
Project developed as a practical exercise in MVC, Repository Pattern, and security best practices using vanilla PHP.
🇪🇸 Versión en Español
VetApp 🐾 - Sistema de Gestión Veterinaria

VetApp es un sistema robusto enfocado en backend, construido con PHP 8.1+ puro, siguiendo una arquitectura MVC + Repository + Middleware para garantizar escalabilidad, seguridad y código limpio.
🚀 Características Arquitectónicas Clave

    Patrón Repository: Separa la lógica de negocio del acceso a datos.

    Middleware personalizado: Autenticación y control de acceso por roles (RBAC).

    Entidades de dominio: Modelos con tipado estricto y validación.

    Autoloading y Bootstrap centralizados: Carga automática de clases PSR-4.

    Singleton para la conexión a BD.

    Protección CSRF y sanitización de entradas.

🛠️ Tecnologías y Seguridad

    Backend: PHP 8.1+

    Base de datos: MySQL / MariaDB (PDO con prepared statements)

    Frontend: HTML5, CSS3, Bootstrap 5, SweetAlert2, Chart.js

    Seguridad: Hash de contraseñas con bcrypt, escape de salidas (XSS), tokens CSRF, sanitización, regeneración de sesión.

📁 Estructura del Proyecto

(Ver estructura en inglés arriba)
🔧 Instalación

    Clonar repositorio.

    Importar database/vetapp.sql en MySQL.

    Configurar credenciales y BASE_URL en app/config/config.php.

    Apuntar servidor web a la carpeta public/.

✅ Funcionalidades actuales (Sprints 1–9)
Módulo	Funcionalidades
Autenticación	Login, logout, gestión de sesiones, roles (admin, veterinario, farmacia).
Usuarios	CRUD, soft delete, asignación de roles, confirmaciones SweetAlert2.
Clientes	CRUD, soft delete, búsqueda por nombre/identificación.
Mascotas	CRUD, subida de imágenes, selección de dueño, ficha médica.
Consultas	CRUD, selector de servicios (precios predefinidos), peso, temperatura, diagnóstico, tratamiento, próxima visita, recordatorios.
Medicamentos	CRUD con lotes (FIFO), cálculo de stock, toggle de IVA, alertas de stock bajo, desactivación/reactivación.
Servicios	CRUD de servicios veterinarios (consulta, peluquería, etc.), usados en consultas.
Ventas	Carrito, búsqueda de cliente/mascota, descuento, exención de IVA, métodos de pago, PDF, WhatsApp, cancelación con SweetAlert2.
Dashboard	Tarjetas resumen, recordatorios del día, alertas de stock, gráfico de ventas anual.
Seguridad	CSRF, sanitización, regeneración de sesión, hashing de contraseñas.
📈 Hoja de ruta

    Arquitectura y autoloading

    Autenticación y roles

    Clientes y mascotas

    Consultas médicas con recordatorios

    Inventario de medicamentos (lotes, FIFO)

    Módulo de ventas (PDF, WhatsApp, exención IVA)

    Gestión de servicios

    Mejoras de seguridad (CSRF, sanitización)

    Facturación electrónica (SRI Ecuador) – pendiente de estudio

    Reportes (ventas, consultas, inventario)

    API REST para app móvil (futuro)

👨‍💻 Autor

Christian Rodríguez
Desarrollador de software enfocado en código limpio y arquitectura backend.
Proyecto desarrollado como ejercicio práctico de MVC, Repository Pattern y buenas prácticas de seguridad en PHP puro.