CREATE TABLE IF NOT EXISTS service_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0,
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS service_catalog (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  service_name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estimated_duration INT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_catalog_category FOREIGN KEY (category_id) REFERENCES service_categories(id)
);

CREATE TABLE IF NOT EXISTS reservation_services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reservation_id INT NOT NULL,
  service_id INT UNSIGNED NOT NULL,
  service_name_snapshot VARCHAR(150) NOT NULL,
  price_snapshot DECIMAL(10,2) NOT NULL,
  duration_snapshot INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_res_service (reservation_id, service_id),
  CONSTRAINT fk_rs_reservation FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
  CONSTRAINT fk_rs_service FOREIGN KEY (service_id) REFERENCES service_catalog(id)
);

INSERT IGNORE INTO service_categories (id,category_name,slug,sort_order,status) VALUES
(1,'PMS Package','pms-package',1,'Active'),(2,'Periodic Services','periodic-services',2,'Active'),(3,'AC Services & Repair','ac-services-repair',3,'Active'),(4,'Brake Services','brake-services',4,'Active'),(5,'Tire Services','tire-services',5,'Active'),(6,'Engine Services','engine-services',6,'Active'),(7,'Battery Services','battery-services',7,'Active');

INSERT IGNORE INTO service_catalog (id,category_id,service_name,description,price,estimated_duration,sort_order,status) VALUES
(1,1,'PMS Basic','Basic preventive maintenance package including standard vehicle inspection.',1500,90,1,'Active'),
(2,1,'PMS Standard','Preventive maintenance package with additional fluid and safety checks.',2500,120,2,'Active'),
(3,1,'PMS Premium','Comprehensive preventive maintenance package for complete vehicle care.',4500,180,3,'Active'),
(4,2,'Regular Oil Change','Regular engine oil replacement with oil filter inspection.',1200,60,1,'Active'),
(5,2,'Semi-Synthetic Oil Change','Semi-synthetic oil service suitable for regular driving conditions.',1800,60,2,'Active'),
(6,2,'Fully Synthetic Oil Change','Premium fully synthetic engine oil replacement.',2600,60,3,'Active'),
(7,3,'Air Conditioning Check','Inspection of the air conditioning system and cooling performance.',800,45,1,'Active'),
(8,3,'AC Cleaning','Air conditioning cleaning service.',1500,90,2,'Active'),
(9,4,'Brake Inspection','Inspection of brake pads, rotors and braking performance.',500,45,1,'Active'),
(10,4,'Brake Cleaning and Adjustment','Brake cleaning and adjustment service.',1200,90,2,'Active'),
(11,5,'Tire Rotation','Rotate tires for more even tread wear.',700,45,1,'Active'),
(12,5,'Tire Inspection','Inspection of tire condition, pressure and tread.',350,30,2,'Active'),
(13,6,'Engine Diagnostic','Basic engine diagnostic inspection.',1500,90,1,'Active'),
(14,6,'Engine Tune-Up','General engine tune-up service.',2200,120,2,'Active'),
(15,7,'Battery Check','Battery health and charging system inspection.',400,30,1,'Active'),
(16,7,'Battery Replacement Service','Battery removal, replacement and charging-system check.',800,45,2,'Active');
