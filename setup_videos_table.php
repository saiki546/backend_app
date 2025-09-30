<?php
require_once 'config/database.php';

try {
    // Create videos table
    $sql = "CREATE TABLE IF NOT EXISTS videos (
        id VARCHAR(255) PRIMARY KEY,
        user_id VARCHAR(255) NOT NULL,
        title VARCHAR(255) NOT NULL,
        duration VARCHAR(50) DEFAULT '00:00:00',
        file_path VARCHAR(500) NOT NULL,
        status ENUM('uploaded', 'processing', 'completed', 'failed') DEFAULT 'uploaded',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Videos table created successfully!\n";
    
    // Create captions table
    $sql = "CREATE TABLE IF NOT EXISTS captions (
        id VARCHAR(255) PRIMARY KEY,
        video_id VARCHAR(255) NOT NULL,
        text TEXT NOT NULL,
        start_time FLOAT NOT NULL,
        end_time FLOAT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "Captions table created successfully!\n";
    
    echo "Database setup completed!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
