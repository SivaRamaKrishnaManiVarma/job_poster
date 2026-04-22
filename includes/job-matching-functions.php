<?php
/**
 * Job Matching Functions — Personalized Recommendations Engine
 *
 * Architecture:
 *   - User data (profile, skills, preferences) is fetched ONCE per call.
 *   - calculateJobMatchScore() is a pure function — zero DB queries inside.
 *   - SQL pre-orders user-preferred categories first, reducing PHP-side work.
 *   - Full scored result set is file-cached per user for 30 minutes.
 *   - Cache is explicitly invalidated by any API that mutates matching data.
 *
 * Score breakdown (max 100):
 *   Category match  : 30 pts
 *   Location match  : 20 pts
 *   Salary overlap  : 15 pts
 *   Experience      : 15 pts
 *   Work mode       : 10 pts
 *   Skills keyword  :  5–10 pts
 */

// =============================================================================
// CACHE LAYER
// =============================================================================

/**
 * Return the file path for a user's recommendation cache entry.
 */
function _getRecCachePath(int $userId): string
{
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jp_rec_' . $userId . '.cache';
}

/**
 * Read cached recommendation results for a user.
 * Returns null if the cache is missing or older than 30 minutes.
 */
function getRecommendationCache(int $userId): ?array
{
    $path = _getRecCachePath($userId);

    if (!file_exists($path)) {
        return null;
    }

    // 30-minute TTL
    if ((time() - filemtime($path)) > 1800) {
        @unlink($path);
        return null;
    }

    $raw = @file_get_contents($path);
    if ($raw === false) {
        return null;
    }

    $data = @unserialize($raw);
    return is_array($data) ? $data : null;
}

/**
 * Persist the full scored result set for a user to the cache.
 * LOCK_EX prevents race conditions on concurrent requests.
 */
function setRecommendationCache(int $userId, array $data): void
{
    @file_put_contents(_getRecCachePath($userId), serialize($data), LOCK_EX);
}

/**
 * Explicitly invalidate a user's recommendation cache.
 *
 * Call this after any operation that changes matching-relevant profile data:
 *   - api/update-profile.php      (location, salary, work mode, experience)
 *   - api/add-skill.php / delete-skill.php
 *   - api/add-job-preference.php / delete-job-preference.php
 *   - api/update-location-preferences.php
 */
function invalidateRecommendationCache(int $userId): void
{
    $path = _getRecCachePath($userId);
    if (file_exists($path)) {
        @unlink($path);
    }
}

// =============================================================================
// PURE SCORING ENGINE  (zero DB queries)
// =============================================================================

/**
 * Calculate the match score between a user and a single job.
 *
 * This is a pure function — all data is passed in as arrays.
 * No database calls are made here, making it safe to call in a tight loop.
 *
 * @param array $job             Full job row from the DB (including title, description)
 * @param array $userProfile     Result of getUserProfileForMatching()
 * @param array $userPreferences Result of getUserJobPreferences()
 * @param array $userSkills      Result of getUserSkills()
 * @return array{score: int, breakdown: array}
 */
function calculateJobMatchScore(
    array $job,
    array $userProfile,
    array $userPreferences,
    array $userSkills
): array {
    $scores = [
        'category'   => 0,
        'location'   => 0,
        'salary'     => 0,
        'experience' => 0,
        'work_mode'  => 0,
        'skills'     => 5,  // Neutral baseline; upgraded to 10 on keyword match
    ];

    // ------------------------------------------------------------------
    // 1. Category Match (30 pts)
    // ------------------------------------------------------------------
    foreach ($userPreferences as $interest) {
        if ((int)$interest['job_category_id'] === (int)($job['job_category_id'] ?? 0)) {
            $scores['category'] = match ((int)$interest['priority']) {
                3       => 30,
                2       => 20,
                1       => 10,
                default => 5,
            };
            break;
        }
    }

    // ------------------------------------------------------------------
    // 2. Location Match (20 pts) — bidirectional partial/substring match
    //    "Mumbai" matches "Mumbai, Maharashtra" and vice-versa.
    // ------------------------------------------------------------------
    if (!empty($userProfile['preferred_locations'])) {
        $preferredLocs = array_filter(
            array_map('trim', explode(',', strtolower($userProfile['preferred_locations'])))
        );
        $jobLoc        = strtolower(trim($job['location'] ?? ''));

        $locationMatched = false;
        foreach ($preferredLocs as $pref) {
            if ($pref !== '' && (str_contains($jobLoc, $pref) || str_contains($pref, $jobLoc))) {
                $scores['location'] = 20;
                $locationMatched    = true;
                break;
            }
        }

        if (!$locationMatched) {
            // Willing to relocate = partial credit
            $scores['location'] = ($userProfile['willing_to_relocate'] ?? 0) ? 10 : 5;
        }
    } else {
        $scores['location'] = 5; // No preference set — neutral
    }

    // ------------------------------------------------------------------
    // 3. Salary Overlap (15 pts)
    // ------------------------------------------------------------------
    $userMin = (float)($userProfile['expected_salary_min'] ?? 0);
    $userMax = (float)($userProfile['expected_salary_max'] ?? 0);
    $jobMin  = (float)($job['salary_min'] ?? 0);
    $jobMax  = (float)($job['salary_max'] ?? 0);

    if ($userMin > 0 && $userMax > 0 && $jobMin > 0 && $jobMax > 0) {
        $overlapStart = max($userMin, $jobMin);
        $overlapEnd   = min($userMax, $jobMax);

        if ($overlapEnd >= $overlapStart) {
            $jobRangeSize = $jobMax - $jobMin;
            if ($jobRangeSize > 0) {
                $overlapPct       = (($overlapEnd - $overlapStart) / $jobRangeSize) * 100;
                $scores['salary'] = (int)min(round(($overlapPct / 100) * 15), 15);
            } else {
                $scores['salary'] = 15; // Fixed single-point salary — full match
            }
        }
    } else {
        $scores['salary'] = 5; // Insufficient data — neutral
    }

    // ------------------------------------------------------------------
    // 4. Experience Match (15 pts)
    // ------------------------------------------------------------------
    $userExp = (int)($userProfile['total_experience_years'] ?? 0);
    $expMap  = [
        'Freshers'  => [0, 1],
        '0-2 years' => [0, 2],
        '2-5 years' => [2, 5],
        '5+ years'  => [5, 99],
    ];

    [$jobExpMin, $jobExpMax] = $expMap[$job['experience_level'] ?? 'Freshers'] ?? [0, 99];

    if ($userExp >= $jobExpMin && $userExp <= $jobExpMax) {
        $scores['experience'] = 15;
    } elseif ($userExp >= ($jobExpMin - 1) && $userExp <= ($jobExpMax + 1)) {
        $scores['experience'] = 10;
    } elseif ($userExp >= ($jobExpMin - 2) && $userExp <= ($jobExpMax + 2)) {
        $scores['experience'] = 5;
    }

    // ------------------------------------------------------------------
    // 5. Work Mode Match (10 pts)
    // ------------------------------------------------------------------
    $userWorkMode = (int)($userProfile['preferred_work_mode_id'] ?? 0);
    $jobWorkMode  = (int)($job['work_mode_id'] ?? 0);

    if ($userWorkMode > 0 && $jobWorkMode > 0) {
        $scores['work_mode'] = ($userWorkMode === $jobWorkMode) ? 10 : 0;
    } else {
        $scores['work_mode'] = 5; // No preference — neutral
    }

    // ------------------------------------------------------------------
    // 6. Skills Keyword Match (5–10 pts)
    //    Substring, case-insensitive. "PHP" matches "PHP Developer".
    //    Searches job title (full) + first 500 chars of description only
    //    to bound execution time on very long descriptions.
    // ------------------------------------------------------------------
    if (!empty($userSkills)) {
        $searchText = strtolower(
            ($job['title'] ?? '') . ' ' . substr($job['description'] ?? '', 0, 500)
        );

        foreach ($userSkills as $skillRow) {
            $skill = strtolower(trim($skillRow['skill_name'] ?? ''));
            if ($skill !== '' && str_contains($searchText, $skill)) {
                $scores['skills'] = 10; // Upgrade from neutral baseline
                break;
            }
        }
    }

    return [
        'score'     => (int)round(array_sum($scores)),
        'breakdown' => $scores,
    ];
}

// =============================================================================
// RECOMMENDATION ENGINE
// =============================================================================

/**
 * Get personalized job recommendations for a user.
 *
 * Performance characteristics:
 *   - Cache hit  : 0 DB queries, O(1).
 *   - Cache miss : 3 DB queries for user data + 1 job-fetch query.
 *                  Scoring loop is pure PHP, no DB calls per job.
 *
 * @param int   $userId
 * @param PDO   $pdo
 * @param array $options  {tier, limit, offset, filters, bypass_cache}
 * @return array{jobs: array, total_count: int}
 */
function getRecommendedJobs(int $userId, PDO $pdo, array $options = []): array
{
    $tier        = $options['tier']         ?? 'all';
    $limit       = (int)($options['limit']  ?? 5);
    $offset      = (int)($options['offset'] ?? 0);
    $filters     = $options['filters']      ?? [];
    $bypassCache = (bool)($options['bypass_cache'] ?? false);

    // ------------------------------------------------------------------
    // 1. Cache check — return immediately if a warm entry exists
    // ------------------------------------------------------------------
    if (!$bypassCache) {
        $cached = getRecommendationCache($userId);
        if ($cached !== null) {
            return _sliceRecommendations($cached, $tier, $limit, $offset);
        }
    }

    // ------------------------------------------------------------------
    // 2. Fetch all user-matching data — 3 queries total, not per job
    // ------------------------------------------------------------------
    $userProfile     = getUserProfileForMatching($userId, $pdo);
    $userPreferences = getUserJobPreferences($userId, $pdo);
    $userSkills      = getUserSkills($userId, $pdo);

    // Build lookup sets for O(1) checks inside the scoring loop
    $userCategoryIds  = array_column($userPreferences, 'job_category_id');
    $highPriorityIds  = array_column(
        array_filter($userPreferences, fn($p) => (int)$p['priority'] === 3),
        'job_category_id'
    );
    $highPrioritySet  = array_flip($highPriorityIds); // O(1) isset() lookup

    // ------------------------------------------------------------------
    // 3. Single SQL fetch
    //    User-preferred categories are ordered first via CASE expression,
    //    so the most relevant rows surface before the 500-row cap cuts off.
    // ------------------------------------------------------------------
    $params = [];
    $sql    = "
        SELECT j.*,
               mc.category_name,
               mc.icon          AS category_icon,
               mw.mode_name     AS work_mode_name,
               met.type_name    AS employment_type_name
        FROM   jobs j
        LEFT JOIN master_job_categories   mc  ON j.job_category_id  = mc.id
        LEFT JOIN master_work_modes       mw  ON j.work_mode_id     = mw.id
        LEFT JOIN master_employment_types met ON j.employment_type_id = met.id
        WHERE  j.is_active = 1
          AND  (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
    ";

    // Caller-supplied filters (preserved from original interface)
    if (!empty($filters['work_mode'])) {
        $ph     = implode(',', array_fill(0, count($filters['work_mode']), '?'));
        $sql   .= " AND j.work_mode_id IN ($ph)";
        $params = array_merge($params, array_values($filters['work_mode']));
    }
    if (!empty($filters['salary_min'])) {
        $sql     .= " AND j.salary_max >= ?";
        $params[] = (float)$filters['salary_min'];
    }
    if (!empty($filters['salary_max'])) {
        $sql     .= " AND j.salary_min <= ?";
        $params[] = (float)$filters['salary_max'];
    }

    // Pre-sort preferred categories to the top; newest posting within each band
    if (!empty($userCategoryIds)) {
        $ph     = implode(',', array_fill(0, count($userCategoryIds), '?'));
        $sql   .= " ORDER BY CASE WHEN j.job_category_id IN ($ph) THEN 0 ELSE 1 END,
                             j.posted_date DESC";
        $params = array_merge($params, array_values($userCategoryIds));
    } else {
        $sql .= " ORDER BY j.posted_date DESC";
    }

    // Cap at 500 — SQL pre-ordering ensures preferred results are not chopped
    $sql .= " LIMIT 500";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ------------------------------------------------------------------
    // 4. Score every job — pure function, zero DB calls per iteration
    // ------------------------------------------------------------------
    $jobsWithScores = [];

    foreach ($jobs as $job) {
        $matchResult = calculateJobMatchScore($job, $userProfile, $userPreferences, $userSkills);
        $score       = $matchResult['score'];

        // Drop jobs below the relevance threshold
        if ($score < 40) {
            continue;
        }

        $matchTier = 'other';
        if ($score >= 90)     $matchTier = 'best';
        elseif ($score >= 70) $matchTier = 'strong';

        $job['company_name'] = $job['company']; // Backward-compatibility alias

        $jobsWithScores[] = [
            'job'             => $job,
            'match_score'     => $score,
            'match_tier'      => $matchTier,
            'is_high_priority' => isset($highPrioritySet[(int)$job['job_category_id']]),
            'match_breakdown' => $matchResult['breakdown'],
        ];
    }

    // Sort descending by score
    usort($jobsWithScores, fn($a, $b) => $b['match_score'] <=> $a['match_score']);

    // ------------------------------------------------------------------
    // 5. Store the full sorted set to cache (30-min TTL)
    // ------------------------------------------------------------------
    setRecommendationCache($userId, $jobsWithScores);

    return _sliceRecommendations($jobsWithScores, $tier, $limit, $offset);
}

/**
 * Apply tier filter, offset, and limit to the full scored set.
 * Extracted so cache-hit and cache-miss paths follow identical logic.
 *
 * @internal
 */
function _sliceRecommendations(array $jobsWithScores, string $tier, int $limit, int $offset): array
{
    if ($tier !== 'all') {
        if ($tier === 'high_priority') {
            $jobsWithScores = array_values(
                array_filter($jobsWithScores, fn($j) => $j['is_high_priority'])
            );
        } else {
            $jobsWithScores = array_values(
                array_filter($jobsWithScores, fn($j) => $j['match_tier'] === $tier)
            );
        }
    }

    return [
        'jobs'        => array_slice($jobsWithScores, $offset, $limit),
        'total_count' => count($jobsWithScores),
    ];
}

// =============================================================================
// PUBLIC HELPER FUNCTIONS  (external callers — unchanged signatures)
// =============================================================================

/**
 * Return counts per recommendation tier.
 * Uses the warm cache when available; triggers computation if cold.
 */
function getRecommendedJobCounts(int $userId, PDO $pdo): array
{
    $counts = [
        'high_priority' => 0,
        'best'          => 0,
        'strong'        => 0,
        'other'         => 0,
        'total'         => 0,
    ];

    // Reuse warm cache; if cold, a full scoring pass populates it
    $allJobs = getRecommendationCache($userId);
    if ($allJobs === null) {
        getRecommendedJobs($userId, $pdo, ['tier' => 'all', 'limit' => 9999]);
        $allJobs = getRecommendationCache($userId);
        if ($allJobs === null) {
            return $counts;
        }
    }

    foreach ($allJobs as $job) {
        if ($job['is_high_priority']) {
            $counts['high_priority']++;
        }
        $counts[$job['match_tier']]++;
        $counts['total']++;
    }

    return $counts;
}

/**
 * Category list sorted by job count, with user-preferred categories first.
 */
function getCategoryJobCounts(int $userId, PDO $pdo, int $limit = 8): array
{
    $stmt = $pdo->prepare("SELECT job_category_id FROM user_job_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userInterestIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $ph  = empty($userInterestIds)
        ? '0'
        : implode(',', array_fill(0, count($userInterestIds), '?'));

    $sql = "
        SELECT   mc.id,
                 mc.category_name,
                 mc.icon,
                 COUNT(j.id) AS job_count
        FROM     master_job_categories mc
        LEFT JOIN jobs j ON mc.id = j.job_category_id
                        AND j.is_active = 1
                        AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
        WHERE    mc.is_active = 1
        GROUP BY mc.id
        HAVING   job_count > 0
        ORDER BY CASE WHEN mc.id IN ($ph) THEN 0 ELSE 1 END,
                 job_count DESC
        LIMIT    ?
    ";

    $params = array_merge($userInterestIds, [$limit]);
    $stmt   = $pdo->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as &$cat) {
        $cat['is_user_interest'] = in_array($cat['id'], $userInterestIds, true);
    }

    return $categories;
}

/**
 * Fetch the subset of user profile fields used for matching.
 */
function getUserProfileForMatching(int $userId, PDO $pdo): array
{
    $stmt = $pdo->prepare("
        SELECT total_experience_years,
               preferred_work_mode_id,
               expected_salary_min,
               expected_salary_max,
               preferred_locations,
               willing_to_relocate,
               location AS current_location
        FROM   users
        WHERE  id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Fetch the user's job category preferences with priority levels.
 */
function getUserJobPreferences(int $userId, PDO $pdo): array
{
    $stmt = $pdo->prepare("
        SELECT job_category_id, priority
        FROM   user_job_preferences
        WHERE  user_id = ?
        ORDER  BY priority DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch the user's skill list.
 */
function getUserSkills(int $userId, PDO $pdo): array
{
    $stmt = $pdo->prepare("SELECT skill_name FROM user_skills WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch a single job's fields for matching (legacy helper).
 * Retained for any direct external callers.
 * Note: Not used internally — the main query already provides all fields.
 */
function getJobForMatching(int $jobId, PDO $pdo): array|false
{
    $stmt = $pdo->prepare("
        SELECT j.job_category_id, j.location,  j.salary_min,
               j.salary_max,      j.work_mode_id, j.experience_level,
               j.employment_type_id, j.title,   j.description
        FROM   jobs j
        WHERE  j.id = ?
          AND  j.is_active = 1
          AND  (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
    ");
    $stmt->execute([$jobId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Check if a job category is a high-priority interest for the user.
 * Kept for any direct external callers outside the scoring loop.
 */
function isJobInHighPriorityCategories(int $userId, int $categoryId, PDO $pdo): bool
{
    $stmt = $pdo->prepare("
        SELECT 1 FROM user_job_preferences
        WHERE  user_id = ? AND job_category_id = ? AND priority = 3
        LIMIT  1
    ");
    $stmt->execute([$userId, $categoryId]);
    return (bool)$stmt->fetchColumn();
}

/**
 * Calculate a user's profile completion percentage (matching quality indicator).
 */
function getProfileCompletion(int $userId, PDO $pdo): int
{
    $points    = 0;
    $maxPoints = 10;

    $user = getUserProfileForMatching($userId, $pdo);

    if (!empty($user['current_location']))             $points++;
    if (($user['total_experience_years'] ?? 0) >= 0)   $points++;
    if (($user['expected_salary_min']    ?? 0) > 0)    $points++;
    if (($user['preferred_work_mode_id'] ?? 0) > 0)    $points++;

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

    return (int)round(($points / $maxPoints) * 100);
}

/**
 * Format a match score for display (color + CSS class label).
 */
function formatMatchScore(int $score): array
{
    $color     = '#9ca3af';
    $textClass = 'fair';

    if ($score >= 90) {
        $color     = '#10b981';
        $textClass = 'excellent';
    } elseif ($score >= 70) {
        $color     = '#3b82f6';
        $textClass = 'great';
    } elseif ($score >= 40) {
        $color     = '#f59e0b';
        $textClass = 'good';
    }

    return [
        'score' => $score,
        'color' => $color,
        'class' => $textClass,
    ];
}
