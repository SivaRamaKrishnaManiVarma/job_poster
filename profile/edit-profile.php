<?php
require_once '../includes/session-check.php';
require_once '../includes/master-data-functions.php';

$userId = $candidateId;
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Get all user data
$educations = $pdo->prepare("SELECT * FROM user_education WHERE user_id = ? ORDER BY end_year DESC");
$educations->execute([$userId]);
$educations = $educations->fetchAll(PDO::FETCH_ASSOC);

$experiences = $pdo->prepare("SELECT * FROM user_experience WHERE user_id = ? ORDER BY is_current DESC, end_date DESC");
$experiences->execute([$userId]);
$experiences = $experiences->fetchAll(PDO::FETCH_ASSOC);

$skills = $pdo->prepare("SELECT * FROM user_skills WHERE user_id = ? ORDER BY proficiency_level DESC");
$skills->execute([$userId]);
$skills = $skills->fetchAll(PDO::FETCH_ASSOC);

// Get master data for dropdowns
$workModes = getAllWorkModes($pdo, true);
$employmentTypes = getAllEmploymentTypes($pdo, true);

$pageTitle = 'Edit Profile - Job Portal';
include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1"><i class="fas fa-user-edit me-2"></i>Edit Profile</h2>
                    <p class="text-muted mb-0">Manage your profile information</p>
                </div>
                <a href="<?= url('profile/dashboard.php') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php
                    switch($success) {
                        case 'profile_updated': echo 'Profile updated successfully!'; break;
                        case 'resume_uploaded': echo 'Resume uploaded successfully!'; break;
                        case 'education_added': echo 'Education added successfully!'; break;
                        case 'education_deleted': echo 'Education deleted successfully!'; break;
                        case 'experience_added': echo 'Experience added successfully!'; break;
                        case 'experience_deleted': echo 'Experience deleted successfully!'; break;
                        case 'skill_added': echo 'Skill added successfully!'; break;
                        case 'skill_deleted': echo 'Skill deleted successfully!'; break;
                        default: echo htmlspecialchars($success);
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Tabbed Interface -->
            <div class="card border-0 shadow-sm">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                        <i class="fas fa-user me-2"></i>Basic Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="resume-tab" data-bs-toggle="tab" data-bs-target="#resume" type="button" role="tab">
                        <i class="fas fa-file-pdf me-2"></i>Resume
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="education-tab" data-bs-toggle="tab" data-bs-target="#education" type="button" role="tab">
                        <i class="fas fa-graduation-cap me-2"></i>Education
                        <span class="badge bg-primary rounded-pill ms-1"><?= count($educations) ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="experience-tab" data-bs-toggle="tab" data-bs-target="#experience" type="button" role="tab">
                        <i class="fas fa-briefcase me-2"></i>Experience
                        <span class="badge bg-success rounded-pill ms-1"><?= count($experiences) ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="skills-tab" data-bs-toggle="tab" data-bs-target="#skills" type="button" role="tab">
                        <i class="fas fa-code me-2"></i>Skills
                        <span class="badge bg-info rounded-pill ms-1"><?= count($skills) ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                        <i class="fas fa-heart me-2"></i>Job Interests
                        <?php 
                        // Get preference count
                        $prefCountStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_job_preferences WHERE user_id = ?");
                        $prefCountStmt->execute([$userId]);
                        $prefCount = $prefCountStmt->fetch()['cnt'];
                        ?>
                        <span class="badge bg-danger rounded-pill ms-1"><?= $prefCount ?></span>
                    </button>
                </li>
                </ul>


                <!-- Tab Content -->
                <div class="card-body">
                    <div class="tab-content" id="profileTabsContent">
                        
                        <!-- ===== TAB 1: BASIC INFO ===== -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <form method="POST" action="<?= url('api/update-profile.php') ?>" enctype="multipart/form-data">
                                <div class="row">
                                    <!-- Profile Photo -->
                                    <div class="col-md-12 mb-4">
                                        <div class="text-center">
                                            <div class="mb-3">
                                                <?php if (!empty($currentUser['profile_photo'])): ?>
                                                    <img src="<?= url('uploads/profile_photos/' . htmlspecialchars($currentUser['profile_photo'])) ?>" 
                                                         id="profilePhotoPreview"
                                                         class="rounded-circle border" 
                                                         width="120" height="120" 
                                                         style="object-fit: cover;"
                                                         alt="Profile Photo">
                                                <?php else: ?>
                                                    <div id="profilePhotoPreview" class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                                         style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold;">
                                                        <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <label for="profile_photo" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-camera me-1"></i>Change Photo
                                            </label>
                                            <input type="file" id="profile_photo" name="profile_photo" class="d-none" accept="image/jpeg,image/png,image/jpg">
                                            <p class="small text-muted mt-2 mb-0">Max 2MB, JPG/PNG only</p>
                                        </div>
                                    </div>

                                    <!-- Full Name -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="full_name" class="form-control" 
                                               value="<?= htmlspecialchars($currentUser['full_name']) ?>" required>
                                    </div>

                                    <!-- Email (Read-only) -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Email Address</label>
                                        <input type="email" class="form-control" 
                                               value="<?= htmlspecialchars($currentUser['email']) ?>" readonly disabled>
                                        <small class="text-muted">Email cannot be changed</small>
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Phone Number</label>
                                        <input type="tel" name="phone" class="form-control" 
                                               pattern="[0-9]{10}" maxlength="10"
                                               value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" 
                                               placeholder="10-digit mobile number">
                                    </div>

                                    <!-- Location -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Location</label>
                                        <input type="text" name="location" class="form-control" 
                                               value="<?= htmlspecialchars($currentUser['location'] ?? '') ?>" 
                                               placeholder="e.g., Mumbai, Maharashtra">
                                    </div>

                                    <!-- Bio -->
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">Bio / About Me</label>
                                        <textarea name="bio" class="form-control" rows="4" 
                                                  placeholder="Tell us about yourself, your career goals, achievements..."><?= htmlspecialchars($currentUser['bio'] ?? '') ?></textarea>
                                        <small class="text-muted">This will be visible to recruiters</small>
                                    </div>

                                    <!-- Social Links -->
                                    <div class="col-12 mb-3">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-link me-2"></i>Social Links (Optional)</h6>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">LinkedIn URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fab fa-linkedin"></i></span>
                                            <input type="url" name="linkedin_url" class="form-control" 
                                                   value="<?= htmlspecialchars($currentUser['linkedin_url'] ?? '') ?>" 
                                                   placeholder="https://linkedin.com/in/username">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">GitHub URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fab fa-github"></i></span>
                                            <input type="url" name="github_url" class="form-control" 
                                                   value="<?= htmlspecialchars($currentUser['github_url'] ?? '') ?>" 
                                                   placeholder="https://github.com/username">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Portfolio URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                            <input type="url" name="portfolio_url" class="form-control" 
                                                   value="<?= htmlspecialchars($currentUser['portfolio_url'] ?? '') ?>" 
                                                   placeholder="https://yourportfolio.com">
                                        </div>
                                    </div>

                                    <!-- Career Preferences -->
                                    <div class="col-12 mb-3 mt-3">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-briefcase me-2"></i>Career Preferences</h6>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Current Company</label>
                                        <input type="text" name="current_company" class="form-control" 
                                               value="<?= htmlspecialchars($currentUser['current_company'] ?? '') ?>" 
                                               placeholder="Current employer">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Current Designation</label>
                                        <input type="text" name="current_designation" class="form-control" 
                                               value="<?= htmlspecialchars($currentUser['current_designation'] ?? '') ?>" 
                                               placeholder="Current role">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Total Experience (Years)</label>
                                        <input type="number" name="total_experience_years" class="form-control" 
                                               value="<?= htmlspecialchars($currentUser['total_experience_years'] ?? '0') ?>" 
                                               min="0" max="50">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Preferred Work Mode</label>
                                        <select name="preferred_work_mode_id" class="form-select">
                                            <option value="">Select...</option>
                                            <?php foreach ($workModes as $mode): ?>
                                                <option value="<?= $mode['id'] ?>" 
                                                    <?= $currentUser['preferred_work_mode_id'] == $mode['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($mode['mode_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Preferred Job Type</label>
                                        <select name="preferred_job_type_id" class="form-select">
                                            <option value="">Select...</option>
                                            <?php foreach ($employmentTypes as $type): ?>
                                                <option value="<?= $type['id'] ?>" 
                                                    <?= $currentUser['preferred_job_type_id'] == $type['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($type['type_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Expected Salary (Min) ₹</label>
                                        <input type="number" name="expected_salary_min" class="form-control" 
                                               value="<?= htmlspecialchars($currentUser['expected_salary_min'] ?? '') ?>" 
                                               placeholder="e.g., 500000" step="1000">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Expected Salary (Max) ₹</label>
                                        <input type="number" name="expected_salary_max" class="form-control" 
                                               value="<?= htmlspecialchars($currentUser['expected_salary_max'] ?? '') ?>" 
                                               placeholder="e.g., 800000" step="1000">
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-save me-2"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- ===== TAB 2: RESUME ===== -->
                        <div class="tab-pane fade" id="resume" role="tabpanel">
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <?php if (!empty($currentUser['resume_path'])): ?>
                                        <!-- Current Resume -->
                                        <div class="card border-0 bg-light mb-4">
                                            <div class="card-body p-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="mb-1"><?= basename($currentUser['resume_path']) ?></h5>
                                                        <p class="text-muted mb-2">
                                                            Uploaded on <?= date('M d, Y', strtotime($currentUser['updated_at'])) ?>
                                                        </p>
                                                        <div class="btn-group">
                                                            <a href="<?= url('uploads/resumes/' . htmlspecialchars($currentUser['resume_path'])) ?>" 
                                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye me-1"></i>View
                                                            </a>
                                                            <a href="<?= url('uploads/resumes/' . htmlspecialchars($currentUser['resume_path'])) ?>" 
                                                               download class="btn btn-sm btn-outline-secondary">
                                                                <i class="fas fa-download me-1"></i>Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Upload Form -->
                                    <div class="card border-2 border-dashed">
                                        <div class="card-body p-4 text-center">
                                            <form method="POST" action="<?= url('api/upload-resume.php') ?>" enctype="multipart/form-data" id="resumeForm">
                                                <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                                                <h5 class="mb-2"><?= !empty($currentUser['resume_path']) ? 'Update Resume' : 'Upload Resume' ?></h5>
                                                <p class="text-muted mb-4">Upload your latest resume in PDF format (Max 5MB)</p>
                                                
                                                <input type="file" name="resume" id="resumeInput" class="d-none" accept=".pdf" required>
                                                <label for="resumeInput" class="btn btn-primary mb-3">
                                                    <i class="fas fa-upload me-2"></i>Choose PDF File
                                                </label>
                                                <div id="fileName" class="text-muted small mb-3"></div>
                                                
                                                <button type="submit" id="uploadBtn" class="btn btn-success" style="display: none;">
                                                    <i class="fas fa-check me-2"></i>Upload Resume
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="alert alert-info mt-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Tips for a great resume:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Keep it updated with latest experience</li>
                                            <li>Use clear section headings</li>
                                            <li>Highlight key achievements</li>
                                            <li>Keep it to 1-2 pages</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== TAB 3: EDUCATION ===== -->
                        <div class="tab-pane fade" id="education" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Education History</h5>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEducationModal">
                                    <i class="fas fa-plus me-2"></i>Add Education
                                </button>
                            </div>

                            <?php if (empty($educations)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No education records added yet</h5>
                                    <p class="text-muted">Add your educational qualifications to improve your profile</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEducationModal">
                                        <i class="fas fa-plus me-2"></i>Add Your First Education
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($educations as $edu): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($edu['degree']) ?></h6>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteEducation(<?= $edu['id'] ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <p class="text-primary mb-2"><?= htmlspecialchars($edu['institution']) ?></p>
                                                    <?php if (!empty($edu['field_of_study'])): ?>
                                                        <p class="text-muted small mb-2">
                                                            <i class="fas fa-book me-1"></i><?= htmlspecialchars($edu['field_of_study']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <p class="text-muted small mb-2">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?= htmlspecialchars($edu['start_year']) ?> - 
                                                        <?= $edu['is_current'] ? '<span class="badge bg-success">Current</span>' : htmlspecialchars($edu['end_year']) ?>
                                                    </p>
                                                    <?php if (!empty($edu['percentage_cgpa'])): ?>
                                                        <p class="text-muted small mb-0">
                                                            <i class="fas fa-award me-1"></i>Grade: <?= htmlspecialchars($edu['percentage_cgpa']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- ===== TAB 4: EXPERIENCE ===== -->
                        <div class="tab-pane fade" id="experience" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Work Experience</h5>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                                    <i class="fas fa-plus me-2"></i>Add Experience
                                </button>
                            </div>

                            <?php if (empty($experiences)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No work experience added yet</h5>
                                    <p class="text-muted">Add your professional experience to showcase your career journey</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                                        <i class="fas fa-plus me-2"></i>Add Your First Experience
                                    </button>
                                </div>
                            <?php else: ?>
                                <?php foreach ($experiences as $exp): ?>
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h5 class="fw-bold mb-1">
                                                        <?= htmlspecialchars($exp['designation']) ?>
                                                        <?php if ($exp['is_current']): ?>
                                                            <span class="badge bg-success ms-2">Current</span>
                                                        <?php endif; ?>
                                                    </h5>
                                                    <h6 class="text-primary mb-2"><?= htmlspecialchars($exp['company_name']) ?></h6>
                                                    <p class="text-muted small mb-2">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?= date('M Y', strtotime($exp['start_date'])) ?> - 
                                                        <?= $exp['is_current'] ? 'Present' : date('M Y', strtotime($exp['end_date'])) ?>
                                                        <span class="ms-3">
                                                            <i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($exp['employment_type']) ?>
                                                        </span>
                                                        <?php if (!empty($exp['location'])): ?>
                                                            <span class="ms-3">
                                                                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($exp['location']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </p>
                                                    <?php if (!empty($exp['description'])): ?>
                                                        <p class="mb-0 small"><?= nl2br(htmlspecialchars($exp['description'])) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <button class="btn btn-sm btn-outline-danger ms-2" 
                                                        onclick="deleteExperience(<?= $exp['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- ===== TAB 5: SKILLS ===== -->
                        <div class="tab-pane fade" id="skills" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0"><i class="fas fa-code me-2"></i>Skills & Expertise</h5>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                                    <i class="fas fa-plus me-2"></i>Add Skill
                                </button>
                            </div>

                            <?php if (empty($skills)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-code fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No skills added yet</h5>
                                    <p class="text-muted">Add your technical and soft skills to highlight your expertise</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                                        <i class="fas fa-plus me-2"></i>Add Your First Skill
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php 
                                    $proficiencyColors = [
                                        'Beginner' => 'secondary',
                                        'Intermediate' => 'info',
                                        'Advanced' => 'primary',
                                        'Expert' => 'success'
                                    ];
                                    foreach ($skills as $skill): 
                                        $color = $proficiencyColors[$skill['proficiency_level']] ?? 'secondary';
                                    ?>
                                        <div class="col-md-4 col-sm-6 mb-3">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($skill['skill_name']) ?></h6>
                                                        <span class="badge bg-<?= $color ?>"><?= htmlspecialchars($skill['proficiency_level']) ?></span>
                                                        <?php if ($skill['years_of_experience'] > 0): ?>
                                                            <small class="text-muted d-block mt-1">
                                                                <?= $skill['years_of_experience'] ?> year<?= $skill['years_of_experience'] > 1 ? 's' : '' ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteSkill(<?= $skill['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- ===== TAB 6: JOB PREFERENCES ===== -->
                        <div class="tab-pane fade" id="preferences" role="tabpanel">
                            <div class="alert alert-info border-0 mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-info-circle fa-2x me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1 fw-bold">Get Personalized Job Recommendations</h6>
                                        <p class="mb-0 small">Select your preferred job categories and locations. We'll show you relevant opportunities based on your interests!</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Preferences -->
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-danger me-2"></i>Location Preferences</h6>
                                    <form method="POST" action="<?= url('api/update-location-preferences.php') ?>">
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label fw-semibold">Preferred Work Locations</label>
                                                <input type="text" name="preferred_locations" class="form-control" 
                                                    value="<?= htmlspecialchars($currentUser['preferred_locations'] ?? '') ?>"
                                                    placeholder="e.g., Mumbai, Pune, Bangalore (comma-separated)">
                                                <small class="text-muted">Enter cities where you'd like to work</small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold">Willing to Relocate?</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" name="willing_to_relocate" 
                                                        value="1" id="willing_to_relocate"
                                                        <?= $currentUser['willing_to_relocate'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="willing_to_relocate">
                                                        Yes, I'm open to relocation
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="job_alert_email" 
                                                        value="1" id="job_alert_email"
                                                        <?= $currentUser['job_alert_email'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="job_alert_email">
                                                        <i class="fas fa-bell me-1"></i>Send me email alerts for matching jobs
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-2"></i>Save Location Preferences
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Job Category Interests -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-heart text-danger me-2"></i>Interested Job Categories</h6>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#addPreferenceModal">
                                    <i class="fas fa-plus me-2"></i>Add Interest
                                </button>
                            </div>

                            <?php
                            // Get user's current preferences
                            $preferencesStmt = $pdo->prepare("
                                SELECT ujp.*, mc.category_name, mc.icon 
                                FROM user_job_preferences ujp
                                JOIN master_job_categories mc ON ujp.job_category_id = mc.id
                                WHERE ujp.user_id = ?
                                ORDER BY ujp.priority DESC, mc.category_name ASC
                            ");
                            $preferencesStmt->execute([$userId]);
                            $userPreferences = $preferencesStmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>

                            <?php if (empty($userPreferences)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-heart fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No job interests added yet</h5>
                                    <p class="text-muted mb-4">Add your preferred job categories to get personalized recommendations</p>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addPreferenceModal">
                                        <i class="fas fa-plus me-2"></i>Add Your First Interest
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php 
                                    $priorityColors = [
                                        3 => ['color' => 'danger', 'text' => 'High Priority'],
                                        2 => ['color' => 'warning', 'text' => 'Medium Priority'],
                                        1 => ['color' => 'info', 'text' => 'Low Priority']
                                    ];
                                    foreach ($userPreferences as $pref): 
                                        $priorityInfo = $priorityColors[$pref['priority']] ?? $priorityColors[1];
                                    ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="mb-2 fw-bold">
                                                                <i class="<?= htmlspecialchars($pref['icon']) ?> me-2"></i>
                                                                <?= htmlspecialchars($pref['category_name']) ?>
                                                            </h6>
                                                            <span class="badge bg-<?= $priorityInfo['color'] ?> rounded-pill">
                                                                <?= $priorityInfo['text'] ?>
                                                            </span>
                                                        </div>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="deletePreference(<?= $pref['id'] ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>Added <?= date('M d, Y', strtotime($pref['created_at'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Matching Jobs Preview -->
                                <div class="card border-0 bg-light mt-4">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3">
                                            <i class="fas fa-lightbulb text-warning me-2"></i>Jobs Matching Your Interests
                                        </h6>
                                        <?php
                                        // Get count of matching jobs
                                        $categoryIds = array_column($userPreferences, 'job_category_id');
                                        $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
                                        
                                        $matchingJobsStmt = $pdo->prepare("
                                            SELECT COUNT(*) as cnt FROM jobs 
                                            WHERE is_active = 1 
                                            AND job_category_id IN ($placeholders)
                                            AND (application_deadline IS NULL OR application_deadline >= CURDATE())
                                        ");
                                        $matchingJobsStmt->execute($categoryIds);
                                        $matchingCount = $matchingJobsStmt->fetch()['cnt'];
                                        ?>
                                        
                                        <p class="mb-3">
                                            We found <strong class="text-success"><?= $matchingCount ?> active job<?= $matchingCount != 1 ? 's' : '' ?></strong> 
                                            matching your interests!
                                        </p>
                                        
                                        <?php if ($matchingCount > 0): ?>
                                            <a href="<?= url('profile/recommended-jobs.php') ?>" class="btn btn-success">
                                                <i class="fas fa-search me-2"></i>View Recommended Jobs
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODALS ===== -->

<!-- Add Education Modal -->
<div class="modal fade" id="addEducationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= url('api/add-education.php') ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-graduation-cap me-2"></i>Add Education</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Degree/Qualification <span class="text-danger">*</span></label>
                            <input type="text" name="degree" class="form-control" placeholder="e.g., B.Tech, MBA" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Institution/University <span class="text-danger">*</span></label>
                            <input type="text" name="institution" class="form-control" placeholder="e.g., IIT Delhi" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Field of Study</label>
                            <input type="text" name="field_of_study" class="form-control" placeholder="e.g., Computer Science">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Grade/Percentage/CGPA</label>
                            <input type="text" name="percentage_cgpa" class="form-control" placeholder="e.g., 8.5 CGPA, 85%">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Start Year <span class="text-danger">*</span></label>
                            <input type="number" name="start_year" class="form-control" min="1980" max="2030" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">End Year</label>
                            <input type="number" name="end_year" class="form-control" id="edu_end_year" min="1980" max="2030">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_current" value="1" id="edu_is_current">
                                <label class="form-check-label" for="edu_is_current">Currently Studying</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Education
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Experience Modal -->
<div class="modal fade" id="addExperienceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= url('api/add-experience.php') ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-briefcase me-2"></i>Add Work Experience</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Job Title/Designation <span class="text-danger">*</span></label>
                            <input type="text" name="designation" class="form-control" placeholder="e.g., Software Engineer" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" placeholder="e.g., Google India" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Employment Type</label>
                            <select name="employment_type" class="form-select">
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Internship">Internship</option>
                                <option value="Freelance">Freelance</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g., Bangalore, Karnataka">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-semibold">End Date</label>
                            <input type="date" name="end_date" class="form-control" id="exp_end_date">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_current" value="1" id="exp_is_current">
                                <label class="form-check-label" for="exp_is_current">Current</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">Job Description</label>
                            <textarea name="description" class="form-control" rows="4" 
                                      placeholder="Describe your responsibilities, achievements, and key projects..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Experience
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Skill Modal -->
<div class="modal fade" id="addSkillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('api/add-skill.php') ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-code me-2"></i>Add Skill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Skill Name <span class="text-danger">*</span></label>
                        <input type="text" name="skill_name" class="form-control" 
                               placeholder="e.g., Python, Communication, Project Management" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Proficiency Level</label>
                        <select name="proficiency_level" class="form-select">
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate" selected>Intermediate</option>
                            <option value="Advanced">Advanced</option>
                            <option value="Expert">Expert</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Years of Experience</label>
                        <input type="number" name="years_of_experience" class="form-control" 
                               min="0" max="50" value="0" placeholder="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Add Skill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Add Job Preference Modal -->
<div class="modal fade" id="addPreferenceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('api/add-job-preference.php') ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-heart me-2"></i>Add Job Interest</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Job Category <span class="text-danger">*</span></label>
                        <select name="job_category_id" class="form-select" required>
                            <option value="">Select a category...</option>
                            <?php
                            // Get all available categories
                            $allCategories = getAllJobCategories($pdo, true);
                            // Get user's existing preferences
                            $existingPrefs = array_column($userPreferences ?? [], 'job_category_id');
                            
                            foreach ($allCategories as $category):
                                // Skip if already added
                                if (in_array($category['id'], $existingPrefs)) continue;
                            ?>
                                <option value="<?= $category['id'] ?>">
                                    <?= htmlspecialchars($category['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Priority Level</label>
                        <select name="priority" class="form-select">
                            <option value="3">High Priority - Very interested</option>
                            <option value="2" selected>Medium Priority - Interested</option>
                            <option value="1">Low Priority - Somewhat interested</option>
                        </select>
                        <small class="text-muted">Higher priority categories will be shown first in recommendations</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-heart me-2"></i>Add Interest
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Delete preference function
function deletePreference(id) {
    if (confirm('Remove this job category from your interests?')) {
        window.location.href = `<?= url('api/delete-job-preference.php') ?>?id=${id}`;
    }
}
</script>

<!-- JavaScript -->
<script>
// Profile photo preview
document.getElementById('profile_photo').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('profilePhotoPreview');
            preview.outerHTML = `<img src="${event.target.result}" id="profilePhotoPreview" class="rounded-circle border" width="120" height="120" style="object-fit: cover;" alt="Profile Photo">`;
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});

// Resume file name display
document.getElementById('resumeInput').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    if (fileName) {
        document.getElementById('fileName').textContent = `Selected: ${fileName}`;
        document.getElementById('uploadBtn').style.display = 'inline-block';
    }
});

// Education: Disable end year if currently studying
document.getElementById('edu_is_current').addEventListener('change', function() {
    document.getElementById('edu_end_year').disabled = this.checked;
    if (this.checked) document.getElementById('edu_end_year').value = '';
});

// Experience: Disable end date if currently working
document.getElementById('exp_is_current').addEventListener('change', function() {
    document.getElementById('exp_end_date').disabled = this.checked;
    if (this.checked) document.getElementById('exp_end_date').value = '';
});

// Delete functions
function deleteEducation(id) {
    if (confirm('Are you sure you want to delete this education record?')) {
        window.location.href = `<?= url('api/delete-education.php') ?>?id=${id}`;
    }
}

function deleteExperience(id) {
    if (confirm('Are you sure you want to delete this work experience?')) {
        window.location.href = `<?= url('api/delete-experience.php') ?>?id=${id}`;
    }
}

function deleteSkill(id) {
    if (confirm('Are you sure you want to delete this skill?')) {
        window.location.href = `<?= url('api/delete-skill.php') ?>?id=${id}`;
    }
}
</script>

<style>
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
}
.nav-tabs .nav-link:hover {
    border-bottom-color: #dee2e6;
}
.nav-tabs .nav-link.active {
    color: #6366f1;
    border-bottom-color: #6366f1;
    font-weight: 600;
}
.border-dashed {
    border-style: dashed !important;
}
.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}
.input-group .form-control {
    border-left: none;
}
</style>

<?php include '../includes/footer.php'; ?>
