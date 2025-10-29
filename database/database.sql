    CREATE DATABASE IF NOT EXISTS ticketing_system;

USE ticketing_system;

-- 1. User Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('user', 'organizer', 'admin') DEFAULT 'user',
    profile_picture VARCHAR(512),
    email_alerts TINYINT(1) DEFAULT 0,
    sms_alerts TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Event Categories
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) UNIQUE NOT NULL, -- e.g., Music, Sports, Technology
    description TEXT
);

-- 3. Events
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    organizer_id INT,
    event_name VARCHAR(150) NOT NULL,
    description TEXT,
    venue VARCHAR(255),
    event_date DATE,
    event_time TIME,
    location_map_link VARCHAR(255),
    image_url VARCHAR(255),
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    rateing INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (organizer_id) REFERENCES users(user_id)
);

-- 4. Ticket Types (GA, VIP, Early Bird, etc.)
CREATE TABLE ticket_types (
    ticket_type_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    ticket_name VARCHAR(50),
    price DECIMAL(10,2),
    total_quantity INT,
    available_quantity INT,
    sale_start DATETIME,
    sale_end DATETIME,
    FOREIGN KEY (event_id) REFERENCES events(event_id)
);

-- 5. Orders
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2), -- sum of all ticket subtotals
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_method ENUM('card', 'bank_transfer') DEFAULT 'card',
    confirmation_code VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- 6. Order Details (Tickets Purchased)
CREATE TABLE order_details (
    order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    ticket_type_id INT,
    quantity INT,
    subtotal DECIMAL(10,2), -- price * quantity
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(ticket_type_id)
);

-- 7. Attendees (for check-in system)
CREATE TABLE attendees (
    attendee_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    event_id INT,
    attendee_name VARCHAR(100),
    email VARCHAR(100),
    qr_code VARCHAR(255), -- URL or base64 string
    check_in_status BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id)
);


-- 8. Contact Messages
CREATE TABLE contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    subject VARCHAR(150),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- 10. forget password table 
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reset_token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 11. feedback table
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    subject VARCHAR(150),
    message TEXT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
