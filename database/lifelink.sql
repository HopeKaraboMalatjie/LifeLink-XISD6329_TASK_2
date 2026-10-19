-- =========================================================
-- LifeLink Database Schema & Full Seed Data
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
-- Matches RegisterActivity.kt's bloodTypes list (index+1 = blood_type_id)
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
  google_id VARCHAR(255) NULL,
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
-- SEED DATA (10+ Records per table)
-- =========================================================

-- 1. Admins (10 records)
INSERT INTO admins (name, email, password) VALUES
  ('System Administrator', 'admin@lifelink.co.za', '$2b$10$yDbksuM0UvvDZJTB43bFDu7V68MydqGGxGdZtvgMq10zAgYWv4bQG'),
  ('Dr. Sarah Connor', 'sarah.connor@lifelink.co.za', '$2b$10$yDbksuM0UvvDZJTB43bFDu7V68MydqGGxGdZtvgMq10zAgYWv4bQG'),
  ('Sibusiso Nkosi', 'sibusiso.nkosi@lifelink.co.za', '$2b$10$yDbksuM0UvvDZJTB43bFDu7V68MydqGGxGdZtvgMq10zAgYWv4bQG'),
  ('Maria Garcia', 'maria.garcia@lifelink.co.za', '$2b$10$yDbksuM0UvvDZJTB43bFDu7V68MydqGGxGdZtvgMq10zAgYWv4bQG'),
  ('David Botha', 'david.botha@lifelink.co.za', '$2b$10$yDbksuM0UvvDZJTB43bFDu7V68MydqGGxGdZtvgMq10zAgYWv4bQG'),
  ('Nomvula Mthembu', 'nomvula.mthembu@lifelink.co.za', '$2b$10$yDbksuM0UvvDZJTB43bFDu7V68MydqGGxGdZtvgMq10zAgYWv4bQG'),
  ('John Smith', 'john.smith@lifelink.co.za', '$2b$10$yDbksuM0UvvDZJTB43bFDu7V68MydqGGxGdZtvgMq10zAgYWv4bQG'),
  ('Amina Patel', 'amina.patel@lifelink.co.za', '$2b$10$yDbksuM0UvvDZJTB43bFDu7V68MydqGGxGdZtvgMq10zAgYWv4bQG'),
  ('Lindiwe Khumalo', 'lindiwe.khumalo@lifelink.co.za', '$2b$10$yDbksuM0UvvDZJTB43bFDu7V68MydqGGxGdZtvgMq10zAgYWv4bQG'),
  ('Kevin van der Merwe', 'kevin.vdm@lifelink.co.za', '$2b$10$yDbksuM0UvvDZJTB43bFDu7V68MydqGGxGdZtvgMq10zAgYWv4bQG');

-- 2. Donors (10 records)
INSERT INTO donors (first_name, last_name, email, password, google_id, phone, date_of_birth, gender, address, blood_type_id, last_donation_date, is_active) VALUES
  ('Thabo', 'Molefe', 'donor@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', NULL, '0821234567', '1996-04-12', 'Male', '12 Church St, Pretoria', 2, '2026-01-15', 1),
  ('Lerato', 'Dube', 'lerato@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', NULL, '0839876543', '1999-11-02', 'Female', '45 Riviera Rd, Killarney', 5, NULL, 1),
  ('Michael', 'Smit', 'michael.smit@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', 'google_sso_101', '0845551234', '1992-08-23', 'Male', '88 Main Rd, Sandton', 1, '2025-11-10', 1),
  ('Zanele', 'Zuma', 'zanele.zuma@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', NULL, '0712223344', '1995-02-14', 'Female', '12 Oxford Rd, Rosebank', 3, '2025-12-01', 1),
  ('Peter', 'Parker', 'peter.parker@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', 'google_sso_102', '0729998877', '2000-06-18', 'Male', '55 Jan Smuts Ave, Randburg', 4, NULL, 1),
  ('Nandi', 'Mokoena', 'nandi.mokoena@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', NULL, '0813334455', '1998-09-30', 'Female', '101 Lynnwood Rd, Hatfield', 6, '2026-02-01', 1),
  ('Kagiso', 'Rabada', 'kagiso.r@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', NULL, '0734445566', '1994-05-25', 'Male', '14 Rivonia Rd, Morningside', 7, NULL, 1),
  ('Samantha', 'Brown', 'samantha.b@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', NULL, '0826667788', '1991-12-05', 'Female', '77 Beyers Naude Dr, Fairland', 8, '2025-10-18', 1),
  ('Bongani', 'Mahlangu', 'bongani.m@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', NULL, '0798889900', '1989-07-11', 'Male', '33 Paul Kruger St, Pretoria', 2, '2026-01-20', 1),
  ('Anchen', 'De Villiers', 'anchen.dv@lifelink.co.za', '$2b$10$sWgMqEaZocEC0J2pEJZO9.93iMERdRu.IfFcbvG.LwvCFpPqjbXtu', NULL, '0831112233', '1997-03-28', 'Female', '90 Menlyn Dr, Menlyn', 1, '2025-09-05', 1);

-- 3. Hospitals (10 records)
INSERT INTO hospitals (hospital_name, address, latitude, longitude, contact_email, contact_phone, operating_hours, is_approved) VALUES
  ('Pretoria Central Hospital', '123 Church St, Pretoria', -25.746111, 28.188056, 'blood@pretoriacentral.co.za', '012 345 6789', '07:00 - 17:00', 1),
  ('Sunninghill Community Hall', '45 Nichol Way, Sandton', -26.058800, 28.077800, 'donations@sunninghill.co.za', '011 234 5678', '08:00 - 16:00', 1),
  ('Steve Biko Academic Hospital', 'Steve Biko Rd, Pretoria', -25.739400, 28.191700, 'blood@sbah.co.za', '012 354 1000', '24 hours', 1),
  ('Charlotte Maxeke Academic Hospital', 'Jubilee Rd, Parktown, Johannesburg', -26.175800, 28.043600, 'blood@cmah.gov.za', '011 488 4911', '24 hours', 1),
  ('Chris Hani Baragwanath Hospital', 'Chris Hani Rd, Soweto', -26.260800, 27.942500, 'blood@chbh.gov.za', '011 938 8000', '24 hours', 1),
  ('Netcare Rosebank Hospital', '14 Sturdee Ave, Rosebank', -26.145000, 28.041000, 'blood@netcarerosebank.co.za', '011 328 0500', '08:00 - 18:00', 1),
  ('Life Wilgeheuwel Hospital', 'Amplifier Rd, Radiokop, Roodepoort', -26.108200, 27.915000, 'blood@wilgeheuwel.co.za', '011 794 2000', '07:30 - 17:00', 1),
  ('Mediclinic Morningside', 'Hill Rd, Morningside, Sandton', -26.086000, 28.058000, 'blood@mediclinic.co.za', '011 282 5000', '08:00 - 17:00', 1),
  ('Life Groenkloof Hospital', 'George Storrar Dr, Groenkloof, Pretoria', -25.772000, 28.210000, 'blood@lifegroenkloof.co.za', '012 460 4700', '08:00 - 16:30', 1),
  ('Midrand Medical Centre', 'Old Pretoria Rd, Midrand', -25.996000, 28.127000, 'donations@midrandmedical.co.za', '011 315 1200', '08:00 - 16:00', 1);

-- 4. Blood Inventory (15+ records)
INSERT INTO blood_inventory (hospital_id, blood_type_id, units_available, units_reserved, min_threshold) VALUES
  (1, 2, 42, 4, 15), (1, 1, 18, 2, 15), (1, 5, 30, 3, 15), (1, 3, 12, 1, 15),
  (2, 2, 25, 2, 15), (2, 6, 9,  1, 15), (2, 7, 14, 1, 15),
  (3, 2, 55, 5, 15), (3, 1, 20, 2, 15), (3, 5, 38, 3, 15), (3, 6, 16, 1, 15),
  (3, 3, 22, 2, 15), (3, 4, 11, 1, 15), (3, 7, 19, 1, 15), (3, 8, 6,  0, 15),
  (4, 1, 8, 3, 15),  (4, 2, 35, 2, 15), (5, 1, 5, 2, 15),   (5, 5, 40, 5, 15),
  (6, 3, 18, 2, 15), (7, 4, 12, 1, 15), (8, 2, 28, 3, 15),  (9, 1, 10, 1, 15), (10, 5, 22, 2, 15);

-- 5. Appointments (10 records)
INSERT INTO appointments (donor_id, hospital_id, scheduled_date, scheduled_time, donation_type, status, notes) VALUES
  (1, 1, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '10:00:00', 'WholeBlood', 'Confirmed', 'First appointment via app'),
  (1, 3, '2026-01-15', '09:30:00', 'WholeBlood', 'Completed', 'Routine donation'),
  (2, 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', 'Plasma', 'Confirmed', 'Plasma donation drive'),
  (3, 4, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '14:00:00', 'WholeBlood', 'Pending', 'Emergency call responder'),
  (4, 5, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '08:30:00', 'WholeBlood', 'Confirmed', 'Regular donor appointment'),
  (5, 6, '2026-02-10', '12:00:00', 'Platelets', 'Completed', 'Platelet donation'),
  (6, 7, DATE_ADD(CURDATE(), INTERVAL 12 DAY), '15:30:00', 'WholeBlood', 'Confirmed', 'Weekend community drive'),
  (7, 8, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:00:00', 'WholeBlood', 'Confirmed', 'First time donor'),
  (8, 9, '2026-01-28', '10:30:00', 'WholeBlood', 'Cancelled', 'Rescheduled due to flu'),
  (9, 10, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', 'WholeBlood', 'Confirmed', 'Corporate blood drive');

-- 6. Emergency Alerts (10 records)
INSERT INTO alerts (hospital_id, blood_type_id, units_needed, urgency_level, status) VALUES
  (2, 1, 4, 'Critical', 'Active'),
  (3, 8, 6, 'High', 'Active'),
  (1, 6, 3, 'Medium', 'Active'),
  (4, 1, 10, 'Critical', 'Active'),
  (5, 4, 5, 'High', 'Active'),
  (6, 2, 8, 'Medium', 'Active'),
  (7, 7, 2, 'Low', 'Active'),
  (8, 1, 7, 'Critical', 'Active'),
  (9, 6, 4, 'High', 'Active'),
  (10, 3, 5, 'Medium', 'Active');

-- 7. Notifications (10 records)
INSERT INTO notifications (donor_id, type, message, is_read) VALUES
  (1, 'Appointment', 'Your appointment at Pretoria Central Hospital has been confirmed.', 0),
  (1, 'Reminder', 'You are eligible to donate again - book your next appointment!', 0),
  (2, 'Alert', 'CRITICAL: O- blood needed at Charlotte Maxeke Hospital.', 0),
  (3, 'Welcome', 'Welcome to LifeLink! Thank you for joining as a life saver.', 1),
  (4, 'Appointment', 'Your appointment at Chris Hani Baragwanath Hospital is tomorrow.', 0),
  (5, 'ThankYou', 'Thank you for your recent platelet donation at Rosebank Hospital!', 1),
  (6, 'Reminder', 'Donation Drive coming to Hatfield this weekend!', 0),
  (7, 'Appointment', 'Appointment confirmed at Mediclinic Morningside.', 0),
  (8, 'Alert', 'Urgent call for A- blood donors in your area.', 0),
  (9, 'General', 'Keep hydrated before your upcoming donation session!', 0);
