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
    <title>SpringBullBars | Premium Vehicle Protection</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.7.2/css/all.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3a86ff;
            --primary-dark: #2667cc;
            --primary-light: #e6f0ff;
            --secondary: #ffbe0b;
            --dark: #1a1a2e;
            --dark-2: #16213e;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --success: #4bb543;
            --danger: #ff3333;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1), 0 1px 3px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 20px rgba(0,0,0,0.1), 0 6px 6px rgba(0,0,0,0.1);
            --shadow-xl: 0 15px 25px rgba(0,0,0,0.1), 0 10px 10px rgba(0,0,0,0.08);
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --radius-full: 9999px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            line-height: 1.6;
            background-color: var(--light);
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        .section {
            padding: 6rem 0;
            position: relative;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: var(--radius-full);
        }
        
        .section-title p {
            color: var(--gray);
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
        }
        
        /* Header Styles */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        
        header.scrolled {
            background-color: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-md);
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logo-icon {
            color: var(--secondary);
            font-size: 2rem;
        }
        
        .logo-text {
            font-family: 'Playfair Display', serif;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 0;
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary);
            transition: var(--transition);
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .nav-link.active {
            color: var(--primary);
        }
        
        .nav-link.active::after {
            width: 100%;
        }
        
        .nav-cta {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-left: 2rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.75rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 1rem;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            color: var(--dark);
        }
        
        .btn-secondary:hover {
            background-color: #e6a908;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
            z-index: 1001;
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 80px;
            background: linear-gradient(135deg, rgba(58,134,255,0.05) 0%, rgba(255,255,255,1) 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 80%;
            height: 150%;
            background: radial-gradient(circle, rgba(58,134,255,0.1) 0%, rgba(255,255,255,0) 70%);
            z-index: -1;
        }
        
        .hero-container {
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
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero-title span {
            color: var(--primary);
            position: relative;
        }
        
        .hero-title span::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 10px;
            background-color: rgba(255, 190, 11, 0.3);
            z-index: -1;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--gray);
            margin-bottom: 2rem;
            max-width: 500px;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .hero-image-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .hero-image {
            width: 100%;
            max-width: 600px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            transform-style: preserve-3d;
            animation: float 8s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }
        
        .hero-shape {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            opacity: 0.1;
            z-index: 0;
            animation: morph 15s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        
        @keyframes morph {
            0% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
            50% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
            100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
        }
        
        /* Features Section */
        .features {
            background-color: white;
            position: relative;
        }
        
        .features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to bottom, var(--light), rgba(255,255,255,0));
            z-index: 1;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            position: relative;
            z-index: 2;
        }
        
        .feature-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 1px solid var(--gray-light);
            text-align: center;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background-color: var(--primary-light);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: var(--primary);
            transition: var(--transition);
        }
        
        .feature-card:hover .feature-icon {
            background-color: var(--primary);
            color: white;
            transform: rotateY(180deg);
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .feature-card p {
            color: var(--gray);
        }
        
        /* Stats Section */
        .stats {
            background: linear-gradient(135deg, var(--dark), var(--dark-2));
            color: white;
            padding: 4rem 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }
        
        .stat-item {
            padding: 1.5rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--secondary);
            font-family: 'Inter', sans-serif;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        /* Testimonials */
        .testimonials {
            background-color: var(--light);
        }
        
        .testimonial-slider {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        
        .testimonial-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            margin: 1rem;
            text-align: center;
            transition: var(--transition);
        }
        
        .testimonial-avatar {
            width: 80px;
            height: 80px;
            border-radius: var(--radius-full);
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 3px solid var(--primary-light);
        }
        
        .testimonial-quote {
            font-style: italic;
            margin-bottom: 1.5rem;
            color: var(--dark);
            position: relative;
        }
        
        .testimonial-quote::before,
        .testimonial-quote::after {
            content: '"';
            font-size: 2rem;
            color: var(--primary-light);
            line-height: 0;
            vertical-align: middle;
        }
        
        .testimonial-quote::before {
            margin-right: 0.5rem;
        }
        
        .testimonial-quote::after {
            margin-left: 0.5rem;
        }
        
        .testimonial-author {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .testimonial-role {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-align: center;
            padding: 5rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .cta::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            z-index: 1;
        }
        
        .cta-container {
            position: relative;
            z-index: 2;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }
        
        .cta p {
            max-width: 700px;
            margin: 0 auto 2rem;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 5rem 0 2rem;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .footer-col h3 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .footer-col h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
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
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-col a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .footer-about p {
            color: rgba(255,255,255,0.7);
            margin-bottom: 1.5rem;
            line-height: 1.7;
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
            border-radius: var(--radius-full);
            background-color: rgba(255,255,255,0.1);
            transition: var(--transition);
            color: white;
        }
        
        .social-links a:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
        }
        
        /* Login Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            backdrop-filter: blur(5px);
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 450px;
            box-shadow: var(--shadow-xl);
            position: relative;
            transform: translateY(20px);
            transition: var(--transition);
            opacity: 0;
        }
        
        .modal-overlay.active .modal-content {
            transform: translateY(0);
            opacity: 1;
            transition-delay: 0.1s;
        }
        
        .close-btn {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
            transition: var(--transition);
            background: none;
            border: none;
        }
        
        .close-btn:hover {
            color: var(--dark);
            transform: rotate(90deg);
        }
        
        .modal-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .modal-header h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .modal-header p {
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
            padding: 0.9rem 1.2rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-md);
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(58, 134, 255, 0.2);
        }
        
        .password-input-container {
            position: relative;
        }
        
        .password-toggle-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .password-toggle-icon:hover {
            color: var(--primary);
        }
        
        .modal-btn {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .modal-btn:disabled {
            background-color: #a5b4fc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .login-links {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1.5rem;
            gap: 1rem;
        }
        
        .link-separator {
            width: 1px;
            height: 16px;
            background-color: var(--gray-light);
        }
        
        .forgot-password, .signup-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        
        .forgot-password:hover, .signup-link:hover {
            text-decoration: underline;
        }
        
        .g-recaptcha {
            margin: 1.5rem 0;
            display: flex;
            justify-content: center;
        }
        
        .recaptcha-error {
            color: var(--danger);
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
            border-radius: var(--radius-full);
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 4rem;
            }
            
            .hero-content {
                margin: 0 auto;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .hero-image {
                max-width: 500px;
                margin: 0 auto;
            }
            
            .section-title h2 {
                font-size: 2.2rem;
            }
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            
            .nav-menu {
                position: fixed;
                top: 0;
                right: -100%;
                width: 80%;
                max-width: 350px;
                height: 100vh;
                background-color: white;
                flex-direction: column;
                align-items: flex-start;
                padding: 6rem 2rem 2rem;
                box-shadow: var(--shadow-lg);
                transition: var(--transition);
                z-index: 1000;
            }
            
            .nav-menu.active {
                right: 0;
            }
            
            .nav-item {
                margin: 0.5rem 0;
                width: 100%;
            }
            
            .nav-link {
                padding: 0.75rem 0;
                display: block;
                width: 100%;
            }
            
            .nav-cta {
                margin-left: 0;
                margin-top: 1rem;
                width: 100%;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .hero-title {
                font-size: 2.8rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .section {
                padding: 4rem 0;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 2.2rem;
            }
            
            .hero-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .modal-content {
                padding: 1.5rem;
                margin: 0 1rem;
            }
            
            .modal-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header Component -->
    <header id="header">
        <div class="container">
            <div class="header-container">
                <a href="#" class="logo">
                    <i class="fa-duotone fa-solid fa-car-side"></i>
                    <span class="logo-text">SpringBullBars</span>
                </a>
                
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                
                <ul class="nav-menu" id="navMenu">
                    <li class="nav-item">
                        <a href="#" class="nav-link active">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="#features" class="nav-link">Features</a>
                    </li>
                    <li class="nav-item">
                        <a href="#solutions" class="nav-link">Solutions</a>
                    </li>
                    <li class="nav-item">
                        <a href="#testimonials" class="nav-link">Testimonials</a>
                    </li>
                    <div class="nav-cta">
                        <a href="#" class="btn btn-outline" id="loginTrigger">Login</a>
                        <a href="signup.php" class="btn btn-secondary">Sign Up</a>
                    </div>
                </ul>
            </div>
        </div>
    </header>

    <!-- Hero Component -->
    <section class="hero" id="hero">
        <div class="container">
            <div class="hero-container">
                <div class="hero-content">
                    <h1 class="hero-title">Premium <span>Vehicle Protection</span> Solutions</h1>
                    <p class="hero-subtitle">Enhance your vehicle's safety and style with our high-quality bullbars, engineered for maximum durability and performance in all conditions.</p>
                    <div class="hero-buttons">
                        <a href="#features" class="btn btn-primary">
                        
                            <i class="fa-duotone fa-solid fa-cars"></i> Explore Products
                        </a>
                        <a href="#" class="btn btn-outline" id="loginTrigger2">
                            <i class="fas fa-shopping-cart"></i> Buy Now
                        </a>
                    </div>
                </div>
                <div class="hero-image-container">
                    <div class="hero-shape"></div>
                    <img src="uploads/img/logo.png" alt="Premium Bullbars" class="hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Component -->
<section class="section features" id="features">
    <div class="container">
        <div class="section-title">
            <h2>Unmatched Quality & Protection</h2>
            <p>Our bullbars are designed to provide superior protection while enhancing your vehicle's appearance</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-shield"></i>
                </div>
                <h3>Military-Grade Protection</h3>
                <p>Constructed with high-tensile steel and advanced engineering to withstand extreme impacts.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-car"></i>
                </div>
                <h3>Vehicle-Specific Design</h3>
                <p>Precision engineered for perfect fitment on all major vehicle makes and models.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-badge-check"></i> <!-- alternative to fa-award -->
                </div>
                <h3>Certified Safety</h3>
                <p>Compliant with all Philippine safety standards and airbag compatibility requirements.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <h3>Easy Installation</h3>
                <p>Comprehensive installation kits with detailed instructions for professional or DIY fitting.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-cloud-sun-rain"></i>
                </div>
                <h3>Weather Resistant</h3>
                <p>Powder-coated finish provides superior protection against rust and corrosion.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa-solid fa-stars"></i> <!-- or fa-award -->
                </div>
                <h3>5-Year Warranty</h3>
                <p>Industry-leading warranty coverage for complete peace of mind.</p>
            </div>
        </div>
    </div>
</section>


    <!-- Stats Component -->
    <section class="section stats" id="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">15K+</div>
                    <div class="stat-label">Vehicles Protected</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Customer Satisfaction</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">25+</div>
                    <div class="stat-label">Years Experience</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">philippine Made</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Component -->
    <section class="section testimonials" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>What Our Customers Say</h2>
                <p>Hear from vehicle owners who trust SpringBullBars for their protection needs</p>
            </div>
            <div class="testimonial-slider">
                <div class="testimonial-card">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="John D." class="testimonial-avatar">
                    <p class="testimonial-quote">The quality of these bullbars is exceptional. After a collision with a kangaroo, my vehicle was completely protected with no damage to the radiator or engine.</p>
                    <h4 class="testimonial-author">John D.</h4>
                    <p class="testimonial-role">4WD Enthusiast</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Component -->
    <section class="section cta" id="cta">
        <div class="container">
            <div class="cta-container">
                <h2>Ready to Protect Your Vehicle?</h2>
                <p>Join thousands of satisfied customers who trust SpringBullBars for premium vehicle protection solutions.</p>
                <div class="hero-buttons">
                    <a href="#" class="btn btn-secondary" id="loginTrigger3">
                        <i class="fas fa-shopping-cart"></i> Shop Now
                    </a>
                    <a href="#features" class="btn btn-outline" style="color: white; border-color: white;">
                        <i class="fas fa-info-circle"></i> Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Component -->
    <footer id="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col footer-about">
                    <h3>SpringBullBars</h3>
                    <p>philippine's leading manufacturer of premium bullbars and vehicle protection systems since 1995.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Products</h3>
                    <ul>
                        <li><a href="#">Bullbars</a></li>
                        <li><a href="#">Side Steps</a></li>
                        <li><a href="#">Rear Bars</a></li>
                        <li><a href="#">Accessories</a></li>
                        <li><a href="#">New Releases</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Company</h3>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Our Story</a></li>
                        <li><a href="#">Manufacturing</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                                <div class="footer-col">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Installation Guides</a></li>
                        <li><a href="#">Warranty Info</a></li>
                        <li><a href="#">Shipping Policy</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 SpringBullBars. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
            </div>
        </div>
    </footer>

    <!-- Login Modal Component -->
    <div class="modal-overlay" id="loginModal">
        <div class="modal-content">
            <button class="close-btn" id="closeLogin">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-header">
                <h2>Welcome Back</h2>
                <p>Sign in to access your account and manage your orders</p>
            </div>
            
            <form method="post" action="auth.php" id="loginForm">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" class="form-control" name="username" id="username" placeholder="Enter your username or email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input-container">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <i class="fas fa-eye password-toggle-icon" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="g-recaptcha" id="recaptchaContainer" data-sitekey="6LevqksrAAAAAD5flykbeFpj-Vve7s0DrlFGw1dh"></div>
                <div class="recaptcha-error" id="recaptchaError">Please verify you're not a robot</div>
                
                <button type="submit" class="modal-btn" id="loginButton">
                    <span id="loginButtonText">Login</span>
                    <span id="loginSpinner" class="spinner" style="display: none;"></span>
                </button>
                
                <div class="login-links">
                    <a href="reset_password.php" class="forgot-password">Forgot password?</a>
                    <div class="link-separator"></div>
                    <a href="signup.php" class="signup-link">Create account</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // React-like component functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Header scroll effect
            const header = document.getElementById('header');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const navMenu = document.getElementById('navMenu');
            
            mobileMenuBtn.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                mobileMenuBtn.innerHTML = navMenu.classList.contains('active') ? 
                    '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
            });

            // Login modal functionality
            const loginModal = document.getElementById('loginModal');
            const loginTriggers = document.querySelectorAll('[id^="loginTrigger"]');
            const closeLogin = document.getElementById('closeLogin');
            
            // Open modal from any trigger
            loginTriggers.forEach(trigger => {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    loginModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    
                    // Close mobile menu if open
                    navMenu.classList.remove('active');
                    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    
                    // Initialize reCAPTCHA if needed
                    initRecaptcha();
                });
            });
            
            // Close modal
            closeLogin.addEventListener('click', function() {
                loginModal.classList.remove('active');
                document.body.style.overflow = 'auto';
                resetRecaptcha();
            });
            
            // Close modal when clicking outside
            loginModal.addEventListener('click', function(e) {
                if (e.target === loginModal) {
                    loginModal.classList.remove('active');
                    document.body.style.overflow = 'auto';
                    resetRecaptcha();
                }
            });

            // Password toggle
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
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
                        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                });
            });

            // reCAPTCHA handling
            let recaptchaWidgetId;
            
            function initRecaptcha() {
                if (typeof grecaptcha !== 'undefined' && grecaptcha.render) {
                    if (!recaptchaWidgetId) {
                        recaptchaWidgetId = grecaptcha.render('recaptchaContainer', {
                            sitekey: '6LevqksrAAAAAD5flykbeFpj-Vve7s0DrlFGw1dh',
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
                    } else {
                        grecaptcha.reset(recaptchaWidgetId);
                    }
                }
            }
            
            function resetRecaptcha() {
                document.getElementById('loginForm').reset();
                document.getElementById('recaptchaError').style.display = 'none';
                document.getElementById('loginButton').disabled = false;
                document.getElementById('loginButtonText').textContent = 'Login';
                document.getElementById('loginSpinner').style.display = 'none';
                
                if (recaptchaWidgetId) {
                    grecaptcha.reset(recaptchaWidgetId);
                }
            }

            // Form submission handling
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const recaptchaResponse = grecaptcha.getResponse(recaptchaWidgetId);
                if (!recaptchaResponse) {
                    document.getElementById('recaptchaError').style.display = 'block';
                    return;
                }
                
                // Show loading state
                document.getElementById('loginButton').disabled = true;
                document.getElementById('loginButtonText').textContent = 'Authenticating...';
                document.getElementById('loginSpinner').style.display = 'inline-block';
                
                // Simulate API call (replace with actual form submission)
                setTimeout(() => {
                    // Submit the form
                    this.submit();
                }, 1500);
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .section-title, .testimonial-card').forEach(element => {
            observer.observe(element);
        });
    </script>
</body>
</html>