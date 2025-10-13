<?php
/**
 * Generate Reports API
 * Endpoint to generate attendance reports and statistics
 */

include 'config.php';

// Get query parameters
$reportType = isset($_GET['type']) ? $_GET['type'] : 'summary';
$studentId = isset($_GET['studentId']) ? (int)$_GET['studentId'] : 0;
$fromDate = isset($_GET['fromDate']) ? $conn->real_escape_string($_GET['fromDate']) : '';
$toDate = isset($_GET['toDate']) ? $conn->real_escape_string($_GET['toDate']) : '';

$response = [
    'success' => true,
    'reportType' => $reportType
];

switch ($reportType) {
    case 'summary':
        // Student-wise attendance summary
        $sql = "SELECT 
                    s.student_id,
                    s.roll_number,
                    s.first_name,
                    s.last_name,
                    s.class,
                    s.section,
                    c.course_id,
                    c.course_code,
                    c.course_name,
                    COUNT(a.attendance_id) AS total_classes,
                    SUM(CASE WHEN a.status IN ('Present', 'Late') THEN 1 ELSE 0 END) AS classes_attended,
                    SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS classes_missed,
                    ROUND((SUM(CASE WHEN a.status IN ('Present', 'Late') THEN 1 ELSE 0 END) / COUNT(a.attendance_id)) * 100, 2) AS attendance_percentage
                FROM students s
                LEFT JOIN attendance a ON s.student_id = a.student_id
                LEFT JOIN courses c ON a.course_id = c.course_id
                WHERE s.status = 'Active'";
        
        if (!empty($fromDate)) {
            $sql .= " AND a.attendance_date >= '$fromDate'";
        }
        
        if (!empty($toDate)) {
            $sql .= " AND a.attendance_date <= '$toDate'";
        }
        
        $sql .= " GROUP BY s.student_id, c.course_id
                  ORDER BY s.roll_number, c.course_name";
        
        $result = $conn->query($sql);
        $summaryData = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $summaryData[] = [
                    'studentId' => $row['student_id'],
                    'rollNumber' => $row['roll_number'],
                    'studentName' => $row['first_name'] . ' ' . $row['last_name'],
                    'class' => $row['class'],
                    'section' => $row['section'],
                    'courseCode' => $row['course_code'],
                    'courseName' => $row['course_name'],
                    'totalClasses' => (int)$row['total_classes'],
                    'classesAttended' => (int)$row['classes_attended'],
                    'classesMissed' => (int)$row['classes_missed'],
                    'attendancePercentage' => (float)$row['attendance_percentage']
                ];
            }
        }
        
        $response['data'] = $summaryData;
        break;

    case 'statistics':
        // Overall statistics
        $today = date('Y-m-d');
        
        // Total students
        $sql = "SELECT COUNT(*) as total FROM students WHERE status = 'Active'";
        $result = $conn->query($sql);
        $totalStudents = $result->fetch_assoc()['total'];
        
        // Today's present
        $sql = "SELECT COUNT(DISTINCT student_id) as present 
                FROM attendance 
                WHERE attendance_date = '$today' AND status = 'Present'";
        $result = $conn->query($sql);
        $todayPresent = $result->fetch_assoc()['present'];
        
        // Today's absent
        $sql = "SELECT COUNT(DISTINCT student_id) as absent 
                FROM attendance 
                WHERE attendance_date = '$today' AND status = 'Absent'";
        $result = $conn->query($sql);
        $todayAbsent = $result->fetch_assoc()['absent'];
        
        // Overall average attendance
        $sql = "SELECT 
                    COUNT(*) as total_records,
                    SUM(CASE WHEN status IN ('Present', 'Late') THEN 1 ELSE 0 END) as present_records
                FROM attendance";
        $result = $conn->query($sql);
        $stats = $result->fetch_assoc();
        $avgAttendance = $stats['total_records'] > 0 ? 
                        round(($stats['present_records'] / $stats['total_records']) * 100, 2) : 0;
        
        $response['statistics'] = [
            'totalStudents' => (int)$totalStudents,
            'todayPresent' => (int)$todayPresent,
            'todayAbsent' => (int)$todayAbsent,
            'avgAttendance' => (float)$avgAttendance
        ];
        break;

    case 'defaulters':
        // Students with attendance below 75%
        $sql = "SELECT 
                    s.student_id,
                    s.roll_number,
                    s.first_name,
                    s.last_name,
                    s.class,
                    COUNT(a.attendance_id) AS total_classes,
                    SUM(CASE WHEN a.status IN ('Present', 'Late') THEN 1 ELSE 0 END) AS classes_attended,
                    ROUND((SUM(CASE WHEN a.status IN ('Present', 'Late') THEN 1 ELSE 0 END) / COUNT(a.attendance_id)) * 100, 2) AS attendance_percentage
                FROM students s
                INNER JOIN attendance a ON s.student_id = a.student_id
                WHERE s.status = 'Active'";
        
        if (!empty($fromDate)) {
            $sql .= " AND a.attendance_date >= '$fromDate'";
        }
        
        if (!empty($toDate)) {
            $sql .= " AND a.attendance_date <= '$toDate'";
        }
        
        $sql .= " GROUP BY s.student_id
                  HAVING attendance_percentage < 75
                  ORDER BY attendance_percentage ASC";
        
        $result = $conn->query($sql);
        $defaulters = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $defaulters[] = [
                    'studentId' => $row['student_id'],
                    'rollNumber' => $row['roll_number'],
                    'studentName' => $row['first_name'] . ' ' . $row['last_name'],
                    'class' => $row['class'],
                    'totalClasses' => (int)$row['total_classes'],
                    'classesAttended' => (int)$row['classes_attended'],
                    'attendancePercentage' => (float)$row['attendance_percentage']
                ];
            }
        }
        
        $response['defaulters'] = $defaulters;
        break;

    default:
        $response['success'] = false;
        $response['message'] = 'Invalid report type';
}

echo json_encode($response);

$conn->close();
?>