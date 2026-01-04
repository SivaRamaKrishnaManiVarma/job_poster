<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'Job Portal - Find Your Dream Job';

// Get ONLY categories that exist in database
$categories = $pdo->query("SELECT DISTINCT category FROM jobs WHERE is_active = 1 AND category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$companies = $pdo->query("SELECT COUNT(DISTINCT company) FROM jobs WHERE is_active = 1")->fetchColumn();
$totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE is_active = 1")->fetchColumn();

include 'includes/header.php';
?>

<style>

</style>

<!-- Hero Section -->
<div class="hero-section">
    <div class="text-center">
        <h1>🚀 Find Your Dream Job</h1>
        <p class="lead">Discover amazing career opportunities from top companies</p>
    </div>
</div>

<!-- Info Bar -->
<div class="info-bar">
    <div class="stat">
        <div class="stat-icon">💼</div>
        <div class="stat-text">
            <h4 id="totalJobsCount"><?php echo $totalJobs; ?></h4>
            <p>Active Jobs</p>
        </div>
    </div>
    <div class="stat">
        <div class="stat-icon">🏢</div>
        <div class="stat-text">
            <h4><?php echo count($categories); ?></h4>
            <p>Categories</p>
        </div>
    </div>
    <div class="stat">
        <div class="stat-icon">⚡</div>
        <div class="stat-text">
            <h4><?php echo $companies; ?></h4>
            <p>Companies</p>
        </div>
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
                       placeholder="🔍 Search by job title, company, or keywords..." 
                       autocomplete="off">
            </div>
            <div class="col-md-4">
                <select name="category" id="categorySelect" class="form-select">
                    <option value="">📂 All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" 
                       name="location" 
                       id="locationInput"
                       class="form-control" 
                       placeholder="📍 Location (City, State)" 
                       autocomplete="off">
            </div>
        </div>
        
        <!-- Advanced Filters -->
        <div class="row g-3">
            <div class="col-md-3">
                <select name="work_mode" id="workModeSelect" class="form-select">
                    <option value="">🏠 All Work Modes</option>
                    <option value="Work from Home">🏠 Work from Home</option>
                    <option value="On-site">🏢 On-site</option>
                    <option value="Hybrid">🔄 Hybrid</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="employment_type" id="employmentTypeSelect" class="form-select">
                    <option value="">💼 All Job Types</option>
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Internship">Internship</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="experience_level" id="experienceSelect" class="form-select">
                    <option value="">🎓 All Experience Levels</option>
                    <option value="Freshers">Freshers</option>
                    <option value="0-2 years">0-2 years</option>
                    <option value="2-5 years">2-5 years</option>
                    <option value="5+ years">5+ years</option>
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
        <label for="sortBy" style="font-size: 0.9375rem; color: var(--gray-600);">Sort by:</label>
        <select id="sortBy" class="form-select">
            <option value="date_desc">Newest First</option>
            <option value="date_asc">Oldest First</option>
            <option value="deadline_asc">Deadline (Urgent)</option>
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

<script>
// Job Portal - AJAX Search & Filter
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
    const totalJobsCount = document.getElementById('totalJobsCount');
    
    let searchTimeout;
    let currentFilters = {};
    
    // Initialize
    init();
    
    function init() {
        // Load jobs on page load
        loadJobs();
        
        // Search with debounce (500ms delay)
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                updateFiltersAndLoad();
            }, 500);
        });
        
        // Location search with debounce
        locationInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                updateFiltersAndLoad();
            }, 500);
        });
        
        // Filter changes
        categorySelect.addEventListener('change', updateFiltersAndLoad);
        workModeSelect.addEventListener('change', updateFiltersAndLoad);
        employmentTypeSelect.addEventListener('change', updateFiltersAndLoad);
        experienceSelect.addEventListener('change', updateFiltersAndLoad);
        sortBy.addEventListener('change', updateFiltersAndLoad);
        
        // Clear filters
        clearFiltersBtn.addEventListener('click', clearAllFilters);
        
        // Read URL parameters on load
        readUrlParams();
    }
    
    function updateFiltersAndLoad() {
        currentFilters = {
            search: searchInput.value.trim(),
            category: categorySelect.value,
            location: locationInput.value.trim(),
            work_mode: workModeSelect.value,
            employment_type: employmentTypeSelect.value,
            experience_level: experienceSelect.value,
            sort: sortBy.value
        };
        
        // Update URL without reload
        updateUrl();
        
        // Update active filters display
        displayActiveFilters();
        
        // Load jobs
        loadJobs();
    }
    
    function loadJobs() {
        // Show loading
        loadingOverlay.classList.add('active');
        
        // Build query string
        const params = new URLSearchParams(currentFilters);
        
        // AJAX Request
        fetch('api/get_jobs.php?' + params.toString())
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Hide loading
                loadingOverlay.classList.remove('active');
                
                if (!data.success && data.error) {
                    throw new Error(data.error);
                }
                
                // Update results count
                const count = data.jobs.length;
                resultsCount.textContent = `${count} job${count !== 1 ? 's' : ''} found`;
                totalJobsCount.textContent = data.total || count;
                
                // Display jobs
                displayJobs(data.jobs);
            })
            .catch(error => {
                console.error('Error loading jobs:', error);
                loadingOverlay.classList.remove('active');
                jobsContainer.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger" role="alert">
                            <h5 class="alert-heading">⚠️ Failed to Load Jobs</h5>
                            <p>We're having trouble loading the job listings right now.</p>
                            <hr>
                            <p class="mb-0">
                                <button class="btn btn-sm btn-primary" onclick="location.reload()">🔄 Retry</button>
                                <small class="text-muted ms-3">Error: ${error.message}</small>
                            </p>
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
                        <div class="empty-state-icon">🔍</div>
                        <h3>No Jobs Found</h3>
                        <p>We couldn't find any jobs matching your search criteria. Try adjusting your filters.</p>
                        <button class="btn btn-primary mt-3" onclick="location.reload()">View All Jobs</button>
                    </div>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        jobs.forEach(job => {
            html += `
                <div class="col-lg-6 col-xl-4">
                    <div class="job-card card">
                        <div class="card-body">
                            <!-- Company Logo -->
                            <div class="company-logo">
                                ${job.company.substring(0, 2).toUpperCase()}
                            </div>
                            
                            <h5 class="card-title">${escapeHtml(job.title)}</h5>
                            <h6 class="card-subtitle">${escapeHtml(job.company)}</h6>
                            
                            <!-- Job Info Badges -->
                            <div class="job-info">
                                ${job.work_mode ? `<span class="job-badge job-badge-blue">🏠 ${escapeHtml(job.work_mode)}</span>` : ''}
                                ${job.employment_type ? `<span class="job-badge job-badge-teal">💼 ${escapeHtml(job.employment_type)}</span>` : ''}
                                ${job.experience_level ? `<span class="job-badge job-badge-green">🎓 ${escapeHtml(job.experience_level)}</span>` : ''}
                                ${job.location ? `<span class="job-badge job-badge-purple">📍 ${escapeHtml(job.location)}</span>` : ''}
                                ${job.category ? `<span class="job-badge job-badge-orange">🏷️ ${escapeHtml(job.category)}</span>` : ''}
                            </div>
                            
                            ${job.description ? `
                                <p class="card-text">
                                    ${escapeHtml(job.description.substring(0, 150))}${job.description.length > 150 ? '...' : ''}
                                </p>
                            ` : ''}
                            
                            <p class="posted-date">
                                🕒 Posted on ${formatDate(job.posted_date)}
                                ${job.application_deadline ? getDeadlineHtml(job.application_deadline) : ''}
                            </p>
                            
                            <a href="${escapeHtml(job.job_link)}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="btn-apply">
                                Apply Now →
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        jobsContainer.innerHTML = html;
        
        // Animate cards
        animateCards();
    }
    
    function getDeadlineHtml(deadline) {
        const deadlineDate = new Date(deadline);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const daysLeft = Math.floor((deadlineDate - today) / (1000 * 60 * 60 * 24));
        
        const dateStr = formatDate(deadline);
        
        if (daysLeft < 0) {
            return `<br><span class="deadline-urgent">⚠️ Deadline Passed (${dateStr})</span>`;
        } else if (daysLeft <= 3) {
            return `<br><span class="deadline-urgent">⏰ Last Date: ${dateStr} <span class="badge bg-danger">${daysLeft} day${daysLeft !== 1 ? 's' : ''} left!</span></span>`;
        } else if (daysLeft <= 7) {
            return `<br><span class="deadline-warning">⏰ Last Date: ${dateStr} <span class="badge bg-warning text-dark">${daysLeft} days left</span></span>`;
        } else {
            return `<br><span class="deadline-normal">⏰ Last Date: ${dateStr}</span>`;
        }
    }
    
    function displayActiveFilters() {
        const filters = [];
        
        if (currentFilters.search) {
            filters.push({ key: 'search', label: `Search: "${currentFilters.search}"` });
        }
        if (currentFilters.category) {
            filters.push({ key: 'category', label: `Category: ${currentFilters.category}` });
        }
        if (currentFilters.location) {
            filters.push({ key: 'location', label: `Location: ${currentFilters.location}` });
        }
        if (currentFilters.work_mode) {
            filters.push({ key: 'work_mode', label: `Work Mode: ${currentFilters.work_mode}` });
        }
        if (currentFilters.employment_type) {
            filters.push({ key: 'employment_type', label: `Type: ${currentFilters.employment_type}` });
        }
        if (currentFilters.experience_level) {
            filters.push({ key: 'experience_level', label: `Experience: ${currentFilters.experience_level}` });
        }
        
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
        
        // Add click handlers to remove filters
        document.querySelectorAll('.remove-filter').forEach(btn => {
            btn.addEventListener('click', function() {
                removeFilter(this.dataset.filter);
            });
        });
    }
    
    function removeFilter(filterKey) {
        switch(filterKey) {
            case 'search':
                searchInput.value = '';
                break;
            case 'category':
                categorySelect.value = '';
                break;
            case 'location':
                locationInput.value = '';
                break;
            case 'work_mode':
                workModeSelect.value = '';
                break;
            case 'employment_type':
                employmentTypeSelect.value = '';
                break;
            case 'experience_level':
                experienceSelect.value = '';
                break;
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
            if (currentFilters[key]) {
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
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }
    
    // Utility functions
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
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
