<?php
require_once 'functions.php';

if (isLoggedIn()) {
    redirectToDashboard();
}

$username = $password = $role = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate role
    if (empty(trim($_POST["role"]))) {
        $login_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err) && empty($login_err)) {
        $sql = "SELECT user_id, username, password_hash, role, full_name FROM users WHERE username = ? AND role = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $param_username, $param_role);
            $param_username = $username;
            $param_role = $role;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($user_id, $username, $hashed_password, $user_role, $full_name);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $user_id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $user_role;
                            $_SESSION["full_name"] = $full_name; // Store full name

                            logAudit($user_id, 'login', $user_id, 'User logged in successfully.');

                            redirectToDashboard();
                        } else {
                            $login_err = "Invalid username, password, or role.";
                        }
                    }
                } else {
                    $login_err = "Invalid username, password, or role.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BookHive</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</head>
<body>
    <div class="min-h-screen bg-gradient-to-br from-[var(--background)] via-[var(--muted)] to-[var(--accent)]">
        <!-- Header - Updated to match image -->
        <header class="login-header">
            <div class="header-content">
                <a href="index.php" class="back-button">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back to Home</span>
                </a>
                
                <div class="logo-section-right">
                    <div class="logo-icon">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                    <span class="logo-text">BookHive</span>
                </div>
            </div>
        </header>

        <!-- Spacing between header and login box -->
        <div class="header-spacing"></div>

        <!-- Login Form -->
        <div class="login-container">
            <div class="auth-container">
                <div class="welcome-section">
                    <div class="icon-container">
                        <i data-lucide="book-open" class="w-8 h-8 text-white"></i>
                    </div>
                    <h2>Welcome Back</h2>
                    <p>Sign in to access your BookHive account</p>
                </div>

                <?php
                if (!empty($login_err)) {
                    echo '<div class="alert alert-danger">' . $login_err . '</div>';
                }
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login-form">
                    <!-- Role Selection - Dropdown with no default selection -->
                    <div class="form-group">
                        <label for="role">I am at:</label>
                        <select name="role" id="role" class="form-control <?php echo (!empty($login_err)) ? 'is-invalid' : ''; ?>">
                            <option value="">Select role</option>
                            <option value="student" <?php echo (isset($role) && $role == 'student') ? 'selected' : ''; ?>>☞ Student</option>
                            <option value="librarian" <?php echo (isset($role) && $role == 'librarian') ? 'selected' : ''; ?>>Librarian</option>
                        </select>
                        <?php if (!empty($login_err)): ?>
                            <span class="invalid-feedback"><?php echo $login_err; ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Email Field - Empty placeholder -->
                    <div class="form-group">
                        <label for="username">Email Address</label>
                        <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" placeholder=" ">
                        <?php if (!empty($username_err)): ?>
                            <span class="invalid-feedback"><?php echo $username_err; ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Enter your password">
                        <?php if (!empty($password_err)): ?>
                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Sign In Button -->
                    <div class="form-group">
                        <button type="submit" class="btn-login" id="login-button">
                            Sign in
                        </button>
                    </div>

                    <div class="form-links">
                        <a href="#" class="btn-link">Forgot Password?</a>
                        <a href="signup.php" class="btn-link">Create Account</a>
                    </div>
                </form>

                <!-- Demo Credentials -->
                <div class="demo-credentials">
                    <p class="title">Demo Credentials:</p>
                    <div class="credentials-list">
                        <div class="credential-item">
                            <span class="credential-line">
                                <strong class="student">Student:</strong>
                                <span class="credential-details">student@school.edu / password</span>
                            </span>
                        </div>
                        <div class="credential-item">
                            <span class="credential-line">
                                <strong class="librarian">Librarian:</strong>
                                <span class="credential-details">librarian@school.edu / password</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update button text when role changes
        document.getElementById('role').addEventListener('change', function() {
            const button = document.getElementById('login-button');
            if (this.value === 'student') {
                button.textContent = 'Sign in as Student';
            } else if (this.value === 'librarian') {
                button.textContent = 'Sign in as Librarian';
            } else {
                button.textContent = 'Sign in';
            }
        });
    </script>
</body>
</html>
