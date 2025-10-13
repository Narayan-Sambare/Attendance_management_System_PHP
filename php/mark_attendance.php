<?php
/**
 * Mark Attendance API
 * Endpoint to mark attendance for multiple students
 */

include 'config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['date']) || !isset($data['courseId']) || !isset($data['records'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

$date = $conn->real_escape_string($data['date']);
$courseId = (int)$data['courseId'];
$records = $data['records'];

$successCount = 0;
$errorCount = 0;
$errors = [];

// Start transaction
$conn->begin_transaction();

try {
    foreach ($records as $record) {
        $studentId = (int)$record['studentId'];
        $status = $conn->real_escape_string($record['status']);
        $remarks = isset($record['remarks']) ? $conn->real_escape_string($record['remarks']) : '';

        // Check if attendance already exists for this student, course, and date
        $checkSql = "SELECT attendance_id FROM attendance 
                     WHERE student_id = $studentId AND course_id = $courseId AND attendance_date = '$date'";
        $result = $conn->query($checkSql);

        if ($result->num_rows > 0) {
            // Update existing record
            $sql = "UPDATE attendance 
                    SET status = '$status', remarks = '$remarks' 
                    WHERE student_id = $studentId AND course_id = $courseId AND attendance_date = '$date'";
        } else {
            // Insert new record
            $sql = "INSERT INTO attendance (student_id, course_id, attendance_date, status, remarks) 
                    VALUES ($studentId, $courseId, '$date', '$status', '$remarks')";
        }

        if ($conn->query($sql) === TRUE) {
            $successCount++;
        } else {
            $errorCount++;
            $errors[] = "Error for student ID $studentId: " . $conn->error;
        }
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Attendance marked successfully for $successCount students",
        'successCount' => $successCount,
        'errorCount' => $errorCount,
        'errors' => $errors
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Transaction failed: ' . $e->getMessage()
    ]);
}

$conn->close();
?>