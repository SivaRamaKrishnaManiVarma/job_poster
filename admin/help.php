<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) redirect('login.php');

$pageTitle = 'Help & Documentation';
$isAdmin = true;

include 'includes/header.php';
?>

<div class="admin-container">
    <div class="page-header">
        <h2>📚 Help & Documentation</h2>
        <p>Quick guide to managing your job portal</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Getting Started -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">🚀 Getting Started</h4>
                    <hr>
                    
                    <h5>Posting a New Job</h5>
                    <ol>
                        <li>Go to <strong>Manage Jobs</strong> from the navigation</li>
                        <li>Fill in the job posting form with all required details</li>
                        <li>Select appropriate categories, work mode, and employment type</li>
                        <li>Set the application deadline (optional)</li>
                        <li>Click <strong>"Post Job"</strong></li>
                    </ol>

                    <h5 class="mt-4">Understanding Job Status</h5>
                    <ul>
                        <li><span class="badge bg-success">Active</span> - Job is visible to students on the portal</li>
                        <li><span class="badge bg-warning">Expiring Soon</span> - Deadline within 7 days</li>
                        <li><span class="badge bg-danger">Expired</span> - Deadline has passed</li>
                        <li><span class="badge bg-secondary">Inactive</span> - Hidden from public view</li>
                    </ul>

                    <h5 class="mt-4">Managing Expired Jobs</h5>
                    <p>When a job's application deadline passes:</p>
                    <ul>
                        <li>It automatically moves to the <strong>Archive</strong> on the public portal</li>
                        <li>You'll see an alert on the dashboard</li>
                        <li>You can bulk deactivate expired jobs from <strong>Manage Jobs → Expired</strong> filter</li>
                        <li>Or manually deactivate individual jobs</li>
                    </ul>
                </div>
            </div>

            <!-- Master Data -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">⚙️ Master Data Settings</h4>
                    <hr>
                    
                    <p>Configure job categories, work modes, and other options from <strong>Settings</strong>:</p>
                    
                    <ul>
                        <li><strong>Job Categories</strong> - IT, Government, Healthcare, etc.</li>
                        <li><strong>Work Modes</strong> - Remote, Hybrid, On-site</li>
                        <li><strong>Employment Types</strong> - Full-time, Part-time, Internship</li>
                        <li><strong>Experience Levels</strong> - Fresher, Mid-level, Senior</li>
                        <li><strong>States</strong> - Indian states and territories</li>
                        <li><strong>Departments</strong> - Government departments and PSUs</li>
                        <li><strong>Qualifications</strong> - Educational requirements</li>
                    </ul>

                    <div class="alert alert-info mt-3">
                        <strong>💡 Tip:</strong> Keep master data organized and consistent for better user experience
                    </div>
                </div>
            </div>

            <!-- Best Practices -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">✅ Best Practices</h4>
                    <hr>
                    
                    <h5>Writing Job Titles</h5>
                    <ul>
                        <li>Be specific: "Senior Full Stack Developer" not just "Developer"</li>
                        <li>Avoid abbreviations unless widely known</li>
                        <li>Include seniority level if relevant</li>
                    </ul>

                    <h5 class="mt-3">Job Descriptions</h5>
                    <ul>
                        <li>Keep it concise but informative (100-150 words ideal)</li>
                        <li>Highlight key responsibilities and benefits</li>
                        <li>Mention unique selling points of the opportunity</li>
                    </ul>

                    <h5 class="mt-3">Application Deadlines</h5>
                    <ul>
                        <li>Set realistic deadlines (minimum 7-14 days recommended)</li>
                        <li>For rolling applications, leave deadline blank</li>
                        <li>Update or extend deadlines if needed</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quick Reference Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">🔗 Quick Links</h5>
                    <div class="list-group list-group-flush">
                        <a href="jobs.php" class="list-group-item list-group-item-action">
                            📋 Manage Jobs
                        </a>
                        <a href="settings.php" class="list-group-item list-group-item-action">
                            ⚙️ Settings
                        </a>
                        <a href="../index.php" target="_blank" class="list-group-item list-group-item-action">
                            🌐 View Public Portal
                        </a>
                        <a href="../archive.php" target="_blank" class="list-group-item list-group-item-action">
                            📚 View Archive
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mb-4 bg-light">
                <div class="card-body">
                    <h5 class="card-title">💡 Pro Tips</h5>
                    <ul class="mb-0">
                        <li>Use filters to manage large numbers of jobs efficiently</li>
                        <li>Regularly review expired jobs and deactivate them</li>
                        <li>Keep master data updated for better categorization</li>
                        <li>Preview jobs on the public portal before publishing</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">🆘 Need Help?</h5>
                    <p class="card-text">
                        If you encounter any issues or have questions, contact your system administrator.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
