// Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Login page loaded');
            
            // DOM Elements
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const loginForm = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const rememberMeCheckbox = document.getElementById('rememberMe');
            const alertMessageDiv = document.getElementById('alertMessage');
            
            // ========================================
            // PASSWORD TOGGLE
            // ========================================
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('fa-eye-slash');
                    this.classList.toggle('fa-eye');
                });
            }
            
            // ========================================
            // COOKIE FUNCTIONS
            // ========================================
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
                return null;
            }
            
            function setCookie(name, value, days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                document.cookie = `${name}=${value}; expires=${date.toUTCString()}; path=/`;
            }
            
            // ========================================
            // LOAD SAVED EMAIL
            // ========================================
            function loadSavedEmail() {
                const savedEmail = getCookie('user_email');
                if (savedEmail && emailInput) {
                    emailInput.value = savedEmail;
                    if (rememberMeCheckbox) {
                        rememberMeCheckbox.checked = true;
                    }
                }
            }
            
            // ========================================
            // SHOW ALERT
            // ========================================
            function showAlert(message, type) {
                if (alertMessageDiv) {
                    const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
                    alertMessageDiv.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
                    alertMessageDiv.className = `alert alert-${type}`;
                    alertMessageDiv.style.display = 'block';
                    
                    setTimeout(() => {
                        alertMessageDiv.style.display = 'none';
                    }, 4000);
                } else {
                    alert(message);
                }
            }
            
            // ========================================
            // FORM SUBMISSION
            // ========================================
            if (loginForm) {
                loginForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const email = emailInput ? emailInput.value.trim() : '';
                    const password = passwordInput ? passwordInput.value : '';
                    const rememberMe = rememberMeCheckbox ? rememberMeCheckbox.checked : false;
                    
                    console.log('Submitting login for:', email);
                    
                    if (!email || !password) {
                        showAlert('Please enter both email and password', 'error');
                        return;
                    }
                    
                    // Show loading state
                    const submitBtn = loginForm.querySelector('button');
                    const originalText = submitBtn.textContent;
                    submitBtn.textContent = 'Signing in...';
                    submitBtn.disabled = true;
                    
                    try {
                        const response = await fetch('backend/api/login.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                email: email,
                                password: password,
                                rememberMe: rememberMe
                            })
                        });
                        
                        const result = await response.json();
                        console.log('Response:', result);
                        
                        if (result.success) {
                            showAlert(result.message, 'success');
                            
                            if (rememberMe) {
                                setCookie('user_email', email, 30);
                            }
                            
                            setTimeout(() => {
                                window.location.href = result.redirect;
                            }, 1500);
                        } else {
                            showAlert(result.message, 'error');
                            submitBtn.textContent = originalText;
                            submitBtn.disabled = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showAlert('Connection error: ' + error.message, 'error');
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    }
                });
            }
            
            // Load saved email
            loadSavedEmail();
        });