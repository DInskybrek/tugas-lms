<?php
/**
 * Dashboard API - Get user dashboard data
 */

require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    json_response(false, 'User not authenticated');
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Get user stats
    $active_courses = getRow(
        'SELECT COUNT(*) as count FROM enrollments WHERE user_id = ' . $user_id . ' AND status = "active"'
    )['count'];
    
    $completed_courses = getRow(
        'SELECT COUNT(*) as count FROM enrollments WHERE user_id = ' . $user_id . ' AND status = "completed"'
    )['count'];
    
    $certificates = getRow(
        'SELECT COUNT(*) as count FROM certificates WHERE user_id = ' . $user_id
    )['count'];
    
    $points = getRow(
        'SELECT COALESCE(SUM(points), 0) as total FROM user_points WHERE user_id = ' . $user_id
    )['total'];
    
    // Get user courses
    $courses = getRows(
        'SELECT c.id, c.title, c.description, e.progress, e.status FROM courses c 
         JOIN enrollments e ON c.id = e.course_id 
         WHERE e.user_id = ' . $user_id . ' LIMIT 10'
    );
    
    // Format courses
    $formatted_courses = array();
    foreach ($courses as $course) {
        $formatted_courses[] = array(
            'id' => $course['id'],
            'title' => $course['title'],
            'description' => $course['description'],
            'progress' => $course['progress'],
            'status' => $course['status']
        );
    }
    
    // Get recent activities
    $activities = getRows(
        'SELECT action, created_at FROM activity_logs WHERE user_id = ' . $user_id . ' ORDER BY created_at DESC LIMIT 5'
    );
    
    // Format activities
    $formatted_activities = array();
    foreach ($activities as $activity) {
        $icon = '';
        $text = '';
        
        switch ($activity['action']) {
            case 'login':
                $icon = '👤';
                $text = 'Anda melakukan login';
                break;
            case 'logout':
                $icon = '🚪';
                $text = 'Anda melakukan logout';
                break;
            case 'enroll':
                $icon = '📚';
                $text = 'Anda terdaftar di kursus baru';
                break;
            case 'complete':
                $icon = '✅';
                $text = 'Anda menyelesaikan kursus';
                break;
            default:
                $icon = '📝';
                $text = $activity['action'];
        }
        
        $formatted_activities[] = array(
            'icon' => $icon,
            'text' => $text,
            'time' => date('d M Y H:i', strtotime($activity['created_at']))
        );
    }
    
    json_response(true, 'Dashboard data retrieved', array(
        'stats' => array(
            'active_courses' => $active_courses,
            'completed_courses' => $completed_courses,
            'certificates' => $certificates,
            'points' => $points
        ),
        'courses' => $formatted_courses,
        'activities' => $formatted_activities
    ));
    
} catch (Exception $e) {
    json_response(false, 'Error: ' . $e->getMessage());
}

?>
