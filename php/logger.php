
<?php
function log_message($message) {
    // Define the path to the logs directory
    $log_dir = __DIR__ . '/../logs/';
    
    // Ensure the logs directory exists
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    date_default_timezone_set("Asia/Calcutta");
    // Set the log file name with the current date
    $log_file = $log_dir . 'log_' . date('Y-m-d') . '.log';
    
    // Format the log message with the current date and time
    $log_entry = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
    
    // Write the log message to the file
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}
?>
