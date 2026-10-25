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
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            padding: 1rem;
        }

        .login-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.3), 0 8px 10px -6px rgb(0 0 0 / 0.3);
            animation: cardAppear 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            color: #f3f4f6;
        }

        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-logo {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .brand-logo span {
            background: linear-gradient(135deg, #818cf8 0%, #4f46e5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            background-color: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #94a3b8 !important;
            border-top-left-radius: 12px !important;
            border-bottom-left-radius: 12px !important;
        }

        .form-control {
            background-color: rgba(15, 23, 42, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
            border-top-right-radius: 12px !important;
            border-bottom-right-radius: 12px !important;
            padding: 0.75rem 1rem !important;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25) !important;
        }

        .btn-login {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .test-account-badge {
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 0.5rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: background-color 0.2s;
            color: #cbd5e1;
        }

        .test-account-badge:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }
        
        #global-spinner {
            z-index: 9999;
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
            <p class="text-muted small">Smart Transport Operations Management</p>
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
        <div class="border-top border-secondary pt-3">
            <div class="small text-muted mb-2 text-center">Quick Access Test Accounts:</div>
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
            <div class="text-center mt-2 small text-light" style="font-size: 0.7rem; opacity: 0.7;">
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
