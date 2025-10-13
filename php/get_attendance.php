<?php
/**
 * Get Attendance Records API
 * Endpoint to retrieve attendance records with filters
 */

include 'config.php';

// Get query parameters
$studentId = isset($_GET['studentId']) ? (int)$_GET['studentId'] : 0;
$courseId = isset($_GET['courseId']) ? (int)$_GET['courseId'] : 0;
$fromDate = isset($_GET['fromDate']) ? $conn->real_escape_string($_GET['fromDate']) : '';
$toDate = isset($_GET['toDate']) ? $conn->real_escape_string($_GET['toDate']) : '';
$status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

// Build SQL query
$sql = "SELECT 
            a.attendance_id,
            a.attendance_date,
            a.status,
            a.remarks,
            s.student_id,
            s.roll_number,
            s.first_name,
            s.last_name,
            c.course_id,
            c.course_code,
            c.course_name
        FROM attendance a
        INNER JOIN students s ON a.student_id = s.student_id
        INNER JOIN courses c ON a.course_id = c.course_id
        WHERE 1=1";

if ($studentId > 0) {
    $sql .= " AND a.student_id = $studentId";
}

if ($courseId > 0) {
    $sql .= " AND a.course_id = $courseId";
}

if (!empty($fromDate)) {
    $sql .= " AND a.attendance_date >= '$fromDate'";
}

if (!empty($toDate)) {
    $sql .= " AND a.attendance_date <= '$toDate'";
}

if (!empty($status)) {
    $sql .= " AND a.status = '$status'";
}

$sql .= " ORDER BY a.attendance_date DESC, s.roll_number ASC";

$result = $conn->query($sql);

$records = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $records[] = [
            'id' => $row['attendance_id'],
            'date' => $row['attendance_date'],
            'status' => $row['status'],
            'remarks' => $row['remarks'],
            'studentId' => $row['student_id'],
            'rollNumber' => $row['roll_number'],
            'studentName' => $row['first_name'] . ' ' . $row['last_name'],
            'courseId' => $row['course_id'],
            'courseCode' => $row['course_code'],
            'courseName' => $row['course_name']
        ];
    }
}

echo json_encode([
    'success' => true,
    'count' => count($records),
    'records' => $records
]);

$conn->close();
?>