<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Bunar Pharmacy</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #94061b;
            --secondary-color: #f8f9fa;
            --accent-color: #e9ecef;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(148, 6, 27, 0.15);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            min-height: 600px;
            display: flex;
        }
        
        .login-image {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color) 0%, #7d0518 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: white;
        }
        
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 600"><rect fill="%2394061b" width="400" height="600"/><circle fill="%23ffffff" fill-opacity="0.1" cx="100" cy="150" r="80"/><circle fill="%23ffffff" fill-opacity="0.1" cx="300" cy="300" r="60"/><circle fill="%23ffffff" fill-opacity="0.1" cx="150" cy="450" r="70"/><circle fill="%23ffffff" fill-opacity="0.05" cx="320" cy="100" r="40"/><circle fill="%23ffffff" fill-opacity="0.05" cx="50" cy="350" r="50"/><path fill="%23ffffff" fill-opacity="0.1" d="M200 200 L220 180 L240 200 L220 220 Z"/><path fill="%23ffffff" fill-opacity="0.1" d="M120 380 L140 360 L160 380 L140 400 Z"/><path fill="%23ffffff" fill-opacity="0.1" d="M280 450 L300 430 L320 450 L300 470 Z"/></svg>');
            background-size: cover;
            background-position: center;
            opacity: 0.1;
        }
        
        .image-content {
            text-align: center;
            z-index: 2;
            position: relative;
        }
        
        .pharmacy-logo {
            font-size: 4rem;
            margin-bottom: 30px;
            color: white;
        }
        
        .image-content h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .image-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .feature-list {
            list-style: none;
            text-align: left;
            max-width: 300px;
            margin: 0 auto;
        }
        
        .feature-list li {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .feature-list i {
            margin-right: 15px;
            font-size: 1.2rem;
        }
        
        .login-form {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .form-header h3 {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(148, 6, 27, 0.25);
            outline: none;
            background-color: white;
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 70%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .password-toggle {
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 8px;
            transform: scale(1.1);
        }
        
        .remember-me label {
            color: #6c757d;
            font-size: 0.95rem;
            cursor: pointer;
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: #7d0518;
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #7d0518 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(148, 6, 27, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .loading-spinner {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            display: none;
        }
        
        .loading-spinner.active {
            display: block;
        }
        
        .btn-text {
            transition: opacity 0.3s ease;
        }
        
        .btn-text.hidden {
            opacity: 0;
        }
        
        .register-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e9ecef;
        }
        
        .register-link p {
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            padding: 10px 25px;
            border: 2px solid var(--primary-color);
            border-radius: 25px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .register-link a:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: none;
            font-size: 0.95rem;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                flex-direction: column;
                min-height: auto;
            }
            
            .login-image {
                min-height: 300px;
                padding: 30px 20px;
            }
            
            .image-content h2 {
                font-size: 2rem;
            }
            
            .image-content p {
                font-size: 1rem;
            }
            
            .pharmacy-logo {
                font-size: 3rem;
                margin-bottom: 20px;
            }
            
            .login-form {
                padding: 40px 30px;
            }
            
            .form-header h3 {
                font-size: 1.8rem;
            }
            
            .form-options {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
        
        @media (max-width: 480px) {
            .login-form {
                padding: 30px 20px;
            }
            
            .image-content h2 {
                font-size: 1.8rem;
            }
            
            .feature-list {
                display: none;
            }
        }
        
        /* Animation for form elements */
        .form-group {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-options { animation-delay: 0.4s; }
        .btn-login { animation-delay: 0.5s; }
        .register-link { animation-delay: 0.6s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Floating animation for image elements */
        .pharmacy-logo {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Image/Branding -->
        <div class="login-image">
            <div class="image-content">
                <div class="pharmacy-logo">
                    <i class="fas fa-pills"></i>
                </div>
                <h2>Welcome Back</h2>
                <p>Access your Bumar Pharmacy management portal</p>
                
                <ul class="feature-list">
                    <li>
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure & Protected</span>
                    </li>
                    <li>
                        <i class="fas fa-chart-line"></i>
                        <span>Advanced Analytics</span>
                    </li>
                    <li>
                        <i class="fas fa-users"></i>
                        <span>Patient Management</span>
                    </li>
                    <li>
                        <i class="fas fa-prescription-bottle"></i>
                        <span>Inventory Control</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="login-form">
            <div class="form-header">
                <h3>Sign In</h3>
                <p>Enter your credentials to access your account</p>
            </div>
            
            <!-- Alert Messages -->
            <div id="alertContainer"></div>
            
            <form id="loginForm" action="authenticate.php" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-2"></i>Username or Email
                    </label>
                    <input type="text" id="username" name="username" class="form-control" required>
                    <i class="fas fa-user input-icon"></i>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <i class="fas fa-eye password-toggle input-icon" id="passwordToggle"></i>
                </div>
                
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" class="form-check-input">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="btn-text">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </span>
                    <div class="loading-spinner">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </button>
            </form>
            
            <div class="register-link">
                <p>Don't have an account?</p>
                <a href="register.php">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Password toggle functionality
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'password') {
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            } else {
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            }
        });
        
        // Form submission with loading state
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = document.querySelector('.btn-text');
        const loadingSpinner = document.querySelector('.loading-spinner');
        
        loginForm.addEventListener('submit', function(e) {
            // Show loading state
            loginBtn.disabled = true;
            btnText.classList.add('hidden');
            loadingSpinner.classList.add('active');
            
            // For demo purposes - remove this in production
            e.preventDefault();
            
            // Simulate authentication process
            setTimeout(() => {
                // Reset button state
                loginBtn.disabled = false;
                btnText.classList.remove('hidden');
                loadingSpinner.classList.remove('active');
                
                // Show demo message
                showAlert('Demo: Authentication successful!', 'success');
                
                // In production, remove the above and let the form submit normally
            }, 2000);
        });
        
        // Input focus effects
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateX(5px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateX(0)';
            });
        });
        
        // Alert system
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alertDiv);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        // Check for URL parameters
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            const success = urlParams.get('success');
            
            if (error) {
                showAlert(decodeURIComponent(error), 'danger');
            }
            
            if (success) {
                showAlert(decodeURIComponent(success), 'success');
            }
        });
        
        // Real-time form validation
        const usernameInput = document.getElementById('username');
        const passwordInputField = document.getElementById('password');
        
        function validateForm() {
            const username = usernameInput.value.trim();
            const password = passwordInputField.value;
            
            if (username.length >= 3 && password.length >= 6) {
                loginBtn.style.opacity = '1';
                loginBtn.style.transform = 'translateY(0)';
            } else {
                loginBtn.style.opacity = '0.7';
            }
        }
        
        usernameInput.addEventListener('input', validateForm);
        passwordInputField.addEventListener('input', validateForm);
        
        // Initial validation
        validateForm();
        
        // Add smooth hover effects to form elements
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('mouseenter', function() {
                this.style.borderColor = '#94061b';
                this.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('mouseleave', function() {
                if (this !== document.activeElement) {
                    this.style.borderColor = '#e9ecef';
                    this.style.transform = 'scale(1)';
                }
            });
        });
    </script>
</body>
</html>