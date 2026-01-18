DROP DATABASE vetapp;
-- =========================================
-- DATABASE CREATION
-- =========================================
CREATE DATABASE IF NOT EXISTS vetapp
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE vetapp;

-- =========================================
-- USERS (EMPLOYEES)
-- =========================================
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,

    name VARCHAR(100) NOT NULL,
    middlename VARCHAR(100),
    lastname1 VARCHAR(100) NOT NULL,
    lastname2 VARCHAR(100),

    phone VARCHAR(20),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- ROELS
-- =========================================
 
CREATE TABLE roles (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(100)
);

-- =========================================
-- USER_ROEL (INTERMEDIARY TABLE)
-- =========================================
CREATE TABLE user_roles (
    id_user INT NOT NULL,
    id_role INT NOT NULL,
    PRIMARY KEY (id_user, id_role),
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_role) REFERENCES roles(id_role) ON DELETE CASCADE
);

-- =========================================
-- POPULATE TABLE
-- =========================================
INSERT INTO roles (name, description) VALUES
('admin','Administrador del sistema'),
('veterinarian','Veterinario'),
('pharmacy','Farmacia');

-- =========================================
-- CLIENTS (PET OWNERS)
-- =========================================
CREATE TABLE clients (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address TEXT,
    identification VARCHAR(20),
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- PETS
-- =========================================
CREATE TABLE pets (
    id_pet INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    species VARCHAR(50) NOT NULL,
    breed VARCHAR(50),
    sex ENUM('M','F','Unknown'),
    date_of_birth DATE,
    current_weight DECIMAL(5,2),
    color VARCHAR(50),
    microchip VARCHAR(50),
    allergies TEXT,
    observations TEXT,
    picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) 
        REFERENCES clients(id_client) 
        ON DELETE CASCADE
);

-- =========================================
-- MEDICATIONS / PRODUCTS
-- =========================================
CREATE TABLE medications (
    id_medication INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    description TEXT,
    stock INT DEFAULT 0,
    minimum_stock INT DEFAULT 5,
    purchase_price DECIMAL(10,2),
    sale_price DECIMAL(10,2),
    supplier VARCHAR(100),
    expiration_date DATE,
    location VARCHAR(50),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- MEDICAL CONSULTATIONS
-- =========================================
CREATE TABLE consultations (
    id_consultation INT AUTO_INCREMENT PRIMARY KEY,
    id_pet INT NOT NULL,
    id_user INT NOT NULL,
    consultation_date DATETIME NOT NULL,
    weight DECIMAL(5,2),
    temperature DECIMAL(4,1),
    diagnosis TEXT,
    treatment TEXT,
    next_visit DATE,
    consultation_fee DECIMAL(10,2),
    status ENUM('pending','completed','cancelled') DEFAULT 'completed',
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pet) 
        REFERENCES pets(id_pet) 
        ON DELETE CASCADE,
    FOREIGN KEY (id_user) 
        REFERENCES users(id_user)
);

-- =========================================
-- PRESCRIBED MEDICATIONS
-- =========================================
CREATE TABLE consultation_medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_consultation INT NOT NULL,
    id_medication INT NOT NULL,
    dosage VARCHAR(50),
    frequency VARCHAR(50),
    days INT,
    FOREIGN KEY (id_consultation) 
        REFERENCES consultations(id_consultation) 
        ON DELETE CASCADE,
    FOREIGN KEY (id_medication) 
        REFERENCES medications(id_medication)
);

-- =========================================
-- VACCINES
-- =========================================
CREATE TABLE vaccines (
    id_vaccine INT AUTO_INCREMENT PRIMARY KEY,
    id_pet INT NOT NULL,
    vaccine_name VARCHAR(100) NOT NULL,
    application_date DATE NOT NULL,
    next_application DATE,
    batch VARCHAR(50),
    applied_by INT,
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pet) 
        REFERENCES pets(id_pet) 
        ON DELETE CASCADE,
    FOREIGN KEY (applied_by) 
        REFERENCES users(id_user)
);

-- =========================================
-- SALES
-- =========================================
CREATE TABLE sales (
    id_sale INT AUTO_INCREMENT PRIMARY KEY,
    sale_code VARCHAR(20) UNIQUE NOT NULL,
    id_client INT,
    id_user INT NOT NULL,
    sale_date DATETIME NOT NULL,
    subtotal DECIMAL(10,2),
    discount DECIMAL(10,2),
    total DECIMAL(10,2),
    payment_method ENUM('cash','card','transfer','credit'),
    status ENUM('pending','paid','cancelled') DEFAULT 'pending',
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) 
        REFERENCES clients(id_client),
    FOREIGN KEY (id_user) 
        REFERENCES users(id_user)
);

-- =========================================
-- SALE DETAILS
-- =========================================
CREATE TABLE sale_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_sale INT NOT NULL,
    id_medication INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2),
    FOREIGN KEY (id_sale) 
        REFERENCES sales(id_sale) 
        ON DELETE CASCADE,
    FOREIGN KEY (id_medication) 
        REFERENCES medications(id_medication)
);

-- =========================================
-- INVENTORY MOVEMENTS
-- =========================================
CREATE TABLE inventory_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_medication INT NOT NULL,
    movement_type ENUM('in','out','adjustment'),
    quantity INT NOT NULL,
    previous_stock INT,
    new_stock INT,
    reason VARCHAR(100),
    reference_id INT,
    id_user INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_medication) 
        REFERENCES medications(id_medication),
    FOREIGN KEY (id_user) 
        REFERENCES users(id_user)
);

-- =========================================
-- REMINDERS
-- =========================================
CREATE TABLE reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reminder_type ENUM('vaccine','consultation','payment'),
    id_pet INT,
    id_client INT,
    reminder_date DATE NOT NULL,
    message TEXT,
    sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pet) 
        REFERENCES pets(id_pet),
    FOREIGN KEY (id_client) 
        REFERENCES clients(id_client)
);

-- =========================================
-- INDEXES
-- =========================================
CREATE INDEX idx_pet_client ON pets(id_client);
CREATE INDEX idx_consultation_date ON consultations(consultation_date);
CREATE INDEX idx_sale_date ON sales(sale_date);
CREATE INDEX idx_inventory_medication ON inventory_movements(id_medication);

