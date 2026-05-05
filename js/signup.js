// ========================================
// SIGNUP.JS - With Database Integration
// ========================================

function initSignupForm() {
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const name = document.getElementById('fullname').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const role = document.getElementById('userType').value;
            const termsAccepted = document.getElementById('termsCheckbox').checked;
            
            // Client-side validation
            if (!name || !email || !phone || !password) {
                showError('Please fill all required fields');
                return;
            }
            
            if (password !== confirmPassword) {
                showError('Passwords do not match');
                return;
            }
            
            if (password.length < 6) {
                showError('Password must be at least 6 characters');
                return;
            }
            
            if (!termsAccepted) {
                showError('Please accept the Terms and Conditions');
                return;
            }
            
            if (!email.includes('@gmail.com')) {
                showError('Email must be a Gmail address (@gmail.com)');
                return;
            }
            
            // Password strength check
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            
            if (!hasUpper || !hasLower || !hasNumber) {
                showError('Password must contain uppercase, lowercase, and number');
                return;
            }
            
            // Show loading
            const submitBtn = signupForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Creating Account...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('backend/api/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: name,
                        email: email,
                        phone: phone,
                        password: password,
                        confirmPassword: confirmPassword,
                        role: role
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess(result.message);
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                } else {
                    showError(result.message);
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                showError('Connection error. Please try again.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
    }
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 4000);
    } else {
        alert(message);
    }
}

function showSuccess(message) {
    const successDiv = document.getElementById('successMessage');
    if (successDiv) {
        successDiv.textContent = message;
        successDiv.style.display = 'block';
    }
}

function initPasswordStrength() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirmPassword');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', (e) => {
            const password = e.target.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            if (strengthBar) {
                let strength = 0;
                if (password.length >= 6) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                
                const width = (strength / 4) * 100;
                strengthBar.style.width = width + '%';
                
                if (strength < 2) strengthBar.style.background = '#ef4444';
                else if (strength < 3) strengthBar.style.background = '#f59e0b';
                else strengthBar.style.background = '#10b981';
            }
        });
    }
    
    if (confirmInput) {
        confirmInput.addEventListener('input', () => {
            if (passwordInput.value !== confirmInput.value) {
                confirmInput.style.borderColor = '#ef4444';
            } else {
                confirmInput.style.borderColor = '#10b981';
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initSignupForm();
    initPasswordStrength();
});