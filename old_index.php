<?php
  ob_start();
  require_once('includes/load.php');
  if($session->isUserLoggedIn(true)) { redirect('home.php', false);}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpringBullBars</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #f59e0b;
            --dark: #1f2937;
            --light: #f9fafb;
            --gray: #6b7280;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .login-links {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 15px;
        gap: 10px;
    }

    .link-separator {
        width: 1px;
        height: 16px;
        background-color: #ccc;
    }

    .forgot-password, .signup-link {
        color: #4361ee;
        text-decoration: none;
        font-size: 14px;
    }

    .forgot-password:hover, .signup-link:hover {
        text-decoration: underline;
    }
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            line-height: 1.6;
            background-color: var(--light);
        }
        
        /* Header Styles */
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
        }
        
        .nav-item {
            margin-left: 2rem;
        }
        
        .nav-link {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            color: var(--primary);
        }
        
        .nav-link.login-trigger {
            background-color: var(--primary);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 0.3rem;
            transition: all 0.3s ease;
        }
        
        .nav-link.signup-trigger {
            background-color: var(--secondary);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 0.3rem;
            transition: all 0.3s ease;
        }
        
        .nav-link.login-trigger:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.2);
        }
        
        .nav-link.signup-trigger:hover {
            background-color: #e69009;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(245, 158, 11, 0.2);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(99,102,241,0.1) 0%, rgba(255,255,255,1) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 80px;
        }
        
        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        
        .hero-content {
            max-width: 600px;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            color: var(--dark);
        }
        
        .hero-title span {
            color: var(--primary);
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
            color: var(--gray);
            margin-bottom: 2rem;
            max-width: 500px;
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.8rem;
            border-radius: 0.3rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            margin-right: 1rem;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        
        .btn-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .hero-image {
            width: 100%;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        /* Features Section */
        .section {
            padding: 6rem 2rem;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .section-title p {
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .feature-card p {
            color: var(--gray);
        }
        
        /* Improved Login Modal Styles */
        .login-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }
        
        .login-content {
            background: white;
            padding: 2.5rem;
            border-radius: 0.8rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
            position: relative;
            animation: modalFadeIn 0.3s ease-out;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
            transition: color 0.3s ease;
        }
        
        .close-btn:hover {
            color: var(--dark);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h2 {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: var(--gray);
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.3rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .password-input-container {
            position: relative;
        }
        
        .password-toggle-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .password-toggle-icon:hover {
            color: var(--primary);
        }
        
        .login-btn {
            width: 100%;
            padding: 0.9rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        
        .login-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.2);
        }
        
        .login-btn:disabled {
            background-color: #a5b4fc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .forgot-password {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.9rem;
        }
        
        .forgot-password:hover {
            color: var(--primary);
        }
        
        .g-recaptcha {
            margin: 1.5rem 0;
            display: flex;
            justify-content: center;
        }
        
        .recaptcha-error {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: -1rem;
            margin-bottom: 1rem;
            text-align: center;
            display: none;
        }
        
        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 4rem 2rem 2rem;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .footer-col h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .footer-col h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: var(--primary);
        }
        
        .footer-col ul {
            list-style: none;
        }
        
        .footer-col li {
            margin-bottom: 0.8rem;
        }
        
        .footer-col a {
            color: #d1d5db;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-col a:hover {
            color: white;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 3rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #9ca3af";
            font-size: 0.9rem;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .hero-content {
                margin: 0 auto;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .hero-image {
                max-width: 600px;
                margin: 0 auto;
            }
        }
        
        @media (max-width: 768px) {
            .nav-menu {
                position: fixed;
                top: 80px;
                left: -100%;
                background-color: white;
                width: 100%;
                flex-direction: column;
                align-items: center;
                padding: 2rem 0;
                box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
            }
            
            .nav-menu.active {
                left: 0;
            }
            
            .nav-item {
                margin: 1rem 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 480px) {
            .login-content {
                padding: 1.5rem;
                margin: 0 1rem;
            }
            
            .login-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="#" class="logo">Spring<span>Bullbars</span></a>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="bi bi-list"></i>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <li class="nav-item">
                    <a href="#" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="#features" class="nav-link">Features</a>
                </li>
                <li class="nav-item">
                    <a href="#solutions" class="nav-link">Solutions</a>
                </li>
                <li class="nav-item">
                    <a href="#pricing" class="nav-link">Pricing</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link login-trigger" id="loginTrigger">Login</a>
                </li>
                <li class="nav-item">
                    <a href="signup.php" class="nav-link signup-trigger">Sign Up</a>
                </li>
            </ul>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Premium <span>Bullbars</span> Solutions</h1>
                <p class="hero-subtitle">Enhance your vehicle's protection and style with our high-quality bullbars, designed and manufactured for durability and performance.</p>
                <div class="hero-buttons">
                    <a href="#features" class="btn btn-primary">Our Products</a>
                    <a href="#" class="btn btn-outline" id="loginTrigger2">Buy Now</a>
                </div>
            </div>
            <div class="hero-image-container">
                <img src="uploads/img/logo.png" alt="Bullbar showcase" class="hero-image">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section" id="features">
        <div class="section-title">
            <h2>Quality and Innovation</h2>
            <p>Discover our range of premium bullbars and vehicle protection accessories</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3>Superior Protection</h3>
                <p>Engineered for maximum vehicle protection with high-grade materials and robust construction.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-tools"></i>
                </div>
                <h3>Custom Fitting</h3>
                <p>Professional installation and custom fitting services for various vehicle makes and models.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-stars"></i>
                </div>
                <h3>Premium Quality</h3>
                <p>Australian-made bullbars meeting the highest quality standards and safety regulations.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <h3>Nationwide Delivery</h3>
                <p>Fast and reliable delivery services across Australia with professional packaging.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-gear"></i>
                </div>
                <h3>Expert Support</h3>
                <p>Technical assistance and after-sales support from our experienced team.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-award"></i>
                </div>
                <h3>Warranty Backed</h3>
                <p>Comprehensive warranty coverage for peace of mind with your purchase.</p>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
<div class="login-modal" id="loginModal">
    <div class="login-content">
        <span class="close-btn" id="closeLogin">&times;</span>
        <div class="login-header">
            <h2>Welcome Back</h2>
            <p>Sign in to access your dashboard</p>
        </div>
        
        <form method="post" action="auth.php" id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" id="username" placeholder="Enter your username or email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-container">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    <i class="bi bi-eye-fill password-toggle-icon" id="togglePassword"></i>
                </div>
            </div>
            
            <div class="g-recaptcha" id="recaptchaContainer" data-sitekey="6LevqksrAAAAAD5flykbeFpj-Vve7s0DrlFGw1dh"></div>
            <div class="recaptcha-error" id="recaptchaError">Please verify you're not a robot</div>
            
            <button type="submit" class="login-btn" id="loginButton">
                <span id="loginButtonText">Login</span>
                <span id="loginSpinner" class="spinner" style="display: none;"></span>
            </button>
            
            <div class="login-links">
                <a href="reset_password.php" class="forgot-password">Forgot password?</a>
                <div class="link-separator"></div>
                <a href="signup.php" class="signup-link">Don't have an account?</a>
            </div>
        </form>
    </div>
</div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-col">
                <h3>SpringBullbars</h3>
                <p>Leading manufacturer of high-quality bullbars and vehicle protection accessories in Australia.</p>
                <div class="social-links">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-twitter"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h3>Product</h3>
                <ul>
                    <li><a href="#">Features</a></li>
                    <li><a href="#">Pricing</a></li>
                    <li><a href="#">Integrations</a></li>
                    <li><a href="#">Updates</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Company</h3>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Support</h3>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Documentation</a></li>
                    <li><a href="#">Community</a></li>
                    <li><a href="#">Status</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2023 ShopSphere. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navMenu = document.getElementById('navMenu');
        
        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
        
        // Login Modal Functionality
        const loginModal = document.getElementById('loginModal');
        const loginTrigger = document.getElementById('loginTrigger');
        const loginTrigger2 = document.getElementById('loginTrigger2');
        const closeLogin = document.getElementById('closeLogin');
        
        loginTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            loginModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            navMenu.classList.remove('active');
        });
        
        loginTrigger2.addEventListener('click', function(e) {
            e.preventDefault();
            loginModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
        
        closeLogin.addEventListener('click', function() {
            loginModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        window.addEventListener('click', function(e) {
            if (e.target === loginModal) {
                loginModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
        
        // Improved Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye-fill');
            this.classList.toggle('bi-eye-slash-fill');
        });

        // reCAPTCHA Handling
        let recaptchaWidgetId;
        
        function renderRecaptcha() {
            recaptchaWidgetId = grecaptcha.render('recaptchaContainer', {
                sitekey: '6Led0EcrAAAAAFjBQ1kIizLhutuIH4TCYh9oNQlO',
                theme: 'light',
                callback: function(response) {
                    document.getElementById('recaptchaError').style.display = 'none';
                },
                'expired-callback': function() {
                    document.getElementById('recaptchaError').style.display = 'block';
                },
                'error-callback': function() {
                    document.getElementById('recaptchaError').style.display = 'block';
                }
            });
        }

        // Handle form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const recaptchaResponse = grecaptcha.getResponse(recaptchaWidgetId);
            if (!recaptchaResponse) {
                document.getElementById('recaptchaError').style.display = 'block';
                return;
            }
            
            // Show loading state
            document.getElementById('loginButton').disabled = true;
            document.getElementById('loginButtonText').textContent = 'Logging in...';
            document.getElementById('loginSpinner').style.display = 'inline-block';
            
            // Submit the form
            this.submit();
        });

        // Initialize reCAPTCHA when modal opens
        document.getElementById('loginTrigger').addEventListener('click', function(e) {
            e.preventDefault();
            loginModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Wait a moment for the modal to render before initializing reCAPTCHA
            setTimeout(() => {
                if (typeof grecaptcha !== 'undefined' && grecaptcha.render) {
                    if (!recaptchaWidgetId) {
                        renderRecaptcha();
                    } else {
                        grecaptcha.reset(recaptchaWidgetId);
                    }
                }
            }, 100);
        });

        // Reset form when modal closes
        document.getElementById('closeLogin').addEventListener('click', function() {
            loginModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Reset form
            document.getElementById('loginForm').reset();
            document.getElementById('recaptchaError').style.display = 'none';
            document.getElementById('loginButton').disabled = false;
            document.getElementById('loginButtonText').textContent = 'Login';
            document.getElementById('loginSpinner').style.display = 'none';
            
            if (recaptchaWidgetId) {
                grecaptcha.reset(recaptchaWidgetId);
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    navMenu.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>