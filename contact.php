<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$pageTitle = 'Contact Us - Job Portal | Get in Touch';
$metaDescription = 'Contact Job Portal for any inquiries, support, or feedback. We are here to help you with your job search and career questions.';
$metaKeywords = 'contact job portal, customer support, job portal help, career assistance';
$canonicalUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/job_poster/contact.php';

// Handle AJAX form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_submit'])) {
    header('Content-Type: application/json');

    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $messageText = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($messageText)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    } else {
        echo json_encode(['success' => true, 'message' => 'Thank you for contacting us! We will get back to you within 24-48 hours.']);
        exit;
    }
}

include 'includes/header.php';
?>

<style>
    .contact-hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
        color: white;
        padding: 4rem 0;
        margin-bottom: 3rem;
        border-radius: var(--radius-xl);
    }

    .contact-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        margin-top: 2rem;
    }

    .contact-form-section,
    .contact-info-section {
        background: white;
        border-radius: var(--radius-xl);
        padding: 2.5rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--gray-200);
    }

    .section-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 1.5rem;
    }

    .contact-info-item {
        display: flex;
        align-items: flex-start;
        gap: 1.5rem;
        padding: 1.5rem;
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
        transition: var(--transition);
    }

    .contact-info-item:hover {
        background: var(--primary-light);
        transform: translateX(5px);
    }

    .contact-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .contact-info-content h4 {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0 0 0.5rem 0;
    }

    .contact-info-content p {
        color: var(--gray-600);
        margin: 0;
    }

    .contact-info-content a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
    }

    .contact-info-content a:hover {
        text-decoration: underline;
    }

    .form-floating {
        margin-bottom: 1.5rem;
    }

    .faq-section {
        background: white;
        border-radius: var(--radius-xl);
        padding: 2.5rem;
        margin-top: 3rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
    }

    .faq-item {
        padding: 1.5rem;
        background: var(--gray-50);
        border-radius: var(--radius);
        margin-bottom: 1rem;
    }

    .faq-item h4 {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0 0 0.75rem 0;
    }

    .faq-item p {
        color: var(--gray-600);
        margin: 0;
    }

    /* Success message styling */
    .success-message {
        padding: 1.5rem;
        background-color: #d4edda;
        color: #155724;
        border-radius: var(--radius);
        text-align: center;
        margin-bottom: 1.5rem;
        border: 1px solid #c3e6cb;
    }

    .success-message .green-tick {
        color: #28a745;
        font-weight: bold;
        font-size: 1.5rem;
        margin-right: 0.5rem;
    }

    @media (max-width: 991px) {
        .contact-content {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container">
    <!-- Hero Section -->
    <div class="contact-hero">
        <div class="text-center">
            <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 800;">Contact Us</h1>
            <p style="font-size: 1.25rem; opacity: 0.9;">
                Have questions? We'd love to hear from you. Send us a message!
            </p>
        </div>
    </div>

    <!-- Alert placeholder for messages -->
    <div id="alert-container"></div>

    <!-- Contact Content -->
    <div class="contact-content">
        <!-- Contact Form -->
        <div class="contact-form-section">
            <h2 class="section-title">Send Us a Message</h2>

            <!-- Success Message (initially hidden) -->
            <div id="successMessage" class="success-message" style="display: none;">
                <span class="green-tick">✓</span>
                <strong>Success!</strong> Thank you for contacting us! We will get back to you within 24-48 hours.
            </div>

            <form id="contactForm" action="https://formsubmit.co/ajax/21jr1a43b4@gmail.com" method="POST">
                <!-- FormSubmit Configuration -->
                <input type="hidden" name="_subject" value="New Contact Form Submission - Job Portal">
                <input type="hidden" name="_template" value="table">
                <input type="hidden" name="_captcha" value="false">
                <input type="text" name="_honey" style="display:none">

                <div class="form-floating">
                    <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required>
                    <label for="name">Your Name *</label>
                </div>

                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
                    <label for="email">Email Address *</label>
                </div>

                <div class="form-floating">
                    <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" required>
                    <label for="subject">Subject *</label>
                </div>

                <div class="form-floating">
                    <textarea class="form-control" id="message" name="message" placeholder="Your Message" 
                              style="height: 150px;" required></textarea>
                    <label for="message">Your Message *</label>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-100">
                    📧 Send Message
                </button>
            </form>
        </div>

        <!-- Contact Info -->
        <div class="contact-info-section">
            <h2 class="section-title">Get in Touch</h2>

            <div class="contact-info-item">
                <div class="contact-icon">📧</div>
                <div class="contact-info-content">
                    <h4>Email</h4>
                    <p><!-- Opens Gmail compose in new tab -->
                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=info@mindrevel.in" target="_blank">
                        info@mindrevel.in
                    </a>
                    </p>
                </div>
            </div>

            <div class="contact-info-item">
                <div class="contact-icon">📞</div>
                <div class="contact-info-content">
                    <h4>Phone</h4>
                    <p><a href="tel:+917730912808">+91 77309 12808</a></p>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                </div>
            </div>

            <div class="contact-info-item">
                <div class="contact-icon">⏰</div>
                <div class="contact-info-content">
                    <h4>Business Hours</h4>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                    <p>Saturday: 10:00 AM - 4:00 PM</p>
                    <p>Sunday: Closed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="faq-section">
        <h2 class="section-title">Frequently Asked Questions</h2>

        <div class="faq-item">
            <h4>❓ How do I apply for a job?</h4>
            <p>Click on any job listing and then click the "Apply Now" button. You will be redirected to the company's application page.</p>
        </div>

        <div class="faq-item">
            <h4>❓ Are all job listings verified?</h4>
            <p>Yes, we verify all job postings before publishing them on our platform to ensure authenticity and quality.</p>
        </div>

        <div class="faq-item">
            <h4>❓ How often are new jobs posted?</h4>
            <p>We update our job listings daily. You can enable notifications to get instant alerts for new opportunities.</p>
        </div>

        <div class="faq-item">
            <h4>❓ Is there any fee to use this portal?</h4>
            <p>No, our job portal is completely free for job seekers. There are no hidden charges or subscription fees.</p>
        </div>

        <div class="faq-item">
            <h4>❓ How can I report a fake job posting?</h4>
            <p>Please contact us immediately at <!-- Opens Gmail compose in new tab -->
            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=info@mindrevel.in" target="_blank">
                info@mindrevel.in
            </a>with the job ID and details. We take such matters seriously.</p>
        </div>
    </div>
</div>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const form = this;
    const successMessage = document.getElementById('successMessage');
    const alertContainer = document.getElementById('alert-container');

    // Change button text and disable
    submitBtn.innerHTML = '⏳ Sending...';
    submitBtn.disabled = true;

    // Hide success message if previously shown
    successMessage.style.display = 'none';
    alertContainer.innerHTML = '';

    // Get form data
    const formData = new FormData(form);

    // Submit to FormSubmit
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Show success message
        successMessage.style.display = 'block';

        // Reset form
        form.reset();

        // Scroll to success message
        successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Reset button
        submitBtn.innerHTML = '📧 Send Message';
        submitBtn.disabled = false;

        // Hide success message after 5 seconds
        setTimeout(() => {
            successMessage.style.display = 'none';
        }, 5000);
    })
    .catch(error => {
        console.error('Error:', error);

        // Show error alert
        alertContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Failed to send message. Please try again or contact us directly at info@mindrevel.in
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Reset button
        submitBtn.innerHTML = '📧 Send Message';
        submitBtn.disabled = false;

        // Scroll to error
        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});
</script>

<?php include 'includes/footer.php'; ?>