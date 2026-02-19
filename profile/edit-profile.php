<?php
require_once '../includes/session-check.php';
require_once '../includes/master-data-functions.php';

$userId = $candidateId;
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Get user data
$educations = $pdo->prepare("SELECT * FROM user_education WHERE user_id = ? ORDER BY end_year DESC");
$educations->execute([$userId]);
$educations = $educations->fetchAll(PDO::FETCH_ASSOC);

$experiences = $pdo->prepare("SELECT * FROM user_experience WHERE user_id = ? ORDER BY is_current DESC, end_date DESC");
$experiences->execute([$userId]);
$experiences = $experiences->fetchAll(PDO::FETCH_ASSOC);

$skills = $pdo->prepare("SELECT * FROM user_skills WHERE user_id = ? ORDER BY proficiency_level DESC");
$skills->execute([$userId]);
$skills = $skills->fetchAll(PDO::FETCH_ASSOC);

$preferences = $pdo->prepare("
    SELECT ujp.*, mc.category_name, mc.icon 
    FROM user_job_preferences ujp
    JOIN master_job_categories mc ON ujp.job_category_id = mc.id
    WHERE ujp.user_id = ?
    ORDER BY ujp.priority DESC
");
$preferences->execute([$userId]);
$preferences = $preferences->fetchAll(PDO::FETCH_ASSOC);

// Get master data
$workModes = getAllWorkModes($pdo, true);
$employmentTypes = getAllEmploymentTypes($pdo, true);
$jobCategories = getAllJobCategories($pdo, true);

$pageTitle = 'Edit Profile - Job Portal';
include '../includes/header.php';
?>

<style>
body {
    background-color: #f5f7fa;
}
.profile-card {
    background: white;
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
}
.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #3b82f6;
}
.help-box {
    background: #eff6ff;
    border-left: 3px solid #3b82f6;
    padding: 12px 16px;
    margin-bottom: 20px;
    border-radius: 4px;
    font-size: 14px;
    color: #1e40af;
}
.field-hint {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: #6b7280;
}
.field-hint i {
    margin-right: 4px;
}
.completion-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    margin-right: 8px;
    margin-bottom: 8px;
}
.badge-complete {
    background: #d1fae5;
    color: #065f46;
}
.badge-incomplete {
    background: #fee2e2;
    color: #991b1b;
}
.upload-box {
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    background: #f9fafb;
    cursor: pointer;
    transition: all 0.3s;
}
.upload-box:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}
.upload-box.uploading {
    border-color: #3b82f6;
    background: #eff6ff;
}
.btn-primary-custom {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s;
}
.btn-primary-custom:hover {
    background: #2563eb;
    color: white;
}
.btn-primary-custom:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}
.item-box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.2s;
}
.item-box:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}
.empty-state i {
    font-size: 3rem;
    margin-bottom: 16px;
    color: #d1d5db;
}
.input-valid {
    border-color: #10b981 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2310b981' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(.375em + .1875rem) center;
    background-size: calc(.75em + .375rem) calc(.75em + .375rem);
}
.input-invalid {
    border-color: #ef4444 !important;
}
.validation-message {
    font-size: 12px;
    margin-top: 4px;
}
.validation-message.valid {
    color: #10b981;
}
.validation-message.invalid {
    color: #ef4444;
}
.char-counter {
    font-size: 12px;
    color: #6b7280;
    text-align: right;
}
.char-counter.warning {
    color: #f59e0b;
}
.char-counter.danger {
    color: #ef4444;
}
.skill-tag {
    display: inline-flex;
    align-items: center;
    background: #3b82f6;
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    margin: 4px;
    font-size: 14px;
}
.skill-tag .remove-skill {
    margin-left: 8px;
    cursor: pointer;
    opacity: 0.8;
}
.skill-tag .remove-skill:hover {
    opacity: 1;
}
.priority-selector {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}
.priority-option {
    flex: 1;
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}
.priority-option:hover {
    border-color: #3b82f6;
}
.priority-option.selected {
    border-color: #3b82f6;
    background: #eff6ff;
}
.progress-bar-custom {
    height: 8px;
    border-radius: 4px;
    background: #e5e7eb;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: #3b82f6;
    transition: width 0.3s ease;
}
</style>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Complete Your Profile</h2>
            <p class="text-muted mb-0">Fill in your details to get better job matches</p>
        </div>
        <a href="<?= url('profile/dashboard.php') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <!-- Alerts -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php
            $messages = [
                'profile_updated' => 'Profile updated successfully!',
                'resume_uploaded' => 'Resume uploaded successfully!',
                'education_updated' => 'Education saved successfully!',
                'experience_added' => 'Experience added!',
                'experience_deleted' => 'Experience removed.',
                'skill_added' => 'Skill added!',
                'skill_deleted' => 'Skill removed.',
                'preference_added' => 'Job interest added!',
                'preference_deleted' => 'Job interest removed.',
                'location_preferences_updated' => 'Preferences saved!'
            ];
            echo $messages[$success] ?? 'Saved successfully!';
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Profile Completion -->
    <div class="profile-card">
        <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Profile Completion</h5>
        <div class="progress-bar-custom mb-3">
            <?php 
            $completion = getCandidateProfileCompletion($pdo, $userId);
            ?>
            <div class="progress-fill" style="width: <?= $completion ?>%"></div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted"><?= $completion ?>% Complete</span>
            <?php if ($completion < 100): ?>
                <span class="text-muted small">Complete all sections to boost your profile</span>
            <?php else: ?>
                <span class="text-success small"><i class="fas fa-check-circle"></i> Your profile is complete!</span>
            <?php endif; ?>
        </div>
        <div>
            <span class="completion-badge <?= !empty($currentUser['full_name']) ? 'badge-complete' : 'badge-incomplete' ?>">
                <i class="fas fa-<?= !empty($currentUser['full_name']) ? 'check' : 'times' ?>"></i> Basic Info
            </span>
            <span class="completion-badge <?= !empty($currentUser['resume_path']) ? 'badge-complete' : 'badge-incomplete' ?>">
                <i class="fas fa-<?= !empty($currentUser['resume_path']) ? 'check' : 'times' ?>"></i> Resume
            </span>
            <span class="completion-badge <?= count($educations) > 0 ? 'badge-complete' : 'badge-incomplete' ?>">
                <i class="fas fa-<?= count($educations) > 0 ? 'check' : 'times' ?>"></i> Education
            </span>
            <span class="completion-badge <?= count($experiences) > 0 ? 'badge-complete' : 'badge-incomplete' ?>">
                <i class="fas fa-<?= count($experiences) > 0 ? 'check' : 'times' ?>"></i> Experience
            </span>
            <span class="completion-badge <?= count($skills) > 0 ? 'badge-complete' : 'badge-incomplete' ?>">
                <i class="fas fa-<?= count($skills) > 0 ? 'check' : 'times' ?>"></i> Skills
            </span>
            <span class="completion-badge <?= count($preferences) > 0 ? 'badge-complete' : 'badge-incomplete' ?>">
                <i class="fas fa-<?= count($preferences) > 0 ? 'check' : 'times' ?>"></i> Interests
            </span>
        </div>
    </div>

    <!-- BASIC INFO -->
    <div class="profile-card" id="basic">
        <h4 class="section-title">
            <i class="fas fa-user me-2 text-primary"></i>Basic Information
        </h4>

        <div class="help-box">
            <i class="fas fa-info-circle me-2"></i>
            This information helps employers contact you. Make sure your phone number and location are accurate.
        </div>

        <form method="POST" action="<?= url('api/update-profile.php') ?>" enctype="multipart/form-data" id="basic-info-form">
            <div class="row">
                <!-- Profile Photo -->
                <div class="col-12 mb-4 text-center">
                    <?php if (!empty($currentUser['profile_photo'])): ?>
                        <img src="<?= url('uploads/profile_photos/' . $currentUser['profile_photo']) ?>" 
                             class="rounded-circle mb-3" 
                             width="100" height="100" 
                             style="object-fit: cover; border: 3px solid #e5e7eb;"
                             id="preview-img">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                             style="width: 100px; height: 100px; border: 3px solid #e5e7eb;"
                             id="preview-container">
                            <i class="fas fa-user fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <label for="profile_photo" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-camera me-1"></i>Upload Photo
                        </label>
                        <input type="file" id="profile_photo" name="profile_photo" 
                               accept="image/jpeg,image/jpg,image/png" class="d-none" 
                               onchange="previewAndValidatePhoto(this)">
                        <p class="field-hint mt-2">
                            <i class="fas fa-info-circle"></i>JPG or PNG • Max 2MB • Square photo works best
                        </p>
                    </div>
                </div>

                <!-- Full Name -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control" 
                           value="<?= htmlspecialchars($currentUser['full_name'] ?? '') ?>" 
                           placeholder="Enter your complete legal name" 
                           required 
                           minlength="3"
                           maxlength="100"
                           oninput="validateName(this)">
                    <span class="field-hint">
                        <i class="fas fa-lightbulb"></i>Use your full name as it appears on official documents
                    </span>
                    <div id="name-validation" class="validation-message"></div>
                </div>

                <!-- Phone -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">+91</span>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" 
                               placeholder="10-digit mobile number" 
                               pattern="[6-9][0-9]{9}"
                               maxlength="10"
                               required
                               oninput="validatePhone(this)">
                    </div>
                    <span class="field-hint">
                        <i class="fas fa-phone"></i>Enter your active mobile number (employers will call/WhatsApp you)
                    </span>
                    <div id="phone-validation" class="validation-message"></div>
                </div>

                <!-- Location -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Current Location</label>
                    <input type="text" name="location" class="form-control" 
                           value="<?= htmlspecialchars($currentUser['location'] ?? '') ?>" 
                           placeholder="e.g., Mumbai, Maharashtra"
                           list="indian-cities"
                           oninput="validateLocation(this)">
                    <datalist id="indian-cities">
                        <option value="Mumbai, Maharashtra">
                        <option value="Delhi, NCR">
                        <option value="Bangalore, Karnataka">
                        <option value="Hyderabad, Telangana">
                        <option value="Chennai, Tamil Nadu">
                        <option value="Kolkata, West Bengal">
                        <option value="Pune, Maharashtra">
                        <option value="Ahmedabad, Gujarat">
                        <option value="Jaipur, Rajasthan">
                        <option value="Lucknow, Uttar Pradesh">
                    </datalist>
                    <span class="field-hint">
                        <i class="fas fa-map-marker-alt"></i>City where you currently live (helps find nearby jobs)
                    </span>
                </div>

                <!-- Email (readonly) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" class="form-control" 
                           value="<?= htmlspecialchars($currentUser['email']) ?>" 
                           disabled>
                    <span class="field-hint text-muted">
                        <i class="fas fa-lock"></i>Email cannot be changed (used for login)
                    </span>
                </div>

                <!-- About -->
                <div class="col-12 mb-3">
                    <label class="form-label fw-semibold">About You (Professional Summary)</label>
                    <textarea name="bio" class="form-control" rows="4" 
                              maxlength="500"
                              placeholder="Write a brief professional summary (e.g., Experienced software developer with 5 years in web development...)"
                              oninput="updateCharCounter(this, 500, 'bio-counter')"><?= htmlspecialchars($currentUser['bio'] ?? '') ?></textarea>
                    <div class="d-flex justify-content-between">
                        <span class="field-hint">
                            <i class="fas fa-edit"></i>Highlight your key skills and career goals (shown to employers)
                        </span>
                        <span class="char-counter" id="bio-counter">
                            <?= strlen($currentUser['bio'] ?? '') ?>/500
                        </span>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="col-12 mb-4">
                    <label class="form-label fw-semibold mb-3">
                        <i class="fas fa-link me-2"></i>Social Profiles (Optional but Recommended)
                    </label>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-linkedin text-primary"></i></span>
                                <input type="url" name="linkedin_url" class="form-control" 
                                       value="<?= htmlspecialchars($currentUser['linkedin_url'] ?? '') ?>" 
                                       placeholder="LinkedIn profile URL"
                                       oninput="validateURL(this, 'linkedin.com')">
                            </div>
                            <span class="field-hint">
                                <i class="fas fa-info-circle"></i>e.g., linkedin.com/in/yourname
                            </span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-github"></i></span>
                                <input type="url" name="github_url" class="form-control" 
                                       value="<?= htmlspecialchars($currentUser['github_url'] ?? '') ?>" 
                                       placeholder="GitHub profile URL"
                                       oninput="validateURL(this, 'github.com')">
                            </div>
                            <span class="field-hint">
                                <i class="fas fa-info-circle"></i>e.g., github.com/yourusername
                            </span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                <input type="url" name="portfolio_url" class="form-control" 
                                       value="<?= htmlspecialchars($currentUser['portfolio_url'] ?? '') ?>" 
                                       placeholder="Portfolio website URL"
                                       oninput="validateURL(this, '')">
                            </div>
                            <span class="field-hint">
                                <i class="fas fa-info-circle"></i>Your personal website or portfolio
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">
                        <i class="fas fa-briefcase me-2 text-primary"></i>Current Employment Status
                    </h6>
                </div>

                <!-- Current Job -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Current Company</label>
                    <input type="text" name="current_company" class="form-control" 
                           value="<?= htmlspecialchars($currentUser['current_company'] ?? '') ?>" 
                           placeholder="e.g., TCS, Infosys, Google">
                    <span class="field-hint">
                        <i class="fas fa-building"></i>Leave blank if not currently employed
                    </span>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Current Job Title</label>
                    <input type="text" name="current_designation" class="form-control" 
                           value="<?= htmlspecialchars($currentUser['current_designation'] ?? '') ?>" 
                           placeholder="e.g., Senior Developer, Marketing Manager">
                    <span class="field-hint">
                        <i class="fas fa-id-badge"></i>Your current role/position
                    </span>
                </div>

                <!-- Experience Years -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Total Work Experience</label>
                    <select name="total_experience_years" class="form-select">
                        <option value="0" <?= ($currentUser['total_experience_years'] ?? 0) == 0 ? 'selected' : '' ?>>Fresher (No experience)</option>
                        <?php for ($i = 1; $i <= 30; $i++): ?>
                            <option value="<?= $i ?>" <?= ($currentUser['total_experience_years'] ?? 0) == $i ? 'selected' : '' ?>>
                                <?= $i ?> year<?= $i > 1 ? 's' : '' ?>
                            </option>
                        <?php endfor; ?>
                        <option value="30" <?= ($currentUser['total_experience_years'] ?? 0) > 30 ? 'selected' : '' ?>>30+ years</option>
                    </select>
                    <span class="field-hint">
                        <i class="fas fa-clock"></i>Total years of professional work experience
                    </span>
                </div>

                <!-- Work Mode -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Preferred Work Mode</label>
                    <select name="preferred_work_mode_id" class="form-select">
                        <option value="">No preference (open to all)</option>
                        <?php foreach ($workModes as $mode): ?>
                            <option value="<?= $mode['id'] ?>" 
                                    <?= ($currentUser['preferred_work_mode_id'] ?? '') == $mode['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mode['mode_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-hint">
                        <i class="fas fa-laptop-house"></i>How you prefer to work (Office/Remote/Hybrid)
                    </span>
                </div>

                <div class="col-12 mb-3">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">
                        <i class="fas fa-rupee-sign me-2 text-success"></i>Salary Expectations
                    </h6>
                </div>

                <!-- Salary Range -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Minimum Expected Salary (₹/year)</label>
                    <input type="number" name="expected_salary_min" class="form-control" 
                           value="<?= htmlspecialchars($currentUser['expected_salary_min'] ?? '') ?>" 
                           placeholder="e.g., 300000"
                           min="0"
                           step="10000"
                           oninput="validateSalary()">
                    <span class="field-hint">
                        <i class="fas fa-info-circle"></i>Minimum salary you're willing to accept per year
                    </span>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Maximum Expected Salary (₹/year)</label>
                    <input type="number" name="expected_salary_max" class="form-control" 
                           value="<?= htmlspecialchars($currentUser['expected_salary_max'] ?? '') ?>" 
                           placeholder="e.g., 500000"
                           min="0"
                           step="10000"
                           oninput="validateSalary()">
                    <span class="field-hint">
                        <i class="fas fa-info-circle"></i>Maximum salary you're targeting per year
                    </span>
                    <div id="salary-validation" class="validation-message"></div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn-primary-custom" id="save-basic-btn">
                    <i class="fas fa-save me-2"></i>Save Basic Information
                </button>
            </div>
        </form>
    </div>

    <!-- RESUME UPLOAD -->
    <div class="profile-card" id="resume">
        <h4 class="section-title">
            <i class="fas fa-file-pdf me-2 text-danger"></i>Your Resume
        </h4>

        <div class="help-box">
            <i class="fas fa-info-circle me-2"></i>
            Upload your latest resume in PDF format. A good resume increases your chances of getting hired!
        </div>

        <?php if (!empty($currentUser['resume_path'])): ?>
            <!-- Existing Resume -->
            <div class="alert alert-success border-0">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                        <div>
                            <h6 class="mb-0"><?= basename($currentUser['resume_path']) ?></h6>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>Uploaded: <?= date('M d, Y', strtotime($currentUser['updated_at'])) ?>
                            </small>
                        </div>
                    </div>
                    <div>
                        <a href="<?= url('uploads/resumes/' . $currentUser['resume_path']) ?>" 
                           target="_blank" class="btn btn-sm btn-outline-primary me-2 mb-2">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="<?= url('uploads/resumes/' . $currentUser['resume_path']) ?>" 
                           download class="btn btn-sm btn-outline-success me-2 mb-2">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-warning mb-2" 
                                onclick="showResumeUpload()">
                            <i class="fas fa-upload"></i> Replace
                        </button>
                    </div>
                </div>
            </div>
            <form id="resume-form" method="POST" action="<?= url('api/upload-resume.php') ?>" 
                  enctype="multipart/form-data" style="display: none;">
        <?php else: ?>
            <form id="resume-form" method="POST" action="<?= url('api/upload-resume.php') ?>" 
                  enctype="multipart/form-data">
        <?php endif; ?>
            <div class="upload-box" id="upload-box" onclick="document.getElementById('resume-file').click()">
                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3" id="upload-icon"></i>
                <h6 class="mb-2" id="upload-text">Click to upload your resume</h6>
                <p class="text-muted small mb-0" id="upload-hint">
                    PDF only • Maximum 5MB • Include your latest work experience
                </p>
                <input type="file" id="resume-file" name="resume" 
                       accept=".pdf,application/pdf" class="d-none" 
                       onchange="handleResumeUpload(this)">
            </div>
            <div id="file-info" class="mt-3 d-none">
                <div class="alert alert-info">
                    <i class="fas fa-file-pdf me-2"></i>
                    <span id="file-name"></span> 
                    <span id="file-size" class="text-muted ms-2"></span>
                </div>
            </div>
        </form>
    </div>

    <!-- EDUCATION (Highest Qualification Only) -->
    <div class="profile-card" id="education">
        <h4 class="section-title">
            <i class="fas fa-graduation-cap me-2 text-success"></i>Highest Education
        </h4>

        <div class="help-box">
            <i class="fas fa-info-circle me-2"></i>
            Add your highest educational qualification. This helps employers understand your academic background.
        </div>

        <?php 
        // Get highest education (most recent)
        $highestEdu = !empty($educations) ? $educations[0] : null;
        ?>

        <form method="POST" action="<?= url('api/update-education.php') ?>" id="education-form">
            <div class="row">
                <!-- Degree -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Degree/Qualification <span class="text-danger">*</span></label>
                    <select name="degree" class="form-select" required onchange="handleDegreeChange(this)">
                        <option value="">Select your highest degree</option>
                        <option value="Ph.D" <?= ($highestEdu['degree'] ?? '') == 'Ph.D' ? 'selected' : '' ?>>Ph.D (Doctorate)</option>
                        <option value="Master's Degree" <?= ($highestEdu['degree'] ?? '') == "Master's Degree" ? 'selected' : '' ?>>Master's Degree (M.Tech/MBA/M.Sc)</option>
                        <option value="Bachelor's Degree" <?= ($highestEdu['degree'] ?? '') == "Bachelor's Degree" ? 'selected' : '' ?>>Bachelor's Degree (B.Tech/BE/BBA/B.Sc)</option>
                        <option value="Diploma" <?= ($highestEdu['degree'] ?? '') == 'Diploma' ? 'selected' : '' ?>>Diploma (Polytechnic)</option>
                        <option value="12th Grade" <?= ($highestEdu['degree'] ?? '') == '12th Grade' ? 'selected' : '' ?>>12th Grade (Senior Secondary)</option>
                        <option value="10th Grade" <?= ($highestEdu['degree'] ?? '') == '10th Grade' ? 'selected' : '' ?>>10th Grade (Secondary)</option>
                        <option value="Other" <?= !in_array($highestEdu['degree'] ?? '', ['Ph.D', "Master's Degree", "Bachelor's Degree", 'Diploma', '12th Grade', '10th Grade']) && !empty($highestEdu['degree']) ? 'selected' : '' ?>>Other</option>
                    </select>
                    <span class="field-hint">
                        <i class="fas fa-graduation-cap"></i>Select your highest completed education level
                    </span>
                </div>

                <!-- Custom Degree (if Other selected) -->
                <div class="col-md-6 mb-3" id="custom-degree-field" style="display: <?= !empty($highestEdu['degree']) && !in_array($highestEdu['degree'], ['Ph.D', "Master's Degree", "Bachelor's Degree", 'Diploma', '12th Grade', '10th Grade']) ? 'block' : 'none' ?>;">
                    <label class="form-label fw-semibold">Specify Degree <span class="text-danger">*</span></label>
                    <input type="text" name="custom_degree" class="form-control" 
                           value="<?= !in_array($highestEdu['degree'] ?? '', ['Ph.D', "Master's Degree", "Bachelor's Degree", 'Diploma', '12th Grade', '10th Grade']) ? htmlspecialchars($highestEdu['degree'] ?? '') : '' ?>"
                           placeholder="Enter your degree name">
                    <span class="field-hint">
                        <i class="fas fa-edit"></i>e.g., B.Pharma, BCA, MCA, etc.
                    </span>
                </div>

                <!-- Institution -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Institution/University <span class="text-danger">*</span></label>
                    <input type="text" name="institution" class="form-control" 
                           value="<?= htmlspecialchars($highestEdu['institution'] ?? '') ?>"
                           placeholder="e.g., Mumbai University" 
                           required>
                    <span class="field-hint">
                        <i class="fas fa-university"></i>Name of your college/university/school
                    </span>
                </div>

                <!-- Field of Study -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Field of Study/Specialization</label>
                    <input type="text" name="field_of_study" class="form-control" 
                           value="<?= htmlspecialchars($highestEdu['field_of_study'] ?? '') ?>"
                           placeholder="e.g., Computer Science, Mechanical Engineering">
                    <span class="field-hint">
                        <i class="fas fa-book"></i>Your major/stream/specialization (if applicable)
                    </span>
                </div>

                <!-- Start Year -->
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Start Year <span class="text-danger">*</span></label>
                    <select name="start_year" id="start_year" class="form-select" required onchange="validateEducationYears()">
                        <option value="">Select year</option>
                        <?php 
                        $currentYear = date('Y');
                        for ($year = $currentYear; $year >= 1960; $year--): 
                        ?>
                            <option value="<?= $year ?>" <?= ($highestEdu['start_year'] ?? '') == $year ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <span class="field-hint">
                        <i class="fas fa-calendar-alt"></i>When did you start?
                    </span>
                </div>

                <!-- End Year -->
                <div class="col-md-4 mb-3" id="end-year-field">
                    <label class="form-label fw-semibold">End Year / Expected</label>
                    <select name="end_year" id="end_year" class="form-select" onchange="validateEducationYears()">
                        <option value="">Select year</option>
                        <?php 
                        $futureYear = $currentYear + 6; // Allow 6 years in future for long courses
                        for ($year = $futureYear; $year >= 1960; $year--): 
                        ?>
                            <option value="<?= $year ?>" <?= ($highestEdu['end_year'] ?? '') == $year ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <span class="field-hint">
                        <i class="fas fa-calendar-check"></i>When did/will you complete?
                    </span>
                    <div id="year-error" class="validation-message invalid d-none">
                        <i class="fas fa-exclamation-circle"></i> End year must be after start year
                    </div>
                </div>

                <!-- Currently Studying -->
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold d-block">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="is_current" 
                               value="1" id="is_current" 
                               <?= ($highestEdu['is_current'] ?? 0) ? 'checked' : '' ?>
                               onchange="toggleEndYear(this)">
                        <label class="form-check-label" for="is_current">
                            <i class="fas fa-clock me-1"></i>Currently studying
                        </label>
                    </div>
                    <span class="field-hint">
                        <i class="fas fa-info-circle"></i>Check if you're still pursuing this degree
                    </span>
                </div>

                <!-- Percentage/CGPA -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Percentage / CGPA / Grade</label>
                    <input type="text" name="percentage_cgpa" class="form-control" 
                           value="<?= htmlspecialchars($highestEdu['percentage_cgpa'] ?? '') ?>"
                           placeholder="e.g., 8.5 CGPA or 85% or First Class">
                    <span class="field-hint">
                        <i class="fas fa-award"></i>Your academic score/grade
                    </span>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn-primary-custom" id="save-education-btn">
                    <i class="fas fa-save me-2"></i>Save Education
                </button>
            </div>
        </form>
    </div>

    <!-- EXPERIENCE -->
    <div class="profile-card" id="experience">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="section-title mb-0" style="border: none; padding: 0;">
                <i class="fas fa-briefcase me-2 text-primary"></i>Work Experience
            </h4>
            <button class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                <i class="fas fa-plus me-1"></i>Add Experience
            </button>
        </div>

        <div class="help-box">
            <i class="fas fa-info-circle me-2"></i>
            List your work experience, internships, or projects. Start with your most recent role. Add at least one for better job matches!
        </div>

        <?php if (empty($experiences)): ?>
            <div class="empty-state">
                <i class="fas fa-briefcase"></i>
                <p class="mb-3">No work experience added yet</p>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                    <i class="fas fa-plus me-1"></i>Add Your First Experience
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($experiences as $exp): ?>
                <div class="item-box">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1">
                                <?= htmlspecialchars($exp['designation']) ?>
                                <?php if ($exp['is_current']): ?>
                                    <span class="badge bg-success ms-2">
                                        <i class="fas fa-circle-dot"></i> Current
                                    </span>
                                <?php endif; ?>
                            </h6>
                            <p class="text-muted mb-1">
                                <i class="fas fa-building me-1"></i><?= htmlspecialchars($exp['company_name']) ?>
                            </p>
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('M Y', strtotime($exp['start_date'])) ?> - 
                                <?= $exp['is_current'] ? 'Present' : date('M Y', strtotime($exp['end_date'])) ?>
                                <?php
                                $start = new DateTime($exp['start_date']);
                                $end = $exp['is_current'] ? new DateTime() : new DateTime($exp['end_date']);
                                $diff = $start->diff($end);
                                $duration = '';
                                if ($diff->y > 0) $duration .= $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ';
                                if ($diff->m > 0) $duration .= $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
                                ?>
                                <span class="text-success">
                                    <i class="fas fa-clock ms-2 me-1"></i><?= trim($duration) ?>
                                </span>
                            </small>
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($exp['employment_type']) ?>
                                <?php if (!empty($exp['location'])): ?>
                                    <i class="fas fa-map-marker-alt ms-2 me-1"></i><?= htmlspecialchars($exp['location']) ?>
                                <?php endif; ?>
                            </small>
                            <?php if (!empty($exp['description'])): ?>
                                <p class="small mb-0 text-muted">
                                    <?= nl2br(htmlspecialchars(substr($exp['description'], 0, 200))) ?>
                                    <?= strlen($exp['description']) > 200 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" 
                                onclick="if(confirm('Delete this experience? This cannot be undone.')) window.location.href='<?= url('api/delete-experience.php?id=' . $exp['id']) ?>'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- SKILLS -->
    <div class="profile-card" id="skills">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="section-title mb-0" style="border: none; padding: 0;">
                <i class="fas fa-code me-2 text-info"></i>Skills
            </h4>
            <button class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                <i class="fas fa-plus me-1"></i>Add Skill
            </button>
        </div>

        <div class="help-box">
            <i class="fas fa-info-circle me-2"></i>
            Add skills relevant to your target job. Include technical skills (e.g., PHP, Java), tools (e.g., Excel, Photoshop), and soft skills (e.g., Communication, Leadership).
        </div>

        <?php if (empty($skills)): ?>
            <div class="empty-state">
                <i class="fas fa-code"></i>
                <p class="mb-3">No skills added yet</p>
                <p class="text-muted small mb-3">Add at least 5 skills to improve your profile visibility</p>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                    <i class="fas fa-plus me-1"></i>Add Your First Skill
                </button>
            </div>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php 
                $proficiencyColors = [
                    'Expert' => 'success',
                    'Advanced' => 'primary',
                    'Intermediate' => 'info',
                    'Beginner' => 'secondary'
                ];
                foreach ($skills as $skill): 
                    $color = $proficiencyColors[$skill['proficiency_level']] ?? 'secondary';
                ?>
                    <div class="skill-tag bg-<?= $color ?>">
                        <span><?= htmlspecialchars($skill['skill_name']) ?></span>
                        <?php if (!empty($skill['proficiency_level'])): ?>
                            <small class="ms-1 opacity-75">(<?= htmlspecialchars($skill['proficiency_level']) ?>)</small>
                        <?php endif; ?>
                        <?php if (!empty($skill['years_of_experience']) && $skill['years_of_experience'] > 0): ?>
                            <small class="ms-1 opacity-75">• <?= $skill['years_of_experience'] ?>y</small>
                        <?php endif; ?>
                        <span class="remove-skill" 
                              onclick="if(confirm('Remove <?= htmlspecialchars($skill['skill_name']) ?>?')) window.location.href='<?= url('api/delete-skill.php?id=' . $skill['id']) ?>'">
                            <i class="fas fa-times-circle"></i>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="text-muted small mt-3 mb-0">
                <i class="fas fa-lightbulb me-1"></i>
                <strong>Total skills:</strong> <?= count($skills) ?> 
                <?php if (count($skills) < 5): ?>
                    • Add <?= 5 - count($skills) ?> more for better profile visibility
                <?php else: ?>
                    • Great! Your profile looks strong
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- JOB INTERESTS -->
    <div class="profile-card" id="preferences">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="section-title mb-0" style="border: none; padding: 0;">
                <i class="fas fa-heart me-2 text-danger"></i>Job Interests
            </h4>
            <button class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addPreferenceModal">
                <i class="fas fa-plus me-1"></i>Add Interest
            </button>
        </div>

        <div class="help-box">
            <i class="fas fa-info-circle me-2"></i>
            Select job categories you're interested in. We'll send you personalized job recommendations and email alerts based on these preferences!
        </div>

        <!-- Location Preferences -->
        <div class="mb-4 p-3 bg-light rounded">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-map-marker-alt me-2"></i>Location & Alert Preferences
            </h6>
            <form method="POST" action="<?= url('api/update-location-preferences.php') ?>">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-semibold">Preferred Work Locations</label>
                        <input type="text" name="preferred_locations" class="form-control" 
                               value="<?= htmlspecialchars($currentUser['preferred_locations'] ?? '') ?>"
                               placeholder="e.g., Mumbai, Pune, Bangalore">
                        <span class="field-hint">
                            <i class="fas fa-info-circle"></i>Comma-separated city names where you want to work
                        </span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Options</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="willing_to_relocate" 
                                   value="1" id="relocate" <?= $currentUser['willing_to_relocate'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="relocate">
                                <i class="fas fa-plane me-1"></i>Willing to relocate
                            </label>
                        </div>
                        <span class="field-hint">Open to moving to other cities</span>
                        
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="job_alert_email" 
                                   value="1" id="alerts" <?= $currentUser['job_alert_email'] ? 'checked' : '' ?>>
                            <!-- <label class="form-check-label" for="alerts">
                                <i class="fas fa-envelope me-1"></i>Email job alerts  #Hiding for now not implemented
                            </label> -->
                        </div>
                        <span class="field-hint">Get daily job recommendations via email</span>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-sm btn-primary-custom">
                            <i class="fas fa-save me-1"></i>Save Preferences
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Job Category Interests -->
        <?php if (empty($preferences)): ?>
            <div class="empty-state">
                <i class="fas fa-heart"></i>
                <p class="mb-3">No job interests added yet</p>
                <p class="text-muted small mb-3">Add at least 2-3 interests to get personalized job recommendations</p>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPreferenceModal">
                    <i class="fas fa-plus me-1"></i>Add Your First Interest
                </button>
            </div>
        <?php else: ?>
            <div class="row">
                <?php 
                $priorityInfo = [
                    3 => ['color' => 'danger', 'text' => 'High Priority', 'icon' => 'fas fa-fire'],
                    2 => ['color' => 'warning', 'text' => 'Medium Priority', 'icon' => 'fas fa-star'],
                    1 => ['color' => 'info', 'text' => 'Low Priority', 'icon' => 'fas fa-star-half-alt']
                ];
                foreach ($preferences as $pref): 
                    $info = $priorityInfo[$pref['priority']] ?? $priorityInfo[1];
                ?>
                    <div class="col-md-4 mb-3">
                        <div class="item-box h-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-2">
                                        <i class="<?= htmlspecialchars($pref['icon']) ?> me-2"></i>
                                        <?= htmlspecialchars($pref['category_name']) ?>
                                    </h6>
                                    <span class="badge bg-<?= $info['color'] ?>">
                                        <i class="<?= $info['icon'] ?> me-1"></i><?= $info['text'] ?>
                                    </span>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="if(confirm('Remove this interest?')) window.location.href='<?= url('api/delete-job-preference.php?id=' . $pref['id']) ?>'">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="text-muted small mt-2 mb-0">
                <i class="fas fa-lightbulb me-1"></i>
                <strong>Tip:</strong> Higher priority categories will get more job recommendations in your feed and emails.
            </p>
        <?php endif; ?>
    </div>

</div>

<!-- MODALS -->

<!-- Add Experience Modal -->
<div class="modal fade" id="addExperienceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= url('api/add-experience.php') ?>" id="experience-form">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-briefcase me-2"></i>Add Work Experience</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Job Title <span class="text-danger">*</span></label>
                            <input type="text" name="designation" class="form-control" 
                                   placeholder="e.g., Software Engineer" required>
                            <span class="field-hint">Your role/position at the company</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" 
                                   placeholder="e.g., TCS, Infosys, Google" required>
                            <span class="field-hint">Name of the organization</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Employment Type <span class="text-danger">*</span></label>
                            <select name="employment_type" class="form-select" required>
                                <option value="">Select type</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Internship">Internship</option>
                                <option value="Freelance">Freelance</option>
                            </select>
                            <span class="field-hint">Type of employment</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Location</label>
                            <input type="text" name="location" class="form-control" 
                                   placeholder="e.g., Mumbai" list="indian-cities">
                            <span class="field-hint">City where you worked</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" 
                                   id="exp_start_date" 
                                   max="<?= date('Y-m-d') ?>"
                                   required 
                                   onchange="validateExperienceDates()">
                            <span class="field-hint">When did you start?</span>
                        </div>
                        <div class="col-md-6 mb-3" id="exp-end-date-field">
                            <label class="form-label fw-semibold">End Date</label>
                            <input type="date" name="end_date" class="form-control" 
                                   id="exp_end_date"
                                   max="<?= date('Y-m-d') ?>"
                                   onchange="validateExperienceDates()">
                            <span class="field-hint">When did you end?</span>
                            <div id="exp-date-error" class="validation-message invalid d-none">
                                End date must be after start date
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_current" 
                                       value="1" id="exp_current" onchange="toggleExpEndDate(this)">
                                <label class="form-check-label" for="exp_current">
                                    <i class="fas fa-circle-dot me-1"></i>I currently work here
                                </label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">Job Description / Responsibilities</label>
                            <textarea name="description" class="form-control" rows="4" 
                                      maxlength="1000"
                                      placeholder="Describe your key responsibilities and achievements..."
                                      oninput="updateCharCounter(this, 1000, 'exp-desc-counter')"></textarea>
                            <div class="d-flex justify-content-between">
                                <span class="field-hint">
                                    <i class="fas fa-info-circle"></i>List your main duties and accomplishments
                                </span>
                                <span class="char-counter" id="exp-desc-counter">0/1000</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom" id="save-exp-btn">
                        <i class="fas fa-plus me-1"></i>Add Experience
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
                               placeholder="e.g., PHP, JavaScript, Project Management" 
                               required
                               list="common-skills">
                        <datalist id="common-skills">
                            <option value="PHP">
                            <option value="JavaScript">
                            <option value="Python">
                            <option value="Java">
                            <option value="React">
                            <option value="Node.js">
                            <option value="SQL">
                            <option value="HTML/CSS">
                            <option value="Communication">
                            <option value="Leadership">
                            <option value="Project Management">
                            <option value="Microsoft Excel">
                            <option value="Problem Solving">
                        </datalist>
                        <span class="field-hint">
                            <i class="fas fa-info-circle"></i>Enter the skill name (typing shows suggestions)
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Proficiency Level</label>
                        <select name="proficiency_level" class="form-select">
                            <option value="Beginner">Beginner - Just started learning</option>
                            <option value="Intermediate" selected>Intermediate - Can work independently</option>
                            <option value="Advanced">Advanced - Can mentor others</option>
                            <option value="Expert">Expert - Industry recognized expertise</option>
                        </select>
                        <span class="field-hint">
                            <i class="fas fa-chart-line"></i>How would you rate your proficiency?
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Years of Experience</label>
                        <input type="number" name="years_of_experience" class="form-control" 
                               placeholder="0" min="0" max="50" value="0">
                        <span class="field-hint">
                            <i class="fas fa-clock"></i>How many years have you worked with this skill?
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom">
                        <i class="fas fa-plus me-1"></i>Add Skill
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
                            $existingPrefs = array_column($preferences ?? [], 'job_category_id');
                            foreach ($jobCategories as $category):
                                if (in_array($category['id'], $existingPrefs)) continue;
                            ?>
                                <option value="<?= $category['id'] ?>">
                                    <?= htmlspecialchars($category['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="field-hint">
                            <i class="fas fa-info-circle"></i>Which job category interests you?
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Priority Level</label>
                        <div class="priority-selector">
                            <div class="priority-option" onclick="selectPriority(this, 3)">
                                <input type="radio" name="priority" value="3" class="d-none">
                                <i class="fas fa-fire fa-2x text-danger mb-2"></i>
                                <div class="fw-bold">High</div>
                                <small class="text-muted">Very interested</small>
                            </div>
                            <div class="priority-option selected" onclick="selectPriority(this, 2)">
                                <input type="radio" name="priority" value="2" class="d-none" checked>
                                <i class="fas fa-star fa-2x text-warning mb-2"></i>
                                <div class="fw-bold">Medium</div>
                                <small class="text-muted">Interested</small>
                            </div>
                            <div class="priority-option" onclick="selectPriority(this, 1)">
                                <input type="radio" name="priority" value="1" class="d-none">
                                <i class="fas fa-star-half-alt fa-2x text-info mb-2"></i>
                                <div class="fw-bold">Low</div>
                                <small class="text-muted">Somewhat interested</small>
                            </div>
                        </div>
                        <span class="field-hint">
                            <i class="fas fa-info-circle"></i>Higher priority = More job recommendations
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-custom">
                        <i class="fas fa-heart me-1"></i>Add Interest
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Character counter
function updateCharCounter(textarea, maxChars, counterId) {
    const counter = document.getElementById(counterId);
    const length = textarea.value.length;
    counter.textContent = length + '/' + maxChars;
    
    counter.classList.remove('warning', 'danger');
    if (length > maxChars * 0.9) {
        counter.classList.add('danger');
    } else if (length > maxChars * 0.7) {
        counter.classList.add('warning');
    }
}

// Validate name
function validateName(input) {
    const value = input.value.trim();
    const validation = document.getElementById('name-validation');
    
    if (value.length < 3) {
        validation.textContent = 'Name must be at least 3 characters';
        validation.className = 'validation-message invalid';
        input.classList.remove('input-valid');
        input.classList.add('input-invalid');
    } else if (!/^[a-zA-Z\s.]+$/.test(value)) {
        validation.textContent = 'Name can only contain letters and spaces';
        validation.className = 'validation-message invalid';
        input.classList.remove('input-valid');
        input.classList.add('input-invalid');
    } else {
        validation.textContent = 'Looks good!';
        validation.className = 'validation-message valid';
        input.classList.remove('input-invalid');
        input.classList.add('input-valid');
    }
}

// Validate phone
function validatePhone(input) {
    const value = input.value;
    const validation = document.getElementById('phone-validation');
    
    if (value.length === 0) {
        validation.textContent = '';
        input.classList.remove('input-valid', 'input-invalid');
    } else if (!/^[6-9]/.test(value)) {
        validation.textContent = 'Indian mobile numbers start with 6, 7, 8, or 9';
        validation.className = 'validation-message invalid';
        input.classList.add('input-invalid');
        input.classList.remove('input-valid');
    } else if (value.length < 10) {
        validation.textContent = 'Enter 10 digits';
        validation.className = 'validation-message invalid';
        input.classList.add('input-invalid');
        input.classList.remove('input-valid');
    } else if (value.length === 10) {
        validation.textContent = 'Valid phone number!';
        validation.className = 'validation-message valid';
        input.classList.add('input-valid');
        input.classList.remove('input-invalid');
    } else {
        validation.textContent = 'Too many digits';
        validation.className = 'validation-message invalid';
        input.classList.add('input-invalid');
        input.classList.remove('input-valid');
    }
}

// Validate location
function validateLocation(input) {
    if (input.value.trim().length > 0) {
        input.classList.add('input-valid');
    } else {
        input.classList.remove('input-valid');
    }
}

// Validate URL
function validateURL(input, domain) {
    if (input.value.trim().length === 0) {
        input.classList.remove('input-valid', 'input-invalid');
        return;
    }
    
    try {
        new URL(input.value);
        if (domain && !input.value.includes(domain)) {
            input.classList.add('input-invalid');
            input.classList.remove('input-valid');
        } else {
            input.classList.add('input-valid');
            input.classList.remove('input-invalid');
        }
    } catch (e) {
        input.classList.add('input-invalid');
        input.classList.remove('input-valid');
    }
}

// Validate salary range
function validateSalary() {
    const minInput = document.querySelector('input[name="expected_salary_min"]');
    const maxInput = document.querySelector('input[name="expected_salary_max"]');
    const validation = document.getElementById('salary-validation');
    
    const min = parseInt(minInput.value) || 0;
    const max = parseInt(maxInput.value) || 0;
    
    if (min > 0 && max > 0 && max < min) {
        validation.textContent = 'Maximum salary must be greater than minimum';
        validation.className = 'validation-message invalid';
    } else if (min > 0 || max > 0) {
        validation.textContent = 'Salary range looks good!';
        validation.className = 'validation-message valid';
    } else {
        validation.textContent = '';
    }
}

// Preview and validate photo
function previewAndValidatePhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate size
        if (file.size > 2 * 1024 * 1024) {
            alert('Image must be less than 2MB');
            input.value = '';
            return;
        }
        
        // Validate type
        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
            alert('Only JPG and PNG images are allowed');
            input.value = '';
            return;
        }
        
        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const container = document.getElementById('preview-container');
            const img = document.getElementById('preview-img');
            if (img) {
                img.src = e.target.result;
            } else if (container) {
                container.outerHTML = '<img src="' + e.target.result + '" class="rounded-circle mb-3" width="100" height="100" style="object-fit: cover; border: 3px solid #e5e7eb;" id="preview-img">';
            }
        }
        reader.readAsDataURL(file);
    }
}

// Show resume upload form
function showResumeUpload() {
    document.getElementById('resume-form').style.display = 'block';
}

// Handle resume upload
function handleResumeUpload(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const uploadBox = document.getElementById('upload-box');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        
        // Validate
        if (file.type !== 'application/pdf') {
            alert('Only PDF files are allowed');
            input.value = '';
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            alert('File must be less than 5MB');
            input.value = '';
            return;
        }
        
        // Show file info
        fileName.textContent = file.name;
        fileSize.textContent = '(' + (file.size / 1024).toFixed(1) + ' KB)';
        fileInfo.classList.remove('d-none');
        uploadBox.classList.add('uploading');
        
        // Auto-submit
        setTimeout(() => {
            document.getElementById('upload-icon').className = 'fas fa-spinner fa-spin fa-3x text-primary mb-3';
            document.getElementById('upload-text').textContent = 'Uploading...';
            document.getElementById('upload-hint').textContent = 'Please wait';
            input.form.submit();
        }, 500);
    }
}

// Education: Handle degree change
function handleDegreeChange(select) {
    const customField = document.getElementById('custom-degree-field');
    if (select.value === 'Other') {
        customField.style.display = 'block';
    } else {
        customField.style.display = 'none';
    }
}

// Education: Toggle end year
function toggleEndYear(checkbox) {
    const endYearField = document.getElementById('end-year-field');
    
    if (checkbox.checked) {
        endYearField.style.display = 'none';
    } else {
        endYearField.style.display = 'block';
    }
}

// Education: Validate years
function validateEducationYears() {
    const startYear = parseInt(document.getElementById('start_year').value);
    const endYear = parseInt(document.getElementById('end_year').value);
    const errorMsg = document.getElementById('year-error');
    const saveBtn = document.getElementById('save-education-btn');
    const isCurrent = document.getElementById('is_current').checked;
    
    if (!isCurrent && startYear && endYear && endYear < startYear) {
        errorMsg.classList.remove('d-none');
        saveBtn.disabled = true;
    } else {
        errorMsg.classList.add('d-none');
        saveBtn.disabled = false;
    }
}

// Experience: Toggle end date
function toggleExpEndDate(checkbox) {
    const endDateField = document.getElementById('exp-end-date-field');
    
    if (checkbox.checked) {
        endDateField.style.display = 'none';
    } else {
        endDateField.style.display = 'block';
    }
}

// Experience: Validate dates
function validateExperienceDates() {
    const startDate = document.getElementById('exp_start_date').value;
    const endDate = document.getElementById('exp_end_date').value;
    const errorMsg = document.getElementById('exp-date-error');
    const saveBtn = document.getElementById('save-exp-btn');
    const isCurrent = document.getElementById('exp_current').checked;
    
    if (!isCurrent && startDate && endDate && new Date(endDate) < new Date(startDate)) {
        errorMsg.classList.remove('d-none');
        saveBtn.disabled = true;
    } else {
        errorMsg.classList.add('d-none');
        saveBtn.disabled = false;
    }
}

// Priority selector
function selectPriority(element, value) {
    document.querySelectorAll('.priority-option').forEach(el => {
        el.classList.remove('selected');
    });
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;
}

// Scroll to section if hash present
if (window.location.hash) {
    setTimeout(() => {
        const el = document.querySelector(window.location.hash);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check education degree
    const degreeSelect = document.querySelector('select[name="degree"]');
    if (degreeSelect && (degreeSelect.value === 'Other' || !['Ph.D', "Master's Degree", "Bachelor's Degree", 'Diploma', '12th Grade', '10th Grade', ''].includes(degreeSelect.value))) {
        document.getElementById('custom-degree-field').style.display = 'block';
    }
    
    // Check if currently studying
    const eduCurrentCheckbox = document.getElementById('is_current');
    if (eduCurrentCheckbox && eduCurrentCheckbox.checked) {
        toggleEndYear(eduCurrentCheckbox);
    }
    
    // Check if currently working
    const expCurrentCheckbox = document.getElementById('exp_current');
    if (expCurrentCheckbox && expCurrentCheckbox.checked) {
        toggleExpEndDate(expCurrentCheckbox);
    }
});
</script>

<?php include '../includes/footer.php'; ?>
