<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PharmaSync | Modern Pharmacy Management</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #00f2fe;
            --secondary: #4facfe;
            --dark-bg: #030712;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f3f4f6;
            --text-dim: #9ca3af;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-main);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Animated Background */
        .bg-glow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 20% 30%, rgba(0, 242, 254, 0.05), transparent 40%),
                        radial-gradient(circle at 80% 70%, rgba(79, 172, 254, 0.05), transparent 40%);
        }

        /* Navbar */
        nav {
            padding: 1.5rem 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            background: rgba(3, 7, 18, 0.5);
            border-bottom: 1px solid var(--glass-border);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-main);
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            font-size: 0.95rem;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .btn-login {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: var(--dark-bg) !important;
            padding: 0.6rem 1.8rem;
            border-radius: 50px;
            font-weight: 600 !important;
            box-shadow: 0 4px 15px rgba(0, 242, 254, 0.3);
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-login:hover {
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 242, 254, 0.5);
        }

        /* Hero Section */
        .hero {
            padding: 10rem 10% 5rem;
            display: flex;
            align-items: center;
            gap: 4rem;
            min-height: 90vh;
        }

        .hero-content {
            flex: 1;
        }

        .hero-content h1 {
            font-size: 4.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(to bottom right, #fff, #9ca3af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-content p {
            font-size: 1.25rem;
            color: var(--text-dim);
            margin-bottom: 2.5rem;
            max-width: 500px;
        }

        .hero-image {
            flex: 1;
            position: relative;
        }

        .hero-image img {
            width: 100%;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--glass-border);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .stats {
            display: flex;
            gap: 3rem;
            margin-top: 3rem;
        }

        .stat-item h3 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.2rem;
        }

        .stat-item p {
            font-size: 0.85rem;
            color: var(--text-dim);
        }

        /* Features Section */
        .features {
            padding: 5rem 10%;
            text-align: center;
        }

        .features h2 {
            font-size: 2.5rem;
            margin-bottom: 3rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            padding: 2.5rem;
            border-radius: 24px;
            text-align: left;
            transition: 0.4s;
            position: relative;
            overflow: hidden;
        }

        .feature-card:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-10px);
            border-color: var(--primary);
        }

        .feature-card i {
            font-size: 2.5rem;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            display: inline-block;
        }

        .feature-card h4 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--text-dim);
            font-size: 0.95rem;
        }

        /* Footer */
        footer {
            padding: 5rem 10% 2rem;
            border-top: 1px solid var(--glass-border);
            margin-top: 5rem;
            text-align: center;
        }

        .footer-logo {
            margin-bottom: 2rem;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .social-links a {
            color: var(--text-dim);
            font-size: 1.5rem;
            transition: 0.3s;
        }

        .social-links a:hover {
            color: var(--primary);
        }

        .copyright {
            color: var(--text-dim);
            font-size: 0.85rem;
        }

        /* Mobile Responsive */
        @media (max-width: 992px) {
            .hero {
                flex-direction: column;
                text-align: center;
                padding-top: 8rem;
            }
            .hero-content h1 {
                font-size: 3.5rem;
            }
            .hero-content p {
                margin: 0 auto 2.5rem;
            }
            .stats {
                justify-content: center;
            }
            .nav-links {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <nav>
        <div class="logo">
            <i class="bi bi-capsule-pill"></i> ZenMedix Erp
        </div>
        <div class="nav-links">
            <a href="#features">Features</a>
            <a href="#pricing">Solutions</a>
            <a href="#contact">About</a>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-login">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-login">Log In</a>
                @endauth
            @endif
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>Intelligent Pharmacy Operations</h1>
            <p>Elevate your pharmacy management with real-time inventory tracking, smart POS billing, and deep business analytics.</p>
            
            <div style="display: flex; gap: 1.5rem; align-items: center;">
                @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-login">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-login">Log In</a>
                @endauth
            @endif
                <a href="#features" style="color: var(--text-main); text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                    See How it Works <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="stats">
                <div class="stat-item">
                    <h3>500+</h3>
                    <p>Stores Integrated</p>
                </div>
                <div class="stat-item">
                    <h3>99.9%</h3>
                    <p>Stock Accuracy</p>
                </div>
                <div class="stat-item">
                    <h3>24/7</h3>
                    <p>Smart Monitoring</p>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <img src="{{ asset('pharmacy_hero_illustration_1778045752153.png') }}" alt="PharmaSync Interface">
        </div>
    </section>

    <section id="features" class="features">
        <h2 style="background: linear-gradient(to bottom, #fff, #9ca3af); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Advanced Capabilities</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <i class="bi bi-box-seam"></i>
                <h4>Master-Batch Inventory</h4>
                <p>Strict batch-level tracking with FEFO logic. Never lose track of expiry dates again.</p>
            </div>
            <div class="feature-card">
                <i class="bi bi-receipt"></i>
                <h4>Smart POS Billing</h4>
                <p>Blazing fast checkout with automatic substitute finder for out-of-stock items.</p>
            </div>
            <div class="feature-card">
                <i class="bi bi-graph-up-arrow"></i>
                <h4>Financial MIS</h4>
                <p>Daily sales, gross profit, and expense tracking with interactive visual charts.</p>
            </div>
            <div class="feature-card">
                <i class="bi bi-shield-check"></i>
                <h4>Multi-Store SaaS</h4>
                <p>Enterprise-grade multi-tenancy. Manage multiple branches from a single dashboard.</p>
            </div>
        </div>
    </section>

    <footer>
        <div class="logo footer-logo" style="justify-content: center;">
            <i class="bi bi-capsule-pill"></i> ZenMedix Erp
        </div>
        <p class="copyright">&copy; {{ date('Y') }} PharmaSync. All rights reserved. Professional Pharmacy Management Suite.</p>
    </footer>

    <script>
        // Simple scroll animation for feature cards
        window.addEventListener('scroll', () => {
            const cards = document.querySelectorAll('.feature-card');
            cards.forEach(card => {
                const cardTop = card.getBoundingClientRect().top;
                if(cardTop < window.innerHeight - 100) {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }
            });
        });
    </script>
</body>
</html>
