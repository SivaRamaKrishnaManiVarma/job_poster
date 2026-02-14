<?php
require_once '../includes/session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

$data = json_decode(file_get_contents('php://input'), true);
$jobId = (int)($data['job_id'] ?? 0);

if ($jobId <= 0) {
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Invalid job ID']));
}

try {
    // Check if already saved
    $checkStmt = $pdo->prepare("SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?");
    $checkStmt->execute([$candidateId, $jobId]);
    
    if ($checkStmt->fetch()) {
        header('Content-Type: application/json');
        exit(json_encode(['success' => false, 'message' => 'Job already saved']));
    }
    
    // Save job
    $stmt = $pdo->prepare("INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)");
    $stmt->execute([$candidateId, $jobId]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Job saved successfully']);
    exit;
    
} catch(PDOException $e) {
    error_log("Save job error: " . $e->getMessage());
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'message' => 'Failed to save job']));
}
?>
