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
