<?php
// Enable CORS for development
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Start session
session_start();

// Destroy the session
session_destroy();

// Return success response
echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>