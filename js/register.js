// Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
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
        });
        
        // Form submission
        const registerForm = document.getElementById('registerForm');
        
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const fullname = document.getElementById('fullname').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validation
            if (!fullname || !email || !phone || !password) {
                showAlert('Please fill all fields', 'error');
                return;
            }
            
            if (password !== confirmPassword) {
                showAlert('Passwords do not match', 'error');
                return;
            }
            
            if (password.length < 6) {
                showAlert('Password must be at least 6 characters', 'error');
                return;
            }
            
            if (!email.includes('@gmail.com')) {
                showAlert('Email must be a Gmail address (@gmail.com)', 'error');
                return;
            }
            
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            
            if (!hasUpper || !hasLower || !hasNumber) {
                showAlert('Password must contain uppercase, lowercase, and number', 'error');
                return;
            }
            
            const submitBtn = registerForm.querySelector('button');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Creating Account...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('backend/api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        fullname: fullname,
                        email: email,
                        phone: phone,
                        password: password,
                        confirmPassword: confirmPassword
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    showAlert(result.message, 'error');
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                showAlert('Connection error. Please try again.', 'error');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
        
        function showAlert(message, type) {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.textContent = message;
            alertDiv.className = `alert alert-${type}`;
            alertDiv.style.display = 'block';
            
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 4000);
        }