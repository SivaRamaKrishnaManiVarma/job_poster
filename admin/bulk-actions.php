<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) redirect('login.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    
    if ($action === 'deactivate_expired') {
        // Deactivate all expired jobs
        $stmt = $pdo->prepare("
            UPDATE jobs 
            SET is_active = 0 
            WHERE is_active = 1 
            AND application_deadline IS NOT NULL 
            AND application_deadline < CURDATE()
        ");
        $stmt->execute();
        $affected = $stmt->rowCount();
        
        header("Location: jobs.php?success=bulk_deactivated&count=$affected");
        exit;
    }
}

redirect('jobs.php');
?>
