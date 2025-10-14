<?php
require_once 'header.php';
?>

<link rel="stylesheet" href="about.css">

<div class="about-container">
    <div class="about-content">
        <div class="about-header">
            <i data-lucide="heart-handshake" class="header-icon"></i>
            <h2>About BookHive</h2>
            <p>Transforming Library Management for Doña Matilde Memorial Elementary School</p>
        </div>

        <div class="about-grid">
            <!-- Main About Section -->
            <div class="about-cards">
                <div class="about-card">
                    <div class="about-card-header">
                        <i data-lucide="library" class="about-card-icon"></i>
                        <h3 class="about-card-title">About BookHive</h3>
                    </div>
                    <div class="about-card-content">
                        <p>The BookHive Inventory Management System is designed to efficiently manage the school's book inventory at Doña Matilde Memorial Elementary School. It allows users to track available books, borrowed books, and facilitates book additions and returns.</p>
                        <p>This system aims to maintain accurate records of books, reduce losses, and ensure students have access to necessary learning materials. With an intuitive interface, it simplifies book transactions and enhances library management efficiency.</p>
                        <p><strong>Future enhancements include AI-powered recommendations and a chatbot assistant to further improve user experience.</strong></p>
                    </div>
                </div>

                <div class="about-card">
                    <div class="about-card-header">
                        <i data-lucide="map-pin" class="about-card-icon"></i>
                        <h3 class="about-card-title">Our Location</h3>
                    </div>
                    <div class="about-card-content">
                        <div class="location-card">
                            <div class="location-info">
                                <i data-lucide="school" class="location-icon"></i>
                                <div class="location-text">
                                    <strong>Doña Matilde Memorial Elementary School</strong><br>
                                    Matingain 1, Lemery, Batangas<br>
                                    Philippines
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-card">
                    <div class="about-card-header">
                        <i data-lucide="users" class="about-card-icon"></i>
                        <h3 class="about-card-title">Development Team</h3>
                    </div>
                    <div class="about-card-content">
                        <p>BookHive was developed by a dedicated team of students passionate about improving educational technology and library management systems.</p>
                        
                        <div class="developers-grid">
                            <div class="developer-card">
                                <i data-lucide="user" class="developer-icon"></i>
                                <div class="developer-name">Kyle Bryan Bumatay</div>
                                <div class="developer-role">Project Manager</div>
                                <a href="mailto:kbpbumatay@mymail.mapua.edu.ph" class="developer-email">kbpbumatay@mymail.mapua.edu.ph</a>
                            </div>
                            
                            <div class="developer-card">
                                <i data-lucide="user" class="developer-icon"></i>
                                <div class="developer-name">Janelle Catibog</div>
                                <div class="developer-role">UI/UX Designer</div>
                                <a href="mailto:jrcatibog@mymail.mapua.edu.ph" class="developer-email">jrcatibog@mymail.mapua.edu.ph</a>
                            </div>
                            
                            <div class="developer-card">
                                <i data-lucide="user" class="developer-icon"></i>
                                <div class="developer-name">Gabrielle Allanah Dizon</div>
                                <div class="developer-role">Backend Developer</div>
                                <a href="mailto:gadizon@mymail.mapua.edu.ph" class="developer-email">gadizon@mymail.mapua.edu.ph</a>
                            </div>
                            
                            <div class="developer-card">
                                <i data-lucide="user" class="developer-icon"></i>
                                <div class="developer-name">Angela Mariel Torres</div>
                                <div class="developer-role">Backend Developer</div>
                                <a href="mailto:angelatorres@mymail.mapua.edu.ph" class="developer-email">angelatorres@mymail.mapua.edu.ph</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-card">
                    <div class="about-card-header">
                        <i data-lucide="mail" class="about-card-icon"></i>
                        <h3 class="about-card-title">Contact Us</h3>
                    </div>
                    <div class="about-card-content">
                        <p>Have questions or suggestions? Feel free to reach out to our development team:</p>
                        
                        <ul class="contact-list">
                            <li class="contact-item">
                                <i data-lucide="mail" class="contact-icon"></i>
                                <a href="mailto:kbpbumatay@mymail.mapua.edu.ph" class="contact-email">kbpbumatay@mymail.mapua.edu.ph</a>
                            </li>
                            <li class="contact-item">
                                <i data-lucide="mail" class="contact-icon"></i>
                                <a href="mailto:jrcatibog@mymail.mapua.edu.ph" class="contact-email">jrcatibog@mymail.mapua.edu.ph</a>
                            </li>
                            <li class="contact-item">
                                <i data-lucide="mail" class="contact-icon"></i>
                                <a href="mailto:gadizon@mymail.mapua.edu.ph" class="contact-email">gadizon@mymail.mapua.edu.ph</a>
                            </li>
                            <li class="contact-item">
                                <i data-lucide="mail" class="contact-icon"></i>
                                <a href="mailto:angelatorres@mymail.mapua.edu.ph" class="contact-email">angelatorres@mymail.mapua.edu.ph</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="features-section">
                <h3 class="section-title">System Features</h3>
                <div class="features-grid">
                    <div class="feature-card">
                        <i data-lucide="book-open" class="feature-icon"></i>
                        <h4 class="feature-title">Book Management</h4>
                        <p class="feature-description">Efficiently track and manage all books in the library inventory with real-time updates.</p>
                    </div>
                    
                    <div class="feature-card">
                        <i data-lucide="users" class="feature-icon"></i>
                        <h4 class="feature-title">User Management</h4>
                        <p class="feature-description">Manage student accounts and track borrowing history with ease.</p>
                    </div>
                    
                    <div class="feature-card">
                        <i data-lucide="search" class="feature-icon"></i>
                        <h4 class="feature-title">Smart Search</h4>
                        <p class="feature-description">Find books quickly with our advanced search and filtering system.</p>
                    </div>
                    
                    <div class="feature-card">
                        <i data-lucide="clock" class="feature-icon"></i>
                        <h4 class="feature-title">Loan Tracking</h4>
                        <p class="feature-description">Monitor due dates, renewals, and returns with automated notifications.</p>
                    </div>
                    
                    <div class="feature-card">
                        <i data-lucide="bar-chart" class="feature-icon"></i>
                        <h4 class="feature-title">Reports & Analytics</h4>
                        <p class="feature-description">Generate comprehensive reports on library usage and book popularity.</p>
                    </div>
                    
                    <div class="feature-card">
                        <i data-lucide="shield" class="feature-icon"></i>
                        <h4 class="feature-title">Security</h4>
                        <p class="feature-description">Secure user authentication and data protection for all library records.</p>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="stats-section">
                <h3 class="section-title">Our Impact</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Books Managed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">200+</div>
                        <div class="stat-label">Active Students</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">99%</div>
                        <div class="stat-label">System Uptime</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Support</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Add animation to stats counter (example - you can make it dynamic)
        const statNumbers = document.querySelectorAll('.stat-number');
        statNumbers.forEach(stat => {
            const target = parseInt(stat.textContent);
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target + (stat.textContent.includes('+') ? '+' : '');
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current) + (stat.textContent.includes('+') ? '+' : '');
                }
            }, 30);
        });
    });
</script>

<?php
require_once 'footer.php';
?>
