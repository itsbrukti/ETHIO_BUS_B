// ========================================
// LOGIN PAGE SPECIFIC JAVASCRIPT
// ========================================

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Login page loaded');
    initLoginFunctionality();
});

function initLoginFunctionality() {
    // DOM Elements
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const rememberMeCheckbox = document.getElementById('rememberMe');
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const alertMessageDiv = document.getElementById('alertMessage');

    console.log('Elements found:', {
        emailInput: !!emailInput,
        passwordInput: !!passwordInput,
        togglePasswordBtn: !!togglePasswordBtn,
        loginForm: !!loginForm
    });

    // ========================================
    // PASSWORD TOGGLE FUNCTIONALITY
    // ========================================
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function() {
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
    // SHOW ALERT MESSAGE
    // ========================================
    function showAlert(message, type) {
        console.log('Alert:', type, message);
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
    // HANDLE LOGIN FORM SUBMISSION
    // ========================================
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Form submitted');
            
            const email = emailInput ? emailInput.value.trim() : '';
            const password = passwordInput ? passwordInput.value : '';
            const rememberMe = rememberMeCheckbox ? rememberMeCheckbox.checked : false;
            
            console.log('Email:', email);
            console.log('Password length:', password.length);
            
            // Validation
            if (!email || !password) {
                showAlert('Please enter both email and password', 'error');
                return;
            }
            
            // Show loading state
            const originalText = loginBtn.textContent;
            loginBtn.textContent = 'Signing in...';
            loginBtn.disabled = true;
            
            try {
                console.log('Sending request to backend...');
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
                
                console.log('Response status:', response.status);
                const result = await response.json();
                console.log('Response data:', result);
                
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
                    loginBtn.textContent = originalText;
                    loginBtn.disabled = false;
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert('Connection error: ' + error.message, 'error');
                loginBtn.textContent = originalText;
                loginBtn.disabled = false;
            }
        });
    } else {
        console.error('Login form not found!');
    }
    
    loadSavedEmail();
}