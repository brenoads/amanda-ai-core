<?php
/**
 * AMANDA AI - CLI Rule Inserter Utility
 * 
 * This script provides a Headless/CLI interface to populate the local
 * MySQL knowledge base directly from the terminal, avoiding the need 
 * for external web interfaces (GUIs).
 */

// Default timezone configuration
date_default_timezone_set('America/Fortaleza');

// ==========================================
// SECURITY CHECK (CLI ENVIRONMENT ONLY)
// ==========================================
// Prevents the script from being executed via a web browser
if (php_sapi_name() !== 'cli') {
    die("Security Error: This script can only be executed via the command line interface (CLI).\n");
}

// ==========================================
// DATABASE CONFIGURATION
// ==========================================
$db_host = '127.0.0.1';
$db_name = 'ia_amanda';
$db_user = 'root';
$db_pass = 'code';

try {
    // Establish PDO Connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ==========================================
    // TERMINAL I/O (INPUT/OUTPUT)
    // ==========================================
    echo "\n============================================\n";
    echo "   Amanda AI: Add New Knowledge Rule\n";
    echo "============================================\n";
    
    echo "Enter Trigger Keyword (e.g., 'who are you'): ";
    $keyword = trim(fgets(STDIN));
    
    echo "Enter AI Response: ";
    $response = trim(fgets(STDIN));
    
    // Input validation
    if (empty($keyword) || empty($response)) {
        die("\n[!] Error: Both Keyword and Response fields are mandatory. Operation aborted.\n\n");
    }
    
    // ==========================================
    // DATABASE UPSERT LOGIC
    // ==========================================
    // Inserts the new rule. If the keyword already exists, updates the response.
    $stmt = $pdo->prepare("INSERT INTO knowledge_base (keyword, response) VALUES (:keyword, :response) ON DUPLICATE KEY UPDATE response = :response");
    
    $stmt->execute([
        'keyword' => mb_strtolower($keyword, 'UTF-8'),
        'response' => $response
    ]);
    
    echo "\n[✔] Success: Rule successfully registered in the local database.\n\n";

} catch (PDOException $e) {
    // Database connection or execution failure
    echo "\n[X] Database Error: " . $e->getMessage() . "\n\n";
}
?>