<?php
// TransitOps Login Page View
if (!defined('SECURE_ACCESS')) exit;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | TransitOps</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            color-scheme: light;
            --login-ink: #172554;
            --login-muted: #64748b;
            --login-primary: #4f46e5;
            --login-border: #dbe4f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            color: var(--login-ink);
            background:
                radial-gradient(ellipse at 12% 12%, rgba(191, 219, 254, 0.7), transparent 38%),
                radial-gradient(ellipse at 88% 85%, rgba(221, 214, 254, 0.65), transparent 36%),
                linear-gradient(135deg, #f8fbff 0%, #eef4ff 52%, #f7f5ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            padding: 2rem 1rem;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 24px;
            padding: clamp(1.5rem, 5vw, 2.75rem);
            width: 100%;
            max-width: 440px;
            box-shadow: 0 24px 70px rgba(51, 65, 105, 0.14), 0 4px 14px rgba(51, 65, 105, 0.06);
            animation: cardAppear 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            color: var(--login-ink);
        }

        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-logo {
            font-size: clamp(1.9rem, 8vw, 2.2rem);
            font-weight: 800;
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 0.6rem;
        }

        .brand-logo span {
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-logo i {
            color: #4f46e5 !important;
        }

        .login-subtitle {
            color: var(--login-muted) !important;
            font-size: 0.95rem !important;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            min-width: 3.2rem;
            justify-content: center;
            background-color: #f8faff !important;
            border: 1px solid var(--login-border) !important;
            color: #64748b !important;
            border-top-left-radius: 12px !important;
            border-bottom-left-radius: 12px !important;
        }

        .form-control {
            min-height: 3.2rem;
            background-color: #fff !important;
            border: 1px solid var(--login-border) !important;
            color: #172554 !important;
            border-top-right-radius: 12px !important;
            border-bottom-right-radius: 12px !important;
            padding: 0.75rem 1rem !important;
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            border-color: #818cf8 !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.13) !important;
        }

        .form-control:user-invalid {
            border-color: #ef4444 !important;
        }

        .btn-login {
            min-height: 3.15rem;
            background: linear-gradient(135deg, #5b62eb 0%, #4338ca 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            letter-spacing: 0.01em;
            transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
            box-shadow: 0 8px 18px rgba(79, 70, 229, 0.22);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 11px 24px rgba(79, 70, 229, 0.28);
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            color: #fff;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .test-account-badge {
            background-color: #f8faff;
            border: 1px solid var(--login-border);
            border-radius: 10px;
            padding: 0.5rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s, color 0.2s;
            color: #475569;
        }

        .test-account-badge:hover {
            background-color: #eef2ff;
            border-color: #a5b4fc;
            color: #3730a3;
        }

        .test-account-divider {
            border-color: #e2e8f0 !important;
        }

        .test-account-hint {
            color: #64748b !important;
            opacity: 1 !important;
        }

        #global-spinner {
            z-index: 9999;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        @media (max-width: 420px) {
            .login-card {
                border-radius: 18px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center mb-4">
            <h1 class="brand-logo">
                <i class="fas fa-truck-moving text-primary"></i>
                <span>TransitOps</span>
            </h1>
            <p class="login-subtitle small">Smart Transport Operations Management</p>
        </div>

        <form id="loginForm" action="<?php echo BASE_URL; ?>/login" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@company.com" maxlength="254" required autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" maxlength="4096" required autocomplete="current-password">
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100 mb-4 py-2.5">
                Sign In
            </button>
        </form>

        <?php if (DEV_MODE): ?>
        <div class="border-top test-account-divider pt-3">
            <div class="small text-muted mb-2 text-center">Quick Access Test Accounts</div>
            <div class="row g-2">
                <div class="col-6">
                    <div class="test-account-badge text-center" onclick="fillCredentials('admin@transitops.com')">
                        <strong>Super Admin</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="test-account-badge text-center" onclick="fillCredentials('manager@transitops.com')">
                        <strong>Fleet Manager</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="test-account-badge text-center" onclick="fillCredentials('dispatcher@transitops.com')">
                        <strong>Dispatcher</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="test-account-badge text-center" onclick="fillCredentials('finance@transitops.com')">
                        <strong>Finance Manager</strong>
                    </div>
                </div>
            </div>
            <div class="text-center mt-2 small test-account-hint" style="font-size: 0.7rem;">
                (Password is: <strong>admin123</strong>)
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Spinner -->
    <div id="global-spinner" class="position-fixed top-0 start-0 w-100 h-100 d-none d-flex align-items-center justify-content-center bg-dark bg-opacity-75">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';

        // Reusable simple AjaxOps clone locally for the login page
        const LoginOps = {
            showSpinner: () => document.getElementById('global-spinner').classList.remove('d-none'),
            hideSpinner: () => document.getElementById('global-spinner').classList.add('d-none'),
            toast: (msg, type = 'success') => {
                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                }).fire({ icon: type, title: msg });
            }
        };

        <?php if (DEV_MODE): ?>
        function fillCredentials(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'admin123';
        }
        <?php endif; ?>

        // Handle Form Submit via AJAX
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            LoginOps.showSpinner();
            
            const formData = new FormData(this);
            fetch(this.getAttribute('action'), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                LoginOps.hideSpinner();
                if (data.success) {
                    LoginOps.toast(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    Swal.fire({
                        title: 'Authentication Failed',
                        text: data.error || 'Invalid credentials.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                }
            })
            .catch(err => {
                LoginOps.hideSpinner();
                Swal.fire('Error', 'Unable to connect to server. Please try again.', 'error');
            });
        });
    </script>
</body>
</html>
