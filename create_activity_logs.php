<?php

try {
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        action VARCHAR(255) NOT NULL,
        model_type VARCHAR(255),
        model_id INTEGER,
        old_values TEXT,
        new_values TEXT,
        ip_address VARCHAR(255),
        user_agent TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";

    $pdo->exec($sql);

    // Create indexes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_activity_logs_user_created ON activity_logs(user_id, created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_activity_logs_model ON activity_logs(model_type, model_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_activity_logs_action ON activity_logs(action)");

    echo "Activity logs table created successfully!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}