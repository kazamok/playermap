<?php
// Start output buffering to catch any stray output/errors
ob_start();

// This function will handle the final JSON output
function send_json_response($data) {
    // Clean (erase) the output buffer and turn off output buffering
    ob_end_clean();
    // Set the correct header
    header('Content-Type: application/json');
    // Echo the JSON encoded data
    echo json_encode($data);
    // Terminate the script
    exit;
}

// Set a basic error handler to prevent HTML errors from being outputted
set_error_handler(function($severity, $message, $file, $line) {
    // In a production environment, you would log this error.
    // For now, we just want to stop the script from outputting HTML.
    send_json_response(['error' => 'A server error occurred during data retrieval.']);
});

try {
    // Define the absolute path to the loader.php file based on the document root.
    // This is more reliable than using relative paths.
    $loader_path = $_SERVER['DOCUMENT_ROOT'] . '/application/loader.php';
    
    if (file_exists($loader_path)) {
        require_once $loader_path;
    } else {
        // If the loader is not found, send a specific error and exit.
        send_json_response(['error' => 'Loader file not found at ' . $loader_path]);
    }

    $players = [];

    // Get the first configured realm to connect to its character database.
    $realmlists = get_config('realmlists');
    if (empty($realmlists)) {
        send_json_response($players); // Send empty array if no realms configured
    }
    $realm_info = current($realmlists);

    // Establish a database connection using PDO.
    $db = new PDO(
        "mysql:host={$realm_info['db_host']};port={$realm_info['db_port']};dbname={$realm_info['db_name']};charset=utf8",
        $realm_info['db_user'],
        $realm_info['db_pass']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch online characters.
    $stmt = $db->prepare("SELECT `name`, `level`, `class`, `position_x`, `position_y`, `map` FROM `characters` WHERE `online` = 1");
    $stmt->execute();
    
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    send_json_response($players);

} catch (PDOException $e) {
    // A database error occurred.
    send_json_response(['error' => 'Database connection failed.']);
} catch (Throwable $t) {
    // Catch any other general errors (e.g., from require_once)
    send_json_response(['error' => 'A general server error occurred.']);
}