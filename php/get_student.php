<?php
/**
 * Get Students API
 * Endpoint to retrieve all students or filter by class
 */

include 'config.php';

// Get query parameters
$class = isset($_GET['class']) ? $conn->real_escape_string($_GET['class']) : '';
$section = isset($_GET['section']) ? $conn->real_escape_string($_GET['section']) : '';

// Build SQL query
$sql = "SELECT student_id, roll_number, first_name, last_name, email, phone, class, section, status, enrollment_date 
        FROM students WHERE status = 'Active'";

if (!empty($class)) {
    $sql .= " AND class = '$class'";
}

if (!empty($section)) {
    $sql .= " AND section = '$section'";
}

$sql .= " ORDER BY roll_number ASC";

$result = $conn->query($sql);

$students = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $students[] = [
            'id' => $row['student_id'],
            'rollNumber' => $row['roll_number'],
            'firstName' => $row['first_name'],
            'lastName' => $row['last_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'class' => $row['class'],
            'section' => $row['section'],
            'status' => $row['status'],
            'enrollmentDate' => $row['enrollment_date']
        ];
    }
}

echo json_encode([
    'success' => true,
    'count' => count($students),
    'students' => $students
]);

$conn->close();
?>