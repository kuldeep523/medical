<x-guest-layout>
    <!-- Custom styling and fonts for visual excellence -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #008b8b;
            --brand-primary-light: #00b3b3;
            --brand-dark: #0f172a;
            --brand-darker: #020617;
            --accent-glow: rgba(0, 139, 139, 0.4);
            --transition-speed: 0.3s;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Outfit', 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100vh;
        }

        .login-wrapper {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        /* Left Hero Panel Styling */
        .hero-panel {
            flex: 1.1;
            background: linear-gradient(135deg, var(--brand-darker) 0%, #002626 50%, #004d4d 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3.5rem;
            position: relative;
            overflow: hidden;
            color: #ffffff;
        }

        /* Glowing backdrop orbs */
        .glowing-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            z-index: 1;
            pointer-events: none;
        }

        .orb-1 {
            width: 350px;
            height: 350px;
            background: var(--brand-primary);
            top: -10%;
            left: -10%;
            animation: pulse 12s ease-in-out infinite alternate;
        }

        .orb-2 {
            width: 450px;
            height: 450px;
            background: #10b981;
            bottom: -15%;
            right: -10%;
            animation: pulse 16s ease-in-out infinite alternate-reverse;
        }

        @keyframes pulse {
            0% { transform: scale(1) translate(0, 0); opacity: 0.2; }
            100% { transform: scale(1.15) translate(20px, 30px); opacity: 0.35; }
        }

        .hero-header {
            position: relative;
            z-index: 5;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .hero-logo {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.6rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            font-size: 1.5rem;
            color: #00ffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .hero-title {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin: 0;
            background: linear-gradient(to right, #ffffff, #a5f3fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-center {
            position: relative;
            z-index: 5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: auto 0;
            text-align: center;
        }

        .hero-illustration-wrapper {
            position: relative;
            width: 100%;
            max-width: 460px;
            margin-bottom: 2rem;
        }

        .hero-illustration {
            width: 100%;
            height: auto;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
            100% { transform: translateY(0px); }
        }

        .hero-headline {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1.25;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #ffffff, #00ffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.05rem;
            color: #94a3b8;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .hero-footer {
            position: relative;
            z-index: 5;
            display: flex;
            gap: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
        }

        .hero-stat-item {
            flex: 1;
        }

        .hero-stat-icon {
            font-size: 1.3rem;
            color: #00ffff;
            margin-bottom: 0.5rem;
        }

        .hero-stat-label {
            font-size: 0.8rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .hero-stat-desc {
            font-size: 0.95rem;
            font-weight: 500;
            color: #e2e8f0;
            margin-top: 0.2rem;
        }

        /* Right Form Panel Styling */
        .form-panel {
            flex: 0.9;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
        }

        .card-glow {
            position: absolute;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(0, 139, 139, 0.15) 0%, rgba(255,255,255,0) 70%);
            z-index: 1;
            pointer-events: none;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            padding: 2.5rem;
            position: relative;
            z-index: 5;
            transition: all var(--transition-speed) ease;
        }

        .login-card:hover {
            box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.08), 0 10px 15px -6px rgba(0, 0, 0, 0.08);
            border-color: rgba(0, 139, 139, 0.2);
        }

        .card-header-block {
            text-align: center;
            margin-bottom: 2rem;
        }

        .card-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--brand-dark);
            margin-bottom: 0.5rem;
        }

        .card-subtitle {
            font-size: 0.95rem;
            color: #64748b;
        }

        /* Alert styling */
        .alert-box {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            border-radius: 12px;
            padding: 0.85rem 1.1rem;
            margin-bottom: 1.5rem;
            font-size: 0.88rem;
            color: #991b1b;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.04);
        }

        .alert-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }

        .alert-list {
            margin: 0;
            padding-left: 1.2rem;
            line-height: 1.5;
        }

        .status-box {
            background-color: #f0fdf4;
            border: 1px solid #dcfce7;
            border-radius: 12px;
            padding: 0.85rem 1.1rem;
            margin-bottom: 1.5rem;
            font-size: 0.88rem;
            color: #166534;
            font-weight: 500;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.35rem;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.45rem;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
            transition: color var(--transition-speed);
        }

        .custom-input {
            width: 100%;
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem 0.75rem 2.8rem;
            font-size: 0.95rem;
            color: var(--brand-dark);
            font-family: inherit;
            outline: none;
            transition: all var(--transition-speed);
        }

        .custom-input:focus {
            background-color: #ffffff;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px rgba(0, 139, 139, 0.15);
        }

        .custom-input:focus + .input-icon {
            color: var(--brand-primary);
        }

        /* Remember & Forgot Block */
        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.8rem;
            font-size: 0.88rem;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
            color: #64748b;
        }

        .checkbox-container input {
            display: none;
        }

        .checkmark {
            height: 18px;
            width: 18px;
            background-color: #f1f5f9;
            border: 1.5px solid #cbd5e1;
            border-radius: 5px;
            margin-right: 0.5rem;
            display: inline-block;
            position: relative;
            transition: all var(--transition-speed);
        }

        .checkbox-container:hover input ~ .checkmark {
            border-color: var(--brand-primary);
        }

        .checkbox-container input:checked ~ .checkmark {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 5px;
            top: 2px;
            width: 5px;
            height: 9px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .checkbox-container input:checked ~ .checkmark:after {
            display: block;
        }

        .forgot-link {
            color: var(--brand-primary);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--transition-speed);
        }

        .forgot-link:hover {
            color: var(--brand-primary-light);
            text-decoration: underline;
        }

        /* Action Button */
        .submit-btn {
            width: 100%;
            background: linear-gradient(90deg, var(--brand-primary) 0%, var(--brand-primary-light) 100%);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            padding: 0.85rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 139, 139, 0.25);
            transition: all var(--transition-speed);
        }

        .submit-btn:hover {
            background: linear-gradient(90deg, var(--brand-primary-light) 0%, var(--brand-primary) 100%);
            box-shadow: 0 6px 18px rgba(0, 139, 139, 0.35);
            transform: translateY(-1px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn i {
            transition: transform var(--transition-speed);
        }

        .submit-btn:hover i {
            transform: translateX(3px);
        }

        /* Branding undercard */
        .license-footer {
            position: absolute;
            bottom: 2rem;
            color: #94a3b8;
            font-size: 0.8rem;
            text-align: center;
            left: 0;
            right: 0;
            z-index: 5;
        }

        /* Responsive Layout */
        @media (max-width: 1024px) {
            .hero-panel {
                display: none;
            }
            .form-panel {
                flex: 1;
                padding: 1.5rem;
            }
            .login-card {
                max-width: 480px;
                padding: 2rem;
            }
        }
    </style>

    <div class="login-wrapper">
        <!-- Left Hero Panel Section -->
        <div class="hero-panel">
            <div class="glowing-orb orb-1"></div>
            <div class="glowing-orb orb-2"></div>

            <div class="hero-header">
                <div class="hero-logo">
                    <i class="bi bi-heart-pulse-fill"></i>
                </div>
                <div>
                    <h1 class="hero-title">HealthPort</h1>
                </div>
            </div>

            <div class="hero-center">
                <div class="hero-illustration-wrapper">
                    <img src="{{ asset('assets/login_hero.png') }}" class="hero-illustration" alt="Pharmacy software mockup">
                </div>
                <h2 class="hero-headline">SaaS-Enabled Pharmacy Management</h2>
                <p class="hero-subtitle">Optimize billing, manage multi-batch inventories, track distributor ledgers, and automate point-of-sale operations in real-time.</p>
            </div>

            <div class="hero-footer">
                <div class="hero-stat-item">
                    <div class="hero-stat-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                    <div class="hero-stat-label">Billing Speed</div>
                    <div class="hero-stat-desc">Real-time POS Integration</div>
                </div>
                <div class="hero-stat-item">
                    <div class="hero-stat-icon"><i class="bi bi-shield-fill-check"></i></div>
                    <div class="hero-stat-label">Accuracy Check</div>
                    <div class="hero-stat-desc">Automated Expiry Control</div>
                </div>
                <div class="hero-stat-item">
                    <div class="hero-stat-icon"><i class="bi bi-bar-chart-fill"></i></div>
                    <div class="hero-stat-label">Analytics Book</div>
                    <div class="hero-stat-desc">Ledger &amp; Cash flow tracking</div>
                </div>
            </div>
        </div>

        <!-- Right Form Panel Section -->
        <div class="form-panel">
            <div class="card-glow"></div>

            <div class="login-card">
                <div class="card-header-block">
                    <h2 class="card-title">Welcome Back</h2>
                    <p class="card-subtitle">Log in to manage your medical store</p>
                </div>

                <!-- Custom styled Session Status Message -->
                @if (session('status'))
                    <div class="status-box">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Custom styled Validation Errors Box -->
                @if ($errors->any())
                    <div class="alert-box">
                        <div class="alert-title">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Authentication Failed</span>
                        </div>
                        <ul class="alert-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Standard Laravel Jetstream form -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Store Email Address</label>
                        <div class="input-icon-wrapper">
                            <input id="email" class="custom-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@store.com">
                            <i class="bi bi-envelope-fill input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Security Password</label>
                        <div class="input-icon-wrapper">
                            <input id="password" class="custom-input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                            <i class="bi bi-shield-lock-fill input-icon"></i>
                        </div>
                    </div>

                    <div class="options-row">
                        <label for="remember_me" class="checkbox-container">
                            <input id="remember_me" name="remember" type="checkbox">
                            <span class="checkmark"></span>
                            <span>Remember this device</span>
                        </label>

                        <!-- @if (Route::has('password.request'))
                            <a class="forgot-link" href="{{ route('password.request') }}">
                                Forgot Password?
                            </a>
                        @endif -->
                    </div>

                    <button type="submit" class="submit-btn">
                        <span>Access Store Dashboard</span>
                        <i class="bi bi-arrow-right-short"></i>
                    </button>
                </form>
            </div>

            <div class="license-footer">
                <span>Licensed for <strong>ZENMEDIX CARE</strong>. All rights reserved.</span>
            </div>
        </div>
    </div>
</x-guest-layout>
