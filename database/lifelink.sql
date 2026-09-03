-- =========================================================
-- LifeLink Database Schema
-- XISD6329 — Ayabonga Hadebe, Gutshwa Magagula,
--            Karabo Mojapelo, Hope Malatjie
--
-- Import via phpMyAdmin, or:
--   mysql -u root -p < database/lifelink.sql
-- =========================================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS lifelink_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lifelink_db;

-- ---------------------------------------------------------
-- blood_types — lookup table.
-- IMPORTANT: this order must match RegisterActivity.kt's
-- `bloodTypes` list (index+1 = blood_type_id) so the Android
-- app's registration spinner lines up with the database.
-- ---------------------------------------------------------
DROP TABLE IF EXISTS blood_types;
CREATE TABLE blood_types (
  blood_type_id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(5) NOT NULL UNIQUE
);
INSERT INTO blood_types (blood_type_id, code) VALUES
  (1, 'O-'), (2, 'O+'), (3, 'B+'), (4, 'B-'),
  (5, 'A+'), (6, 'A-'), (7, 'AB+'), (8, 'AB-');

-- ---------------------------------------------------------
-- donors
-- ---------------------------------------------------------
DROP TABLE IF EXISTS donors;
CREATE TABLE donors (
  donor_id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(60) NOT NULL,
  last_name VARCHAR(60) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(30),
  date_of_birth DATE,
  gender VARCHAR(20),
  address VARCHAR(255),
  blood_type_id INT NOT NULL,
  last_donation_date DATE NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (blood_type_id) REFERENCES blood_types(blood_type_id)
);

-- ---------------------------------------------------------
-- hospitals
-- ---------------------------------------------------------
DROP TABLE IF EXISTS hospitals;
CREATE TABLE hospitals (
  hospital_id INT AUTO_INCREMENT PRIMARY KEY,
  hospital_name VARCHAR(150) NOT NULL,
  address VARCHAR(255) NOT NULL,
  latitude DECIMAL(10,6),
  longitude DECIMAL(10,6),
  contact_email VARCHAR(150),
  contact_phone VARCHAR(30),
  operating_hours VARCHAR(100),
  is_approved TINYINT(1) NOT NULL DEFAULT 1
);

-- ---------------------------------------------------------
-- blood_inventory — units on hand per hospital, per type
-- ---------------------------------------------------------
DROP TABLE IF EXISTS blood_inventory;
CREATE TABLE blood_inventory (
  inventory_id INT AUTO_INCREMENT PRIMARY KEY,
  hospital_id INT NOT NULL,
  blood_type_id INT NOT NULL,
  units_available INT NOT NULL DEFAULT 0,
  units_reserved INT NOT NULL DEFAULT 0,
  min_threshold INT NOT NULL DEFAULT 10,
  UNIQUE KEY uniq_hospital_type (hospital_id, blood_type_id),
  FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE CASCADE,
  FOREIGN KEY (blood_type_id) REFERENCES blood_types(blood_type_id)
);

-- ---------------------------------------------------------
-- appointments
-- ---------------------------------------------------------
DROP TABLE IF EXISTS appointments;
CREATE TABLE appointments (
  appointment_id INT AUTO_INCREMENT PRIMARY KEY,
  donor_id INT NOT NULL,
  hospital_id INT NOT NULL,
  scheduled_date DATE NOT NULL,
  scheduled_time TIME NOT NULL,
  donation_type VARCHAR(20) NOT NULL DEFAULT 'WholeBlood',
  status VARCHAR(20) NOT NULL DEFAULT 'Confirmed', -- Confirmed | Pending | Completed | Cancelled
  notes VARCHAR(255),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE CASCADE,
  FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id)
);

-- ---------------------------------------------------------
-- alerts — emergency blood requests raised by hospitals
-- ---------------------------------------------------------
DROP TABLE IF EXISTS alerts;
CREATE TABLE alerts (
  alert_id INT AUTO_INCREMENT PRIMARY KEY,
  hospital_id INT NOT NULL,
  blood_type_id INT NOT NULL,
  units_needed INT NOT NULL,
  urgency_level VARCHAR(20) NOT NULL DEFAULT 'Medium', -- Low | Medium | High | Critical
  status VARCHAR(20) NOT NULL DEFAULT 'Active', -- Active | Resolved
  alerted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id),
  FOREIGN KEY (blood_type_id) REFERENCES blood_types(blood_type_id)
);

-- ---------------------------------------------------------
-- notifications
-- ---------------------------------------------------------
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  donor_id INT NOT NULL,
  type VARCHAR(30) NOT NULL DEFAULT 'General',
  message VARCHAR(255) NOT NULL,
  sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- admins — web admin portal users
-- ---------------------------------------------------------
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

-- =========================================================
-- SEED DATA
-- =========================================================

-- Admin login: admin@lifelink.co.za / Admin@123
INSERT INTO admins (name, email, password) VALUES
  ('System Administrator', 'admin@lifelink.co.za', '$2b$10$yDbksuM0UvvDZJTB43bFDu7V68MydqGGxGdZtvgMq10zAgYWv4bQG');

-- Demo donor login: donor@lifelink.co.za / Donor@123
INSERT INTO donors (first_name, last_name, email, password, phone, date_of_birth, gender, address, blood_type_id, last_donation_date, is_active) VALUES
  ('Thabo', 'Molefe', 'donor@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', '0821234567', '1996-04-12', 'Male', '12 Church St, Pretoria', 2, '2026-07-01', 1),
  ('Lerato', 'Dube', 'lerato@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', '0839876543', '1999-11-02', 'Female', '45 Nichol Way, Sandton', 5, NULL, 1);

INSERT INTO hospitals (hospital_name, address, latitude, longitude, contact_email, contact_phone, operating_hours, is_approved) VALUES
  ('Pretoria Central Hospital', '123 Church St, Pretoria', -25.746111, 28.188056, 'blood@pretoriacentral.co.za', '012 345 6789', '07:00 - 17:00', 1),
  ('Sunninghill Community Hall', '45 Nichol Way, Sandton', -26.058800, 28.077800, 'donations@sunninghill.co.za', '011 234 5678', '08:00 - 16:00', 1),
  ('Steve Biko Academic Hospital', 'Steve Biko Rd, Pretoria', -25.739400, 28.191700, 'blood@sbah.co.za', '012 354 1000', '24 hours', 1);

-- Inventory (units_available per hospital/type)
INSERT INTO blood_inventory (hospital_id, blood_type_id, units_available, units_reserved, min_threshold) VALUES
  (1, 2, 42, 4, 15), (1, 1, 18, 2, 15), (1, 5, 30, 3, 15), (1, 3, 12, 1, 15),
  (2, 2, 25, 2, 15), (2, 6, 9,  1, 15), (2, 7, 14, 1, 15),
  (3, 2, 55, 5, 15), (3, 1, 20, 2, 15), (3, 5, 38, 3, 15), (3, 6, 16, 1, 15),
  (3, 3, 22, 2, 15), (3, 4, 11, 1, 15), (3, 7, 19, 1, 15), (3, 8, 6,  0, 15);

-- Emergency alerts
INSERT INTO alerts (hospital_id, blood_type_id, units_needed, urgency_level, status) VALUES
  (2, 1, 4, 'Critical', 'Active'),
  (3, 8, 6, 'High', 'Active'),
  (1, 6, 3, 'Medium', 'Active');

-- Sample appointments for the demo donor (donor_id = 1)
INSERT INTO appointments (donor_id, hospital_id, scheduled_date, scheduled_time, donation_type, status, notes) VALUES
  (1, 1, DATE_ADD(CURDATE(), INTERVAL 10 DAY), '10:00:00', 'WholeBlood', 'Confirmed', 'First appointment via app'),
  (1, 3, '2026-07-01', '09:30:00', 'WholeBlood', 'Completed', 'Routine donation');

-- Sample notifications
INSERT INTO notifications (donor_id, type, message, is_read) VALUES
  (1, 'Appointment', 'Your appointment at Pretoria Central Hospital has been confirmed.', 0),
  (1, 'Reminder', 'You are eligible to donate again - book your next appointment!', 0);
