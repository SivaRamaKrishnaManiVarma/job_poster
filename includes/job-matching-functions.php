<?php
/**
 * Job Matching Functions - Personalized Recommendations Engine
 */

if (!defined('URL')) {
    // die('Direct access not permitted');
}

/**
 * Calculate match score between user and job (0-100 points)
 */
function calculateJobMatchScore($userId, $jobId, $pdo) {
    $scores = [
        'category' => 0,
        'location' => 0, 
        'salary' => 0,
        'experience' => 0,
        'work_mode' => 0,
        'skills' => 5 // Neutral since no required_skills field
    ];
    
    $user = getUserProfileForMatching($userId, $pdo);
    $userInterests = getUserJobPreferences($userId, $pdo);
    $userSkills = getUserSkills($userId, $pdo);
    $job = getJobForMatching($jobId, $pdo);
    
    if (!$job) {
        return ['score' => 0, 'breakdown' => $scores];
    }
    
    // 1. Category Match (30 points)
    foreach ($userInterests as $interest) {
        if ($interest['job_category_id'] == $job['job_category_id']) {
            $scores['category'] = match($interest['priority']) {
                3 => 30,
                2 => 20,
                1 => 10,
                default => 5
            };
            break;
        }
    }
    
    // 2. Location Match (20 points)
    if (!empty($user['preferred_locations'])) {
        $preferredLocs = array_map('trim', array_map('strtolower', explode(',', $user['preferred_locations'])));
        $jobLoc = strtolower(trim($job['location']));
        
        if (in_array($jobLoc, $preferredLocs)) {
            $scores['location'] = 20;
        } elseif ($user['willing_to_relocate'] ?? 0) {
            $scores['location'] = 10;
        } else {
            $scores['location'] = 5;
        }
    } else {
        $scores['location'] = 5;
    }
    
    // 3. Salary Overlap (15 points)
    if (($user['expected_salary_min'] ?? 0) && ($user['expected_salary_max'] ?? 0) &&
        ($job['salary_min'] ?? 0) && ($job['salary_max'] ?? 0)) {
        
        $overlapStart = max($user['expected_salary_min'], $job['salary_min']);
        $overlapEnd = min($user['expected_salary_max'], $job['salary_max']);
        
        if ($overlapEnd >= $overlapStart) {
            $overlapSize = $overlapEnd - $overlapStart;
            $jobRangeSize = $job['salary_max'] - $job['salary_min'];
            if ($jobRangeSize > 0) {
                $overlapPercent = ($overlapSize / $jobRangeSize) * 100;
                $scores['salary'] = min(($overlapPercent / 100) * 15, 15);
            } else {
                $scores['salary'] = 15; // Full match if job has fixed salary
            }
        }
    } else {
        $scores['salary'] = 5; // Neutral
    }
    
    // 4. Experience Match (15 points)
    $userExp = (int)($user['total_experience_years'] ?? 0);
    
    // Map experience_level enum to years
    $expMap = [
        'Freshers' => [0, 1],
        '0-2 years' => [0, 2],
        '2-5 years' => [2, 5],
        '5+ years' => [5, 99]
    ];
    
    $jobExpLevel = $job['experience_level'] ?? 'Freshers';
    $range = $expMap[$jobExpLevel] ?? [0, 99];
    $jobMin = $range[0];
    $jobMax = $range[1];
    
    if ($userExp >= $jobMin && $userExp <= $jobMax) {
        $scores['experience'] = 15;
    } elseif ($userExp >= ($jobMin - 1) && $userExp <= ($jobMax + 1)) {
        $scores['experience'] = 10;
    } elseif ($userExp >= ($jobMin - 2) && $userExp <= ($jobMax + 2)) {
        $scores['experience'] = 5;
    }
    
    // 5. Work Mode Match (10 points)
    if (($user['preferred_work_mode_id'] ?? 0) && ($job['work_mode_id'] ?? 0)) {
        if ($user['preferred_work_mode_id'] == $job['work_mode_id']) {
            $scores['work_mode'] = 10;
        }
    } else {
        $scores['work_mode'] = 5;
    }
    
    $totalScore = array_sum($scores);
    
    return [
        'score' => (int)round($totalScore),
        'breakdown' => $scores
    ];
}

/**
 * Get personalized job recommendations
 */
function getRecommendedJobs($userId, $pdo, $options = []) {
    $tier = $options['tier'] ?? 'all';
    $limit = $options['limit'] ?? 5;
    $offset = $options['offset'] ?? 0;
    $filters = $options['filters'] ?? [];
    
    $sql = "
        SELECT j.*, 
               mc.category_name, mc.icon as category_icon,
               mw.mode_name as work_mode_name,
               met.type_name as employment_type_name
        FROM jobs j 
        LEFT JOIN master_job_categories mc ON j.job_category_id = mc.id
        LEFT JOIN master_work_modes mw ON j.work_mode_id = mw.id
        LEFT JOIN master_employment_types met ON j.employment_type_id = met.id 
        WHERE j.is_active = 1 
        AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
    ";
    
    $params = [];
    
    // Apply filters
    if (!empty($filters['location'])) {
        $placeholders = implode(',', array_fill(0, count($filters['location']), '?'));
        $sql .= " AND j.location IN ($placeholders)";
        $params = array_merge($params, $filters['location']);
    }
    
    if (!empty($filters['work_mode'])) {
        $placeholders = implode(',', array_fill(0, count($filters['work_mode']), '?'));
        $sql .= " AND j.work_mode_id IN ($placeholders)";
        $params = array_merge($params, $filters['work_mode']);
    }
    
    if (!empty($filters['salary_min'])) {
        $sql .= " AND j.salary_max >= ?";
        $params[] = $filters['salary_min'];
    }
    
    if (!empty($filters['salary_max'])) {
        $sql .= " AND j.salary_min <= ?";
        $params[] = $filters['salary_max'];
    }
    
    $sql .= " ORDER BY j.posted_date DESC LIMIT 999";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate match scores
    $jobsWithScores = [];
    foreach ($jobs as $job) {
        $matchResult = calculateJobMatchScore($userId, $job['id'], $pdo);
        $score = $matchResult['score'];
        
        if ($score < 40) continue;
        
        $matchTier = 'other';
        if ($score >= 90) $matchTier = 'best';
        elseif ($score >= 70) $matchTier = 'strong';
        
        $isHighPriority = isJobInHighPriorityCategories($userId, $job['job_category_id'], $pdo);
        
        // Add company_name field (use company column from jobs table)
        $job['company_name'] = $job['company'];
        
        $jobsWithScores[] = [
            'job' => $job,
            'match_score' => $score,
            'match_tier' => $matchTier,
            'is_high_priority' => $isHighPriority,
            'match_breakdown' => $matchResult['breakdown']
        ];
    }
    
    usort($jobsWithScores, function($a, $b) {
        return $b['match_score'] <=> $a['match_score'];
    });
    
    // Filter by tier
    if ($tier !== 'all') {
        if ($tier === 'high_priority') {
            $jobsWithScores = array_filter($jobsWithScores, fn($j) => $j['is_high_priority']);
        } else {
            $jobsWithScores = array_filter($jobsWithScores, fn($j) => $j['match_tier'] === $tier);
        }
    }
    
    $totalCount = count($jobsWithScores);
    $jobsWithScores = array_slice($jobsWithScores, $offset, $limit);
    
    return [
        'jobs' => $jobsWithScores,
        'total_count' => $totalCount
    ];
}

/**
 * Get job counts for each tier
 */
function getRecommendedJobCounts($userId, $pdo) {
    $counts = [
        'high_priority' => 0,
        'best' => 0,
        'strong' => 0,
        'other' => 0,
        'total' => 0
    ];
    
    $allJobs = getRecommendedJobs($userId, $pdo, ['tier' => 'all', 'limit' => 999]);
    
    foreach ($allJobs['jobs'] as $job) {
        if ($job['is_high_priority']) {
            $counts['high_priority']++;
        }
        $counts[$job['match_tier']]++;
        $counts['total']++;
    }
    
    return $counts;
}

/**
 * Get categories with job counts
 */
function getCategoryJobCounts($userId, $pdo, $limit = 8) {
    $userInterestIds = [];
    $stmt = $pdo->prepare("SELECT job_category_id FROM user_job_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userInterestIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $sql = "
        SELECT 
            mc.id,
            mc.category_name, 
            mc.icon,
            COUNT(j.id) as job_count
        FROM master_job_categories mc
        LEFT JOIN jobs j ON mc.id = j.job_category_id 
            AND j.is_active = 1 
            AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
        WHERE mc.is_active = 1
        GROUP BY mc.id
        HAVING job_count > 0
        ORDER BY 
            CASE WHEN mc.id IN (" . 
            (empty($userInterestIds) ? '0' : implode(',', array_fill(0, count($userInterestIds), '?'))) 
            . ") THEN 0 ELSE 1 END,
            job_count DESC
        LIMIT ?
    ";
    
    $params = array_merge($userInterestIds, [$limit]);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($categories as &$cat) {
        $cat['is_user_interest'] = in_array($cat['id'], $userInterestIds);
    }
    
    return $categories;
}

/**
 * Check if job category is high priority
 */
function isJobInHighPriorityCategories($userId, $categoryId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT priority FROM user_job_preferences 
        WHERE user_id = ? AND job_category_id = ? AND priority = 3
    ");
    $stmt->execute([$userId, $categoryId]);
    return $stmt->rowCount() > 0;
}

/**
 * Get user profile for matching
 */
function getUserProfileForMatching($userId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            total_experience_years,
            preferred_work_mode_id,
            expected_salary_min,
            expected_salary_max,
            preferred_locations,
            willing_to_relocate,
            location as current_location
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Get user job preferences
 */
function getUserJobPreferences($userId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT job_category_id, priority 
        FROM user_job_preferences 
        WHERE user_id = ?
        ORDER BY priority DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get user skills
 */
function getUserSkills($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT skill_name FROM user_skills WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get job data for matching
 */
function getJobForMatching($jobId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            j.job_category_id,
            j.location,
            j.salary_min,
            j.salary_max,
            j.work_mode_id,
            j.experience_level,
            j.employment_type_id
        FROM jobs j
        WHERE j.id = ? 
        AND j.is_active = 1 
        AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
    ");
    $stmt->execute([$jobId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get profile completion percentage
 */
function getProfileCompletion($userId, $pdo) {
    $points = 0;
    $maxPoints = 10;
    
    $user = getUserProfileForMatching($userId, $pdo);
    
    if (!empty($user['current_location'])) $points++;
    if (($user['total_experience_years'] ?? 0) >= 0) $points++;
    if (($user['expected_salary_min'] ?? 0) > 0) $points++;
    if (($user['preferred_work_mode_id'] ?? 0) > 0) $points++;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_job_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetchColumn() >= 1) $points += 2;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_skills WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetchColumn() >= 3) $points++;
    
    $stmt = $pdo->prepare("SELECT resume_path FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if (!empty($stmt->fetchColumn())) $points++;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_education WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetchColumn() >= 1) $points++;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_experience WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetchColumn() >= 1) $points++;
    
    return round(($points / $maxPoints) * 100);
}

/**
 * Format match score for display
 */
function formatMatchScore($score) {
    $color = '#9ca3af';
    $textClass = 'fair';
    
    if ($score >= 90) {
        $color = '#10b981';
        $textClass = 'excellent';
    } elseif ($score >= 70) {
        $color = '#3b82f6';
        $textClass = 'great';
    } elseif ($score >= 40) {
        $color = '#f59e0b';
        $textClass = 'good';
    }
    
    return [
        'score' => $score,
        'color' => $color,
        'class' => $textClass
    ];
}
?>
