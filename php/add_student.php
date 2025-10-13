<?php
/**
 * Add Student API
 * Endpoint to add a new student to the database
 */

include 'config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['rollNumber']) || !isset($data['firstName']) || !isset($data['lastName'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Sanitize inputs
$rollNumber = $conn->real_escape_string($data['rollNumber']);
$firstName = $conn->real_escape_string($data['firstName']);
$lastName = $conn->real_escape_string($data['lastName']);
$email = isset($data['email']) ? $conn->real_escape_string($data['email']) : '';
$phone = isset($data['phone']) ? $conn->real_escape_string($data['phone']) : '';
$class = $conn->real_escape_string($data['class']);
$section = $conn->real_escape_string($data['section']);

// Check if roll number already exists
$checkSql = "SELECT student_id FROM students WHERE roll_number = '$rollNumber'";
$result = $conn->query($checkSql);

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Student with this roll number already exists'
    ]);
    exit;
}

// Insert student
$sql = "INSERT INTO students (roll_number, first_name, last_name, email, phone, class, section, status) 
        VALUES ('$rollNumber', '$firstName', '$lastName', '$email', '$phone', '$class', '$section', 'Active')";

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        'success' => true,
        'message' => 'Student added successfully',
        'student_id' => $conn->insert_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $conn->error
    ]);
}

$conn->close();
?>