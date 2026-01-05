<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/master-data-functions.php';

$pageTitle = 'Job Archive - Past Opportunities';

// Get master data
$categories = getAllJobCategories($pdo, true);
$workModes = getAllWorkModes($pdo, true);
$employmentTypes = getAllEmploymentTypes($pdo, true);
$experienceLevels = getAllExperienceLevels($pdo, true);

// Get count of archived jobs
$totalArchived = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE is_active = 1 
    AND application_deadline IS NOT NULL 
    AND application_deadline < CURDATE()
")->fetchColumn();

include 'includes/header.php';
?>

<style>
.archive-banner {
    background: linear-gradient(135deg, #6c757d, #495057);
    color: white;
    padding: 3rem 0;
    text-align: center;
    margin-bottom: 2rem;
    border-radius: 12px;
}

.archive-banner h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.archive-banner p {
    font-size: 1.125rem;
    opacity: 0.9;
    margin-bottom: 1rem;
}

.archive-notice {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.archive-notice-icon {
    font-size: 2rem;
}

.archive-notice-text h5 {
    margin: 0 0 0.25rem 0;
    color: #856404;
}

.archive-notice-text p {
    margin: 0;
    color: #856404;
    font-size: 0.9375rem;
}

.job-card.archived {
    opacity: 0.85;
    position: relative;
}

.job-card.archived::before {
    content: "ARCHIVED";
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #6c757d;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    z-index: 1;
}

.back-to-jobs {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 1.5rem;
    transition: gap 0.2s;
}

.back-to-jobs:hover {
    gap: 0.75rem;
    color: var(--primary-hover);
}
</style>

<div class="container">
    <!-- Back to Active Jobs -->
    <a href="index.php" class="back-to-jobs">
        ← Back to Active Jobs
    </a>

    <!-- Archive Banner -->
    <div class="archive-banner">
        <h1>📚 Job Archive</h1>
        <p>Browse past job opportunities for reference</p>
        <div class="mt-3">
            <span class="badge bg-light text-dark fs-5 px-4 py-2">
                <?php echo $totalArchived; ?> Archived Jobs
            </span>
        </div>
    </div>

    <!-- Archive Notice -->
    <div class="archive-notice">
        <div class="archive-notice-icon">ℹ️</div>
        <div class="archive-notice-text">
            <h5>Reference Only</h5>
            <p>These jobs have passed their application deadlines. They are displayed here for reference purposes only. Visit <a href="index.php">Active Jobs</a> for current opportunities.</p>
        </div>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <form id="searchForm">
            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <input type="text" 
                           name="search" 
                           id="searchInput"
                           class="form-control" 
                           placeholder="🔍 Search archived jobs..." 
                           autocomplete="off">
                </div>
                <div class="col-md-4">
                    <select name="category" id="categorySelect" class="form-select">
                        <option value="">📂 All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo $cat['icon']; ?> <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" 
                           name="location" 
                           id="locationInput"
                           class="form-control" 
                           placeholder="📍 Location" 
                           autocomplete="off">
                </div>
            </div>
            
            <!-- Advanced Filters -->
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="work_mode" id="workModeSelect" class="form-select">
                        <option value="">🏠 All Work Modes</option>
                        <?php foreach ($workModes as $mode): ?>
                            <option value="<?php echo $mode['id']; ?>">
                                <?php echo $mode['icon']; ?> <?php echo htmlspecialchars($mode['mode_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="employment_type" id="employmentTypeSelect" class="form-select">
                        <option value="">💼 All Job Types</option>
                        <?php foreach ($employmentTypes as $type): ?>
                            <option value="<?php echo $type['id']; ?>">
                                <?php echo $type['icon']; ?> <?php echo htmlspecialchars($type['type_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="experience_level" id="experienceSelect" class="form-select">
                        <option value="">🎓 All Experience Levels</option>
                        <?php foreach ($experienceLevels as $level): ?>
                            <option value="<?php echo $level['id']; ?>">
                                <?php echo $level['icon']; ?> <?php echo htmlspecialchars($level['level_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" id="clearFilters" class="btn btn-outline-secondary w-100">
                        ✕ Clear Filters
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Active Filters Display -->
        <div id="activeFilters" class="active-filters" style="display: none;"></div>
    </div>

    <!-- Results Header -->
    <div class="results-header">
        <div class="results-count">
            <span id="resultsCount">Loading...</span>
        </div>
        <div class="sort-dropdown">
            <label for="sortBy">Sort by:</label>
            <select id="sortBy" class="form-select">
                <option value="date_desc">Newest First</option>
                <option value="date_asc">Oldest First</option>
                <option value="deadline_asc">Deadline (Recent)</option>
                <option value="company_asc">Company (A-Z)</option>
            </select>
        </div>
    </div>

    <!-- Job Listings Container -->
    <div id="jobListings" style="position: relative;">
        <!-- Loading Overlay -->
        <div class="loading-overlay">
            <div class="spinner"></div>
        </div>
        
        <!-- Jobs will be loaded here via AJAX -->
        <div class="row g-4" id="jobsContainer"></div>
    </div>
</div>

<script>
// Archive Page - AJAX Search & Filter (show_expired = true)
(function() {
    'use strict';
    
    // DOM Elements
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const categorySelect = document.getElementById('categorySelect');
    const locationInput = document.getElementById('locationInput');
    const workModeSelect = document.getElementById('workModeSelect');
    const employmentTypeSelect = document.getElementById('employmentTypeSelect');
    const experienceSelect = document.getElementById('experienceSelect');
    const sortBy = document.getElementById('sortBy');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const jobsContainer = document.getElementById('jobsContainer');
    const loadingOverlay = document.querySelector('.loading-overlay');
    const resultsCount = document.getElementById('resultsCount');
    const activeFiltersDiv = document.getElementById('activeFilters');
    
    let searchTimeout;
    let currentFilters = { show_expired: true }; // IMPORTANT: Always show expired jobs
    
    // Initialize
    init();
    
    function init() {
        loadJobs();
        
        searchInput.addEventListener('input', debounceSearch);
        locationInput.addEventListener('input', debounceSearch);
        categorySelect.addEventListener('change', updateFiltersAndLoad);
        workModeSelect.addEventListener('change', updateFiltersAndLoad);
        employmentTypeSelect.addEventListener('change', updateFiltersAndLoad);
        experienceSelect.addEventListener('change', updateFiltersAndLoad);
        sortBy.addEventListener('change', updateFiltersAndLoad);
        clearFiltersBtn.addEventListener('click', clearAllFilters);
        
        readUrlParams();
    }
    
    function debounceSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            updateFiltersAndLoad();
        }, 500);
    }
    
    function updateFiltersAndLoad() {
        currentFilters = {
            search: searchInput.value.trim(),
            category: categorySelect.value,
            location: locationInput.value.trim(),
            work_mode: workModeSelect.value,
            employment_type: employmentTypeSelect.value,
            experience_level: experienceSelect.value,
            sort: sortBy.value,
            show_expired: true // Always true for archive page
        };
        
        updateUrl();
        displayActiveFilters();
        loadJobs();
    }
    
    function loadJobs() {
        loadingOverlay.classList.add('active');
        
        const params = new URLSearchParams(currentFilters);
        
        fetch('api/get_jobs.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                loadingOverlay.classList.remove('active');
                
                const count = data.jobs.length;
                resultsCount.textContent = `${count} archived job${count !== 1 ? 's' : ''} found`;
                
                displayJobs(data.jobs);
            })
            .catch(error => {
                console.error('Error:', error);
                loadingOverlay.classList.remove('active');
                jobsContainer.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            ⚠️ Failed to load archived jobs. Please try again.
                        </div>
                    </div>
                `;
            });
    }
    
    function displayJobs(jobs) {
        if (jobs.length === 0) {
            jobsContainer.innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3>No Archived Jobs Found</h3>
                        <p>No jobs match your search criteria in the archive.</p>
                        <a href="index.php" class="btn btn-primary mt-3">View Active Jobs</a>
                    </div>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        jobs.forEach(job => {
            html += `
                <div class="col-lg-6 col-xl-4">
                    <div class="job-card card archived">
                        <div class="card-body">
                            <div class="company-logo">
                                ${job.company.substring(0, 2).toUpperCase()}
                            </div>
                            
                            <h5 class="card-title">${escapeHtml(job.title)}</h5>
                            <h6 class="card-subtitle">${escapeHtml(job.company)}</h6>
                            
                            <div class="job-info">
                                ${job.work_mode ? `<span class="job-badge job-badge-blue">${job.work_mode_icon || '🏠'} ${escapeHtml(job.work_mode)}</span>` : ''}
                                ${job.employment_type ? `<span class="job-badge job-badge-teal">${job.employment_type_icon || '💼'} ${escapeHtml(job.employment_type)}</span>` : ''}
                                ${job.experience_level ? `<span class="job-badge job-badge-green">${job.experience_level_icon || '🎓'} ${escapeHtml(job.experience_level)}</span>` : ''}
                                ${job.location ? `<span class="job-badge job-badge-purple">📍 ${escapeHtml(job.location)}</span>` : ''}
                                ${job.category ? `<span class="job-badge job-badge-orange">${job.category_icon || '🏷️'} ${escapeHtml(job.category)}</span>` : ''}
                            </div>
                            
                            ${job.description ? `
                                <p class="card-text">
                                    ${escapeHtml(job.description.substring(0, 150))}${job.description.length > 150 ? '...' : ''}
                                </p>
                            ` : ''}
                            
                            <p class="posted-date">
                                🕒 Posted: ${formatDate(job.posted_date)}
                                ${job.application_deadline ? `<br><span class="deadline-expired">⚠️ Deadline: ${formatDate(job.application_deadline)}</span>` : ''}
                            </p>
                            
                            <div class="alert alert-warning py-2 px-3 mb-2" style="font-size: 0.875rem;">
                                <strong>Archive Notice:</strong> This job has expired. Link kept for reference only.
                            </div>
                            
                            <a href="${escapeHtml(job.job_link)}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="btn-apply"
                               style="opacity: 0.7; pointer-events: none; cursor: not-allowed;">
                                Deadline Passed
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        jobsContainer.innerHTML = html;
        animateCards();
    }
    
    function displayActiveFilters() {
        const filters = [];
        
        if (currentFilters.search) filters.push({ key: 'search', label: `Search: "${currentFilters.search}"` });
        if (currentFilters.category) filters.push({ key: 'category', label: `Category: ${categorySelect.options[categorySelect.selectedIndex].text}` });
        if (currentFilters.location) filters.push({ key: 'location', label: `Location: ${currentFilters.location}` });
        if (currentFilters.work_mode) filters.push({ key: 'work_mode', label: workModeSelect.options[workModeSelect.selectedIndex].text });
        if (currentFilters.employment_type) filters.push({ key: 'employment_type', label: employmentTypeSelect.options[employmentTypeSelect.selectedIndex].text });
        if (currentFilters.experience_level) filters.push({ key: 'experience_level', label: experienceSelect.options[experienceSelect.selectedIndex].text });
        
        if (filters.length === 0) {
            activeFiltersDiv.style.display = 'none';
            return;
        }
        
        activeFiltersDiv.style.display = 'flex';
        activeFiltersDiv.innerHTML = filters.map(filter => `
            <div class="filter-pill">
                ${escapeHtml(filter.label)}
                <span class="remove-filter" data-filter="${filter.key}">✕</span>
            </div>
        `).join('');
        
        document.querySelectorAll('.remove-filter').forEach(btn => {
            btn.addEventListener('click', function() {
                removeFilter(this.dataset.filter);
            });
        });
    }
    
    function removeFilter(filterKey) {
        switch(filterKey) {
            case 'search': searchInput.value = ''; break;
            case 'category': categorySelect.value = ''; break;
            case 'location': locationInput.value = ''; break;
            case 'work_mode': workModeSelect.value = ''; break;
            case 'employment_type': employmentTypeSelect.value = ''; break;
            case 'experience_level': experienceSelect.value = ''; break;
        }
        updateFiltersAndLoad();
    }
    
    function clearAllFilters() {
        searchInput.value = '';
        categorySelect.value = '';
        locationInput.value = '';
        workModeSelect.value = '';
        employmentTypeSelect.value = '';
        experienceSelect.value = '';
        sortBy.value = 'date_desc';
        updateFiltersAndLoad();
    }
    
    function updateUrl() {
        const params = new URLSearchParams();
        Object.keys(currentFilters).forEach(key => {
            if (currentFilters[key] && key !== 'show_expired') {
                params.set(key, currentFilters[key]);
            }
        });
        
        const newUrl = params.toString() ? `?${params.toString()}` : window.location.pathname;
        window.history.pushState({}, '', newUrl);
    }
    
    function readUrlParams() {
        const params = new URLSearchParams(window.location.search);
        
        if (params.get('search')) searchInput.value = params.get('search');
        if (params.get('category')) categorySelect.value = params.get('category');
        if (params.get('location')) locationInput.value = params.get('location');
        if (params.get('work_mode')) workModeSelect.value = params.get('work_mode');
        if (params.get('employment_type')) employmentTypeSelect.value = params.get('employment_type');
        if (params.get('experience_level')) experienceSelect.value = params.get('experience_level');
        if (params.get('sort')) sortBy.value = params.get('sort');
        
        updateFiltersAndLoad();
    }
    
    function animateCards() {
        const cards = document.querySelectorAll('.job-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.4s ease';
                card.style.opacity = '0.85';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }
    
    function escapeHtml(text) {
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        return date.toLocaleDateString('en-IN', options);
    }
    
})();
</script>

<?php include 'includes/footer.php'; ?>
