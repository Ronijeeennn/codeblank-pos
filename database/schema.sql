-- Tindahan POS: schema + demo data
-- Import with phpMyAdmin (select your database first) or:
--   mysql -u root -p tindahan_pos < database/schema.sql
-- Same structure as app/Database/Migrations. Use either this file or the migrations, not both.
-- Demo logins: admin / admin12345  and  cashier / cashier123  (change them after first login)

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS sales, users, customers, products;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  stock_quantity INT NOT NULL DEFAULT 0,
  image VARCHAR(255),
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  phone VARCHAR(20),
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  full_name VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  avatar VARCHAR(255),
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  customer_id INT,
  sold_by INT NOT NULL,
  quantity INT NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (customer_id) REFERENCES customers(id),
  FOREIGN KEY (sold_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (username, full_name, password, created_at) VALUES
  ('admin', 'Store Admin', '$2y$10$OgpjtsJk5zXU/TrrZPxn0.b2LdCu53H0lUxjJwGaRLEcOTMLQTkxC', NOW()),
  ('cashier', 'Maria Santos', '$2y$10$8yIlYYmNYsYMTWMnR1ljfOcDLK6oi2WP5s7O34oUu/UFoMf6uAiry', NOW());

INSERT INTO products (name, price, stock_quantity, created_at) VALUES
  ('Brewed Coffee (12oz)', 85.00, 40, NOW()),
  ('Iced Latte', 120.00, 25, NOW()),
  ('Ensaymada', 65.00, 18, NOW()),
  ('Chicken Sandwich', 145.50, 12, NOW()),
  ('Bottled Water 500ml', 25.00, 100, NOW()),
  ('Chocolate Chip Cookie', 55.00, 3, NOW()),
  ('Tote Bag', 350.00, 8, NOW()),
  ('Reusable Tumbler', 499.00, 0, NOW());

INSERT INTO customers (full_name, email, phone, created_at) VALUES
  ('Juan Dela Cruz', 'juan@example.com', '0917 123 4567', NOW()),
  ('Ana Reyes', 'ana.reyes@example.com', '0928 765 4321', NOW()),
  ('Carlo Mendoza', 'carlo@example.com', NULL, NOW());
