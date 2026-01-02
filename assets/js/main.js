// Main JavaScript for Job Portal

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // 1. Smooth Fade-in Animation for Job Cards
    // ============================================
    const jobCards = document.querySelectorAll('.job-card');
    jobCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // ============================================
    // 2. Search Input Focus Animation
    // ============================================
    const searchInputs = document.querySelectorAll('.search-section input, .search-section select');
    searchInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // ============================================
    // 3. Apply Button Click Animation
    // ============================================
    const applyButtons = document.querySelectorAll('.btn-apply');
    applyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.6)';
            ripple.style.width = '20px';
            ripple.style.height = '20px';
            ripple.style.animation = 'ripple 0.6s ease-out';
            
            const rect = this.getBoundingClientRect();
            ripple.style.left = (e.clientX - rect.left - 10) + 'px';
            ripple.style.top = (e.clientY - rect.top - 10) + 'px';
            
            this.style.position = 'relative';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // ============================================
    // 4. Scroll to Top Button
    // ============================================
    const scrollToTopBtn = document.createElement('button');
    scrollToTopBtn.innerHTML = '↑';
    scrollToTopBtn.className = 'scroll-to-top';
    scrollToTopBtn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5, #06b6d4);
        color: white;
        border: none;
        font-size: 24px;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    `;
    
    document.body.appendChild(scrollToTopBtn);
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.style.opacity = '1';
            scrollToTopBtn.style.visibility = 'visible';
        } else {
            scrollToTopBtn.style.opacity = '0';
            scrollToTopBtn.style.visibility = 'hidden';
        }
    });
    
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    scrollToTopBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
    });
    
    scrollToTopBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });

    // ============================================
    // 5. Live Search Counter
    // ============================================
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const value = this.value.trim();
            if (value.length > 0) {
                this.style.borderColor = '#4f46e5';
            } else {
                this.style.borderColor = '#e2e8f0';
            }
        });
    }

    // ============================================
    // 6. Job Card Tilt Effect (Optional - Advanced)
    // ============================================
    jobCards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;
            
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px)`;
        });
        
        card.addEventListener('mouseleave', function() {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
        });
    });

    // ============================================
    // 7. Copy Job Link Feature
    // ============================================
    applyButtons.forEach(button => {
        const copyBtn = document.createElement('button');
        copyBtn.innerHTML = '📋';
        copyBtn.className = 'btn btn-sm btn-outline-secondary ms-2';
        copyBtn.title = 'Copy job link';
        copyBtn.style.cssText = `
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
        `;
        
        copyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const jobLink = button.getAttribute('href');
            navigator.clipboard.writeText(jobLink).then(() => {
                copyBtn.innerHTML = '✓';
                copyBtn.style.background = '#10b981';
                copyBtn.style.color = 'white';
                copyBtn.style.borderColor = '#10b981';
                
                setTimeout(() => {
                    copyBtn.innerHTML = '📋';
                    copyBtn.style.background = '';
                    copyBtn.style.color = '';
                    copyBtn.style.borderColor = '';
                }, 2000);
            });
        });
        
        button.parentElement.style.display = 'flex';
        button.parentElement.style.alignItems = 'center';
        button.parentElement.appendChild(copyBtn);
    });

    // ============================================
    // 8. Loading State for Search
    // ============================================
    const searchForm = document.querySelector('.search-section form');
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Searching...';
            submitBtn.disabled = true;
        });
    }

    // ============================================
    // 9. Lazy Load Images (if you add company logos later)
    // ============================================
    const lazyImages = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    }

    // ============================================
    // 10. Show Job Count Animation
    // ============================================
    const statNumbers = document.querySelectorAll('.stat-text h4');
    statNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const increment = Math.ceil(finalValue / 50);
        
        const counter = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                stat.textContent = finalValue;
                clearInterval(counter);
            } else {
                stat.textContent = currentValue;
            }
        }, 30);
    });

    // ============================================
    // 11. Filter Clear Button
    // ============================================
    const filterInputs = document.querySelectorAll('.search-section input[type="text"], .search-section select');
    let hasFilters = false;
    
    filterInputs.forEach(input => {
        if (input.value.trim() !== '' && input.value !== '') {
            hasFilters = true;
        }
    });
    
    if (hasFilters) {
        const clearBtn = document.createElement('a');
        clearBtn.href = 'index.php';
        clearBtn.className = 'btn btn-outline-secondary';
        clearBtn.innerHTML = '✕ Clear Filters';
        clearBtn.style.cssText = `
            border-radius: 12px;
            font-weight: 600;
        `;
        
        const searchSection = document.querySelector('.search-section form .row');
        const col = document.createElement('div');
        col.className = 'col-md-12 text-center mt-2';
        col.appendChild(clearBtn);
        searchSection.appendChild(col);
    }

});

// ============================================
// CSS Animation Keyframes (Add to page)
// ============================================
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        from {
            width: 20px;
            height: 20px;
            opacity: 1;
        }
        to {
            width: 100px;
            height: 100px;
            opacity: 0;
        }
    }
    
    .scroll-to-top:hover {
        transform: scale(1.1) !important;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4) !important;
    }
    
    .scroll-to-top:active {
        transform: scale(0.95) !important;
    }
`;
document.head.appendChild(style);

// ============================================
// Console Message (Optional - Fun Easter Egg)
// ============================================
console.log('%c💼 Job Portal ', 'background: linear-gradient(135deg, #4f46e5, #06b6d4); color: white; padding: 10px 20px; font-size: 20px; font-weight: bold; border-radius: 8px;');
console.log('%cLooking for a job? Browse our latest opportunities! 🚀', 'color: #4f46e5; font-size: 14px; font-weight: 600;');
