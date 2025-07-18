<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/php_error.log");

// Intentional error
echo $undefinedVariable;

// Check if error logging works
file_put_contents(__DIR__ . "/test_log.txt", "Error testing at " . date("Y-m-d H:i:s"), FILE_APPEND);
?>
