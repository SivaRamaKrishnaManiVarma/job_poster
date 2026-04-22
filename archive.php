<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/master-data-functions.php';

$pageTitle = 'Expired Jobs - Past Opportunities';

// Get master data
$categories = getAllJobCategories($pdo, true);
$workModes = getAllWorkModes($pdo, true);
$employmentTypes = getAllEmploymentTypes($pdo, true);
$experienceLevels = getAllExperienceLevels($pdo, true);

// Get count of expired jobs (capped at 100 for display)
$totalExpiredCount = $pdo->query("
    SELECT COUNT(*) FROM jobs 
    WHERE is_active = 1 
    AND application_deadline IS NOT NULL 
    AND application_deadline < CURDATE()
")->fetchColumn();

include 'includes/header.php';
?>

<style>
/* Expired Page Specific Styles */
.archive-hero {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    padding: 4rem 0;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
}

.archive-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.5;
}

.archive-hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
}

.archive-title {
    font-size: 3rem;
    font-weight: 800;
    color: white;
    margin-bottom: 1rem;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.archive-subtitle {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 1.5rem;
}

.archive-badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    color: white;
    padding: 0.75rem 2rem;
    border-radius: var(--radius-lg);
    font-size: 1.25rem;
    font-weight: 700;
    display: inline-block;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 2rem;
    transition: gap 0.2s;
}

.back-link:hover {
    gap: 0.75rem;
    color: var(--primary-hover);
}

.archive-notice {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 2px solid #ffc107;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    box-shadow: var(--shadow);
}

.archive-notice-icon {
    font-size: 3rem;
    flex-shrink: 0;
}

.archive-notice-content h5 {
    margin: 0 0 0.5rem 0;
    color: #856404;
    font-weight: 700;
    font-size: 1.125rem;
}

.archive-notice-content p {
    margin: 0;
    color: #856404;
    line-height: 1.6;
}

.archive-notice-content a {
    color: var(--primary);
    font-weight: 600;
    text-decoration: underline;
}

/* Archived Job Card Styling */
.job-card.archived {
    position: relative;
    border: 2px solid var(--gray-300);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.job-card.archived::after {
    content: "EXPIRED";
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--gray-600);
    color: white;
    padding: 0.375rem 0.875rem;
    border-radius: var(--radius);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    z-index: 2;
    box-shadow: var(--shadow);
}

.job-card.archived:hover {
    border-color: var(--gray-400);
}

.job-card.archived .company-logo {
    background: linear-gradient(135deg, var(--gray-500), var(--gray-600));
    opacity: 0.8;
}

.job-card.archived .job-card-title {
    color: var(--gray-700);
}

.expired-notice {
    background: #fee;
    border: 1px solid #fcc;
    border-radius: var(--radius);
    padding: 0.75rem 1rem;
    margin-top: 1rem;
    font-size: 0.875rem;
    color: #c33;
}

.expired-notice strong {
    display: block;
    margin-bottom: 0.25rem;
}

.btn-archived {
    background: var(--gray-400);
    cursor: not-allowed;
    opacity: 0.7;
}

.btn-archived:hover {
    background: var(--gray-400);
    transform: none;
    box-shadow: none;
}

.deadline-expired {
    color: var(--danger);
    font-weight: 600;
}

/* Pagination styles */
#loadMoreContainer {
    padding: 2rem 0;
    margin-top: 1rem;
    border-top: 1px solid #eee;
}

#loadMoreBtn {
    transition: all 0.3s ease;
}

#loadMoreBtn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Responsive */
@media (max-width: 767px) {
    .archive-title {
        font-size: 2rem;
    }
    
    .archive-subtitle {
        font-size: 1rem;
    }
    
    .archive-notice {
        flex-direction: column;
        text-align: center;
    }
    
    .archive-notice-icon {
        font-size: 2.5rem;
    }
}
</style>

<div class="container">
    <!-- Back Button -->
    <a href="index.php" class="back-link">
        ← Back to Active Jobs
    </a>

    <!-- Expired Hero -->
    <div class="archive-hero">
        <div class="container">
            <div class="archive-hero-content">
                <h1 class="archive-title"><i class="fas fa-history me-2"></i> Expired Jobs</h1>
                <p class="archive-subtitle">Displaying 100 most recently ended job opportunities</p>
                <div class="archive-badge">
                    <?php echo $totalExpiredCount > 100 ? 'Top 100' : $totalExpiredCount; ?> Expired Jobs
                </div>
            </div>
        </div>
    </div>

    <!-- Expired Notice -->
    <div class="archive-notice">
        <div class="archive-notice-icon">ℹ️</div>
        <div class="archive-notice-content">
            <h5>📌 Reference Only - Applications Closed</h5>
            <p>These jobs have passed their application deadlines and are displayed here for reference purposes only. Visit <a href="index.php">Active Jobs</a> to explore current opportunities and apply.</p>
        </div>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <form id="searchForm">
            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" 
                               name="search" 
                               id="searchInput"
                               class="form-control form-control-lg border-start-0" 
                               placeholder="Search expired jobs..." 
                               autocomplete="off"
                               style="padding-left: 0.5rem;">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="category" id="categorySelect" class="form-select form-select-lg">
                        <option value="">📂 All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                        <input type="text" 
                               name="location" 
                               id="locationInput"
                               class="form-control form-control-lg border-start-0" 
                               placeholder="Location" 
                               autocomplete="off"
                               style="padding-left: 0.5rem;">
                    </div>
                </div>
            </div>
            
            <!-- Advanced Filters -->
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="work_mode" id="workModeSelect" class="form-select">
                        <option value="">🏠 All Work Modes</option>
                        <?php foreach ($workModes as $mode): ?>
                            <option value="<?php echo $mode['id']; ?>">
                                <?php echo htmlspecialchars($mode['mode_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="employment_type" id="employmentTypeSelect" class="form-select">
                        <option value="">💼 All Job Types</option>
                        <?php foreach ($employmentTypes as $type): ?>
                            <option value="<?php echo $type['id']; ?>">
                                <?php echo htmlspecialchars($type['type_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="experience_level" id="experienceSelect" class="form-select">
                        <option value="">🎓 All Experience Levels</option>
                        <?php foreach ($experienceLevels as $level): ?>
                            <option value="<?php echo $level['id']; ?>">
                                <?php echo htmlspecialchars($level['level_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" id="clearFilters" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-2"></i> Clear Filters
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Active Filters Display -->
        <div id="activeFilters" class="active-filters" style="display: none;"></div>
    </div>

    <!-- Results Header -->
    <div class="results-header d-flex justify-content-between align-items-center mb-3">
        <div class="results-count">
            <span id="resultsCount">Loading...</span>
        </div>
        <div class="sort-dropdown d-flex align-items-center">
            <label for="sortBy" class="me-2 text-muted small fw-bold text-uppercase">Sort by:</label>
            <select id="sortBy" class="form-select form-select-sm" style="width: auto;">
                <option value="date_desc">Newest First</option>
                <option value="date_asc">Oldest First</option>
                <option value="deadline_asc">Recent Deadline</option>
                <option value="company_asc">Company (A-Z)</option>
            </select>
        </div>
    </div>

    <!-- Job Listings Container -->
    <div id="jobListings" style="position: relative; min-height: 400px;">
        <!-- Loading Overlay -->
        <div class="loading-overlay">
            <div class="spinner"></div>
            <p class="loading-text">Loading expired jobs...</p>
        </div>
        
        <!-- Jobs will be loaded here via AJAX -->
        <div class="row g-4" id="jobsContainer"></div>

        <!-- Load More Button -->
        <div id="loadMoreContainer" class="text-center" style="display: none;">
            <button id="loadMoreBtn" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-plus me-2"></i> Load More Expired Jobs
            </button>
            <p id="endOfResults" class="text-muted mt-3" style="display: none;">
                <i class="fas fa-info-circle me-1"></i> You've reached the end of the 100 most recently expired jobs.
            </p>
        </div>
    </div>
</div>

<script>
// Expired Jobs Page - AJAX Search & Filter with Lazy Loading
(function() {
    'use strict';
    
    // DOM Elements
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
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const endOfResults = document.getElementById('endOfResults');
    
    let searchTimeout;
    let currentPage = 1;
    const limitPerPage = 25;
    let totalAvailable = 0;
    
    let currentFilters = { 
        show_expired: true,
        page: 1,
        limit: limitPerPage
    };
    
    // Initialize
    init();
    
    function init() {
        loadJobs(false);
        
        searchInput.addEventListener('input', debounceSearch);
        locationInput.addEventListener('input', debounceSearch);
        categorySelect.addEventListener('change', updateFiltersAndLoad);
        workModeSelect.addEventListener('change', updateFiltersAndLoad);
        employmentTypeSelect.addEventListener('change', updateFiltersAndLoad);
        experienceSelect.addEventListener('change', updateFiltersAndLoad);
        sortBy.addEventListener('change', updateFiltersAndLoad);
        clearFiltersBtn.addEventListener('click', clearAllFilters);
        loadMoreBtn.addEventListener('click', loadMore);
        
        readUrlParams();
    }
    
    function debounceSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            updateFiltersAndLoad();
        }, 500);
    }
    
    function updateFiltersAndLoad() {
        currentPage = 1;
        currentFilters = {
            search: searchInput.value.trim(),
            category: categorySelect.value,
            location: locationInput.value.trim(),
            work_mode: workModeSelect.value,
            employment_type: employmentTypeSelect.value,
            experience_level: experienceSelect.value,
            sort: sortBy.value,
            show_expired: true,
            page: currentPage,
            limit: limitPerPage
        };
        
        updateUrl();
        displayActiveFilters();
        loadJobs(false); // false = replace content
    }
    
    function loadMore() {
        currentPage++;
        currentFilters.page = currentPage;
        loadJobs(true); // true = append content
    }
    
    function loadJobs(append = false) {
        if (!append) {
            loadingOverlay.classList.add('active');
            jobsContainer.innerHTML = ''; // Clear for new search
        }
        
        loadMoreBtn.disabled = true;
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Loading...';
        
        const params = new URLSearchParams(currentFilters);
        
        fetch('api/get_jobs.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                loadingOverlay.classList.remove('active');
                
                totalAvailable = data.total;
                const shownSoFar = append ? (jobsContainer.children.length + data.jobs.length) : data.jobs.length;
                
                resultsCount.textContent = `${totalAvailable} expired job${totalAvailable !== 1 ? 's' : ''} found`;
                
                displayJobs(data.jobs, append);
                
                // Update "Load More" visibility
                if (totalAvailable > shownSoFar) {
                    loadMoreContainer.style.display = 'block';
                    loadMoreBtn.style.display = 'inline-block';
                    loadMoreBtn.disabled = false;
                    loadMoreBtn.innerHTML = '<i class="fas fa-plus me-2"></i> Load More Expired Jobs';
                    endOfResults.style.display = 'none';
                } else {
                    loadMoreBtn.style.display = 'none';
                    if (totalAvailable > 0) {
                        loadMoreContainer.style.display = 'block';
                        endOfResults.style.display = 'block';
                    } else {
                        loadMoreContainer.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                loadingOverlay.classList.remove('active');
                if (!append) {
                    jobsContainer.innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <h5 class="alert-heading">⚠️ Failed to Load</h5>
                                <p>We're having trouble loading expired jobs right now.</p>
                                <button class="btn btn-sm btn-primary" onclick="location.reload()">🔄 Retry</button>
                            </div>
                        </div>
                    `;
                }
            });
    }
    
    function displayJobs(jobs, append = false) {
        if (jobs.length === 0 && !append) {
            jobsContainer.innerHTML = `
                <div class="col-12">
                    <div class="empty-state text-center py-5">
                        <div class="empty-state-icon fs-1 mb-3">📭</div>
                        <h3>No Recently Expired Jobs Found</h3>
                        <p class="text-muted">No jobs matching your search criteria were found among the most recent 100 expirations.</p>
                        <a href="index.php" class="btn btn-primary mt-3">← View Active Jobs</a>
                    </div>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        jobs.forEach(job => {
            html += `
                <div class="col-lg-6 col-xl-4 job-item-fade">
                    <div class="job-card archived h-100">
                        <div class="job-card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="company-logo me-3" style="width: 48px; height: 48px; line-height: 48px; text-align: center; border-radius: 8px; font-weight: bold; color: white;">
                                    ${job.company.substring(0, 2).toUpperCase()}
                                </div>
                                <div>
                                    <h5 class="job-card-title mb-0">${escapeHtml(job.title)}</h5>
                                    <h6 class="job-card-subtitle text-muted mb-0 small">${escapeHtml(job.company)}</h6>
                                </div>
                            </div>
                            
                            <div class="job-info mb-3 d-flex flex-wrap gap-2">
                                ${job.work_mode ? `<span class="badge bg-light text-dark border"><i class="fas fa-laptop-house me-1"></i> ${escapeHtml(job.work_mode)}</span>` : ''}
                                ${job.employment_type ? `<span class="badge bg-light text-dark border"><i class="fas fa-briefcase me-1"></i> ${escapeHtml(job.employment_type)}</span>` : ''}
                                ${job.location ? `<span class="badge bg-light text-dark border"><i class="fas fa-map-marker-alt me-1"></i> ${escapeHtml(job.location)}</span>` : ''}
                            </div>
                            
                            <p class="posted-date small text-muted mb-3">
                                <i class="far fa-clock me-1"></i> Expired: ${formatDate(job.application_deadline)}
                            </p>
                            
                            <div class="expired-notice mb-3 p-2 rounded" style="background-color: #fff5f5; border: 1px solid #fed7d7; color: #c53030; font-size: 0.8rem;">
                                <strong><i class="fas fa-ban me-1"></i> Closed</strong> No longer accepting applications.
                            </div>
                            
                            <a href="job-details.php?id=${job.id}" class="btn btn-outline-secondary w-100 btn-sm">
                                View Details (Expired)
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        if (append) {
            jobsContainer.insertAdjacentHTML('beforeend', html);
        } else {
            jobsContainer.innerHTML = html;
        }
        
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
        activeFiltersDiv.style.flexWrap = 'wrap';
        activeFiltersDiv.style.gap = '8px';
        activeFiltersDiv.style.marginBottom = '1rem';
        
        activeFiltersDiv.innerHTML = filters.map(filter => `
            <div class="badge bg-primary d-flex align-items-center p-2" style="font-weight: 500;">
                ${escapeHtml(filter.label)}
                <span class="ms-2 ms-auto" style="cursor: pointer;" onclick="window.removeSearchFilter('${filter.key}')">✕</span>
            </div>
        `).join('');
    }
    
    window.removeSearchFilter = function(filterKey) {
        switch(filterKey) {
            case 'search': searchInput.value = ''; break;
            case 'category': categorySelect.value = ''; break;
            case 'location': locationInput.value = ''; break;
            case 'work_mode': workModeSelect.value = ''; break;
            case 'employment_type': employmentTypeSelect.value = ''; break;
            case 'experience_level': experienceSelect.value = ''; break;
        }
        updateFiltersAndLoad();
    };
    
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
            if (currentFilters[key] && key !== 'show_expired' && key !== 'page' && key !== 'limit') {
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
        
        // Don't trigger another load, init() already calls loadJobs()
    }
    
    function animateCards() {
        const cards = document.querySelectorAll('.job-item-fade:not(.animated)');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(15px)';
            card.classList.add('animated');
            setTimeout(() => {
                card.style.transition = 'all 0.4s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 40);
        });
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        return date.toLocaleDateString('en-IN', options);
    }
    
})();
</script>

<?php include 'includes/footer.php'; ?>
