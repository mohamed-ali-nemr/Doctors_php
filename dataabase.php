<?php
$servername = "localhost";
$username = "root";
$password = "gege";
$DB_DATABASE = "doctor_website";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $DB_DATABASE";
    $conn->exec($sql);
    $conn = null;

    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$DB_DATABASE;charset=utf8", $username, $password);

    // SQL to create tables
    $sql = "CREATE TABLE IF NOT EXISTS doctors (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(30) NOT NULL,
        email VARCHAR(30) NOT NULL,
        mobile VARCHAR(15) NOT NULL,
        department VARCHAR(30) NOT NULL,
        specialty VARCHAR(30) NOT NULL,
        photo TEXT,
        password VARCHAR(255) NOT NULL,
        confirm_password VARCHAR(255) NOT NULL
    );";
    $conn->exec($sql);

    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        department ENUM('patient', 'doctor') NOT NULL,
        phone VARCHAR(15) NOT NULL,
        photo TEXT,
        doctor INT(6) UNSIGNED,
        password VARCHAR(255) NOT NULL,
        confirm_password VARCHAR(255) NOT NULL,
        FOREIGN KEY (doctor) REFERENCES doctors(id) ON DELETE SET NULL
    );";
    $conn->exec($sql);

    $sql = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT,
        sender_department ENUM('patient', 'doctor') NOT NULL,
        receiver_id INT,
        receiver_department ENUM('patient', 'doctor') NOT NULL,
        message TEXT,
        FOREIGN KEY (sender_id) REFERENCES users(id),
        FOREIGN KEY (receiver_id) REFERENCES users(id)
    );";
    $conn->exec($sql);

    echo "Tables created successfully";
} catch(PDOException $e) {
    echo "Error: " . "<br>" . $e->getMessage();
}

$conn = null;
?>
