<?php
session_start();
include 'db.php';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validation flags
    $is_valid = true;
    $validation_errors = [];
    
    // Name validation
    if (empty($name)) {
        $is_valid = false;
        $validation_errors['name'] = "Name is required";
    } elseif (strlen($name) < 2) {
        $is_valid = false;
        $validation_errors['name'] = "Name must be at least 2 characters";
    }
    
    // Email validation
    if (empty($email)) {
        $is_valid = false;
        $validation_errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $is_valid = false;
        $validation_errors['email'] = "Please enter a valid email address";
    }
    
    // Password validation
    if (empty($password)) {
        $is_valid = false;
        $validation_errors['password'] = "Password is required";
    } elseif (strlen($password) < 6) {
        $is_valid = false;
        $validation_errors['password'] = "Password must be at least 6 characters";
    }
    
    // Confirm password validation
    if (empty($confirm_password)) {
        $is_valid = false;
        $validation_errors['confirm_password'] = "Please confirm your password";
    } elseif ($password !== $confirm_password) {
        $is_valid = false;
        $validation_errors['confirm_password'] = "Passwords do not match";
    }
    
    // Phone validation
    if (empty($phone)) {
        $is_valid = false;
        $validation_errors['phone'] = "Phone number is required";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $is_valid = false;
        $validation_errors['phone'] = "Please enter a valid phone number";
    }
    
    // Address validation
    if (empty($address)) {
        $is_valid = false;
        $validation_errors['address'] = "Address is required";
    } elseif (strlen($address) < 5) {
        $is_valid = false;
        $validation_errors['address'] = "Please enter a valid address";
    }
    
    // If all validations pass
    if ($is_valid) {
        // Use email as username
        $username = $email;
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $result = $checkEmail->get_result();

        if ($result->num_rows > 0) {
            $signup_error = "Email already registered! Please login.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, name, email, password, phone, address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssss", $username, $name, $email, $hashed_password, $phone, $address);

            if ($stmt->execute()) {
                $signup_success = "Signup successful! You can now login.";
                // Clear form data
                $name = $email = $phone = $address = "";
            } else {
                $signup_error = "Error occurred! Try again.";
            }
        }
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signin'])) {
    $email = trim($_POST['username']); // Using username field for email
    $password = $_POST['password'];
    
    // Reset validation errors for login
    $login_validation_errors = [];
    
    // Email validation
    if (empty($email)) {
        $login_validation_errors['username'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_validation_errors['username'] = "Please enter a valid email address";
    }
    
    // Password validation
    if (empty($password)) {
        $login_validation_errors['password'] = "Password is required";
    }
    
    // If no validation errors, check credentials
    if (empty($login_validation_errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $login_success = "Login successful! Redirecting...";
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 1500);
            </script>";
        } else {
            $login_error = "Invalid email or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registration</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary-color: #0a1d3b; /* Dark navy blue */
            --secondary-color: #1e3c72; /* Deep blue tone */
            --accent-color: #00bfff; /* Sky blue for highlights */
            --text-color: #ffffff;
            --background-color: #0b132b;
        }

        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100vh;
            overflow: hidden;
        }

        .container {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            height: 100vh;
        }

        .col {
            width: 50%;
        }

        .align-items-center {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .form-wrapper {
            width: 100%;
            max-width: 28rem;
        }

        .form {
            padding: 1rem;
            background-color: var(--white);
            border-radius: 1.5rem;
            width: 100%;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            transform: scale(0);
            transition: .5s ease-in-out;
            transition-delay: 1s;
        }

        .input-group {
            position: relative;
            width: 100%;
            margin: 1rem 0;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            font-size: 1.4rem;
            color: var(--gray-2);
        }

        .input-group input {
            width: 100%;
            padding: 1rem 3rem;
            font-size: 1rem;
            background-color: var(--gray);
            border-radius: .5rem;
            border: 0.125rem solid var(--white);
            outline: none;
        }

        .input-group input:focus {
            border: 0.125rem solid var(--primary-color);
        }

        .input-group input.error {
            border-color: #e74c3c;
        }

        .input-group input.success {
            border-color: #2ecc71;
        }

        .form button {
            cursor: pointer;
            width: 100%;
            padding: .6rem 0;
            border-radius: .5rem;
            border: none;
            background-color: var(--primary-color);
            color: white;
            font-size: 1.2rem;
            outline: none;
            transition: background-color 0.3s;
        }

        .form button:hover {
            background-color: var(--secondary-color);
        }

        .form p {
            margin: 1rem 0;
            font-size: .7rem;
        }

        .flex-col {
            flex-direction: column;
        }

        .social-list {
            margin: 2rem 0;
            padding: 1rem;
            border-radius: 1.5rem;
            width: 100%;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            transform: scale(0);
            transition: .5s ease-in-out;
            transition-delay: 1.2s;
        }

        .social-list>div {
            color: var(--white);
            margin: 0 .5rem;
            padding: .7rem;
            cursor: pointer;
            border-radius: .5rem;
            cursor: pointer;
            transform: scale(0);
            transition: .5s ease-in-out;
        }

        .social-list>div:nth-child(1) {
            transition-delay: 1.4s;
        }

        .social-list>div:nth-child(2) {
            transition-delay: 1.6s;
        }

        .social-list>div:nth-child(3) {
            transition-delay: 1.8s;
        }

        .social-list>div:nth-child(4) {
            transition-delay: 2s;
        }

        .social-list>div>i {
            font-size: 1.5rem;
            transition: .4s ease-in-out;
        }

        .social-list>div:hover i {
            transform: scale(1.5);
        }

        .facebook-bg {
            background-color: var(--facebook-color);
        }

        .google-bg {
            background-color: var(--google-color);
        }

        .twitter-bg {
            background-color: var(--twitter-color);
        }

        .insta-bg {
            background-color: var(--insta-color);
        }

        .pointer {
            cursor: pointer;
        }

        .container.sign-in .form.sign-in,
        .container.sign-in .social-list.sign-in,
        .container.sign-in .social-list.sign-in>div,
        .container.sign-up .form.sign-up,
        .container.sign-up .social-list.sign-up,
        .container.sign-up .social-list.sign-up>div {
            transform: scale(1);
        }

        .content-row {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 6;
            width: 100%;
        }

        .text {
            margin: 4rem;
            color: white;
        }

        .text h2 {
            font-size: 3.5rem;
            font-weight: 800;
            margin: 2rem 0;
            transition: 1s ease-in-out;
        }

        .text p {
            font-weight: 600;
            transition: 1s ease-in-out;
            transition-delay: .2s;
        }

        .img img {
            width: 30vw;
            transition: 1s ease-in-out;
            transition-delay: .4s;
        }

        .text.sign-in h2,
        .text.sign-in p,
        .img.sign-in img {
            transform: translateX(-250%);
        }

        .text.sign-up h2,
        .text.sign-up p,
        .img.sign-up img {
            transform: translateX(250%);
        }

        .container.sign-in .text.sign-in h2,
        .container.sign-in .text.sign-in p,
        .container.sign-in .img.sign-in img,
        .container.sign-up .text.sign-up h2,
        .container.sign-up .text.sign-up p,
        .container.sign-up .img.sign-up img {
            transform: translateX(0);
        }

        /* BACKGROUND */

        .container::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            height: 100vh;
            width: 300vw;
            transform: translate(35%, 0);
            background-image: linear-gradient(-45deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            transition: 1s ease-in-out;
            z-index: 6;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            border-bottom-right-radius: max(50vw, 50vh);
            border-top-left-radius: max(50vw, 50vh);
        }

        .container.sign-in::before {
            transform: translate(0, 0);
            right: 50%;
        }

        .container.sign-up::before {
            transform: translate(100%, 0);
            right: 50%;
        }

        /* Error and Success Messages */
        .error-message {
            color: #e74c3c;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            text-align: left;
            padding-left: 1rem;
        }

        .success-message {
            color: #2ecc71;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            text-align: left;
            padding-left: 1rem;
        }

        /* Alert styling */
        .alert {
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.5rem;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        /* Custom Alert Box */
        .custom-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.5s ease-out;
        }

        .alert-success-custom {
            background-color: #2ecc71;
        }

        .alert-error-custom {
            background-color: #e74c3c;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* RESPONSIVE */

        @media only screen and (max-width: 425px) {

            .container::before,
            .container.sign-in::before,
            .container.sign-up::before {
                height: 100vh;
                border-bottom-right-radius: 0;
                border-top-left-radius: 0;
                z-index: 0;
                transform: none;
                right: 0;
            }

            .container.sign-in .col.sign-in,
            .container.sign-up .col.sign-up {
                transform: translateY(0);
            }

            .content-row {
                align-items: flex-start !important;
            }

            .content-row .col {
                transform: translateY(0);
                background-color: unset;
            }

            .col {
                width: 100%;
                position: absolute;
                padding: 2rem;
                background-color: var(--white);
                border-top-left-radius: 2rem;
                border-top-right-radius: 2rem;
                transform: translateY(100%);
                transition: 1s ease-in-out;
            }

            .row {
                align-items: flex-end;
                justify-content: flex-end;
            }

            .form,
            .social-list {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .text {
                margin: 0;
            }

            .text p {
                display: none;
            
            }

            .text h2 {
                margin: .5rem;
                font-size: 2rem;
                
            }
        }
    </style>
</head>
<body>
    <div id="container" class="container">
        <!-- FORM SECTION -->
        <div class="row">
            <!-- SIGN UP -->
            <div class="col align-items-center flex-col sign-up">
                <div class="form-wrapper align-items-center">
                    <div class="form sign-up">
                        <form method="POST" id="signupForm">
                            <div class="input-group">
                                <i class='bx bxs-user'></i>
                                <input type="text" name="name" placeholder="Name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                                <?php if (isset($validation_errors['name'])): ?>
                                    <div class="error-message"><?php echo $validation_errors['name']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="input-group">
                                <i class='bx bx-mail-send'></i>
                                <input type="email" name="email" placeholder="Email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                <?php if (isset($validation_errors['email'])): ?>
                                    <div class="error-message"><?php echo $validation_errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="input-group">
                                <i class='bx bxs-lock-alt'></i>
                                <input type="password" name="password" placeholder="Password" required>
                                <?php if (isset($validation_errors['password'])): ?>
                                    <div class="error-message"><?php echo $validation_errors['password']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="input-group">
                                <i class='bx bxs-lock-alt'></i>
                                <input type="password" name="confirm_password" placeholder="Confirm password" required>
                                <?php if (isset($validation_errors['confirm_password'])): ?>
                                    <div class="error-message"><?php echo $validation_errors['confirm_password']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="input-group">
                                <i class='bx bx-phone'></i>
                                <input type="text" name="phone" placeholder="Phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                                <?php if (isset($validation_errors['phone'])): ?>
                                    <div class="error-message"><?php echo $validation_errors['phone']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="input-group">
                                <i class='bx bx-home'></i>
                                <input type="text" name="address" placeholder="Address" value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>" required>
                                <?php if (isset($validation_errors['address'])): ?>
                                    <div class="error-message"><?php echo $validation_errors['address']; ?></div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" name="signup">
                                Sign up
                            </button>
                            <?php if (isset($signup_error)): ?>
                                <div class="alert alert-danger"><?php echo $signup_error; ?></div>
                            <?php endif; ?>
                            <?php if (isset($signup_success)): ?>
                                <div class="alert alert-success"><?php echo $signup_success; ?></div>
                            <?php endif; ?>
                            <p>
                                <span>
                                    Already have an account?
                                </span>
                                <b onclick="toggle()" class="pointer">
                                    Sign in here
                                </b>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
            <!-- END SIGN UP -->
            <!-- SIGN IN -->
            <div class="col align-items-center flex-col sign-in">
                <div class="form-wrapper align-items-center">
                    <div class="form sign-in">
                        <form method="POST" id="signinForm">
                            <div class="input-group">
                                <i class='bx bx-mail-send'></i>
                                <input type="email" name="username" placeholder="Email" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                <?php if (isset($login_validation_errors['username'])): ?>
                                    <div class="error-message"><?php echo $login_validation_errors['username']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="input-group">
                                <i class='bx bxs-lock-alt'></i>
                                <input type="password" name="password" placeholder="Password" required>
                                <?php if (isset($login_validation_errors['password'])): ?>
                                    <div class="error-message"><?php echo $login_validation_errors['password']; ?></div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" name="signin">
                                Sign in
                            </button>
                            <?php if (isset($login_error)): ?>
                                <div class="alert alert-danger"><?php echo $login_error; ?></div>
                            <?php endif; ?>
                            <?php if (isset($login_success)): ?>
                                <div class="alert alert-success"><?php echo $login_success; ?></div>
                            <?php endif; ?>
                            <p>
                                <b>
                                    Forgot password?
                                </b>
                            </p>
                            <p>
                                <span>
                                    Don't have an account?
                                </span>
                                <b onclick="toggle()" class="pointer">
                                    Sign up here
                                </b>
                            </p>
                        </form>
                    </div>
                </div>
                <div class="form-wrapper">
                </div>
            </div>
            <!-- END SIGN IN -->
        </div>
        <!-- END FORM SECTION -->
        <!-- CONTENT SECTION -->
        <div class="row content-row">
            <!-- SIGN IN CONTENT -->
            <div class="col align-items-center flex-col">
                <div class="text sign-in">
                    <h2 >
                        Welcome
                    </h2>
                    <p >
                        Sign in to access your account and enjoy our services.
                    </p>
                </div>
                <div class="img sign-in">
                    <!-- You can add an image here if needed -->
                </div>
            </div>
            <!-- END SIGN IN CONTENT -->
            <!-- SIGN UP CONTENT -->
            <div class="col align-items-center flex-col">
                <div class="img sign-up">
                    <!-- You can add an image here if needed -->
                </div>
                <div class="text sign-up">
                    <h2>
                        Join with us
                    </h2>
                    <p>
                        Create an account to get started with our amazing services.
                    </p>
                </div>
            </div>
            <!-- END SIGN UP CONTENT -->
        </div>
        <!-- END CONTENT SECTION -->
    </div>

    <script>
        let container = document.getElementById('container')

        toggle = () => {
            container.classList.toggle('sign-in')
            container.classList.toggle('sign-up')
        }

        setTimeout(() => {
            container.classList.add('sign-in')
        }, 200)
        
        // Show custom alert function
        function showAlert(message, type) {
            // Remove any existing alerts
            const existingAlert = document.querySelector('.custom-alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            // Create new alert
            const alert = document.createElement('div');
            alert.className = `custom-alert alert-${type}-custom`;
            alert.textContent = message;
            document.body.appendChild(alert);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                alert.remove();
            }, 3000);
        }
        
        // Check for PHP messages and show alerts
        <?php if (isset($signup_success)): ?>
            showAlert('<?php echo $signup_success; ?>', 'success');
            // Switch to login form after successful registration
            setTimeout(() => {
                container.classList.add('sign-in');
                container.classList.remove('sign-up');
            }, 2000);
        <?php endif; ?>
        
        <?php if (isset($signup_error)): ?>
            showAlert('<?php echo $signup_error; ?>', 'error');
        <?php endif; ?>
        
        <?php if (isset($login_success)): ?>
            showAlert('<?php echo $login_success; ?>', 'success');
        <?php endif; ?>
        
        <?php if (isset($login_error)): ?>
            showAlert('<?php echo $login_error; ?>', 'error');
        <?php endif; ?>
        
        // Form validation for password confirmation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('Passwords do not match!', 'error');
                return false;
            }
            
            // Additional client-side validation
            const name = document.querySelector('input[name="name"]').value;
            const email = document.querySelector('input[name="email"]').value;
            const phone = document.querySelector('input[name="phone"]').value;
            const address = document.querySelector('input[name="address"]').value;
            
            if (name.length < 2) {
                e.preventDefault();
                showAlert('Name must be at least 2 characters long', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('Password must be at least 6 characters long', 'error');
                return false;
            }
            
            if (!/^[0-9]{10,15}$/.test(phone)) {
                e.preventDefault();
                showAlert('Please enter a valid phone number (10-15 digits)', 'error');
                return false;
            }
            
            if (address.length < 5) {
                e.preventDefault();
                showAlert('Please enter a valid address', 'error');
                return false;
            }
        });
        
        // Login form validation - REMOVED the problematic validation
        // The PHP validation is sufficient for login
        
        // Real-time validation for signup form
        document.querySelectorAll('#signupForm input').forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
        
        function validateField(field) {
            const value = field.value.trim();
            let isValid = true;
            let errorMessage = '';
            
            switch(field.name) {
                case 'name':
                    if (value.length < 2) {
                        isValid = false;
                        errorMessage = 'Name must be at least 2 characters';
                    }
                    break;
                case 'email':
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                    break;
                case 'password':
                    if (value.length < 6) {
                        isValid = false;
                        errorMessage = 'Password must be at least 6 characters';
                    }
                    break;
                case 'confirm_password':
                    const password = document.querySelector('input[name="password"]').value;
                    if (value !== password) {
                        isValid = false;
                        errorMessage = 'Passwords do not match';
                    }
                    break;
                case 'phone':
                    if (!/^[0-9]{10,15}$/.test(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid phone number';
                    }
                    break;
                case 'address':
                    if (value.length < 5) {
                        isValid = false;
                        errorMessage = 'Please enter a valid address';
                    }
                    break;
            }
            
            // Update field styling
            if (isValid) {
                field.classList.remove('error');
                field.classList.add('success');
            } else {
                field.classList.remove('success');
                field.classList.add('error');
            }
            
            // Update error message
            let errorDiv = field.parentNode.querySelector('.error-message');
            if (!isValid) {
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    field.parentNode.appendChild(errorDiv);
                }
                errorDiv.textContent = errorMessage;
            } else if (errorDiv) {
                errorDiv.remove();
            }
        }
    </script>
</body>
</html>