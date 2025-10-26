<?php
// Temporary debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'functions.php';
// Handle AJAX request for librarian check
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_librarian'])) {
    header('Content-Type: application/json');
    $librarian_check_sql = "SELECT COUNT(*) FROM users WHERE role = 'librarian'";
    $librarian_check_result = $conn->query($librarian_check_sql);
    if ($librarian_check_result) {
        $count = $librarian_check_result->fetch_row()[0];
        echo json_encode(['exists' => $count > 0]);
    } else {
        echo json_encode(['error' => 'Database query failed.']);
    }
    exit;  // Stop further execution for AJAX
}
if (isLoggedIn()) {
    redirectToDashboard();
}

$username = $email = $lrn = $full_name = $year_level = $password = $confirm_password = $role = "";
$username_err = $email_err = $lrn_err = $full_name_err = $year_level_err = $password_err = $confirm_password_err = $role_err = "";
$signup_err = $success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate role first (always required)
    if (empty(trim($_POST["role"]))) {
        $role_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
        if (!in_array($role, ['student', 'librarian'])) {
            $role_err = "Invalid role selected.";
        }
    }

    // Validate username (for both roles)
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else {
        $param_username = trim($_POST["username"]);
        $sql = "SELECT user_id FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                $username_err = "This username is already taken.";
            } else {
                $username = $param_username;
            }
            $stmt->close();
        } else {
            $signup_err = "Database error (username check): " . $conn->error;
        }
    }

    // Validate email (for both roles)
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        $param_email = trim($_POST["email"]);
        $sql = "SELECT user_id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                $email_err = "This email is already registered.";
            } else {
                $email = $param_email;
            }
            $stmt->close();
        } else {
            $signup_err = "Database error (email check): " . $conn->error;
        }
    }

    // Validate full name (for both roles - always required)
    if (empty(trim($_POST["full_name"]))) {
        $full_name_err = "Please enter your full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
    }

    // Student-only validations (skip for librarians)
    $lrn_err = $year_level_err = "";
    if ($role === 'student') {
        // Validate LRN
        if (empty(trim($_POST["lrn"]))) {
            $lrn_err = "Please enter your LRN.";
        } elseif (!preg_match('/^\d{12}$/', trim($_POST["lrn"]))) {
            $lrn_err = "LRN must be exactly 12 digits.";
        } else {
            $param_lrn = trim($_POST["lrn"]);
            $sql = "SELECT user_id FROM users WHERE lrn = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $param_lrn);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $lrn_err = "This LRN is already registered.";
                } else {
                    $lrn = $param_lrn;
                }
                $stmt->close();
            } else {
                $signup_err = "Database error (LRN check): " . $conn->error;
            }
        }

        // Validate year level
        if (empty(trim($_POST["year_level"]))) {
            $year_level_err = "Please enter your year level (e.g., Grade 12).";
        } else {
            $year_level = trim($_POST["year_level"]);
        }
    } else {
        // For librarians, explicitly clear student fields to avoid submission issues
        $lrn = $year_level = "";
    }

    // Validate password (for both roles)
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 8) {
        $password_err = "Password must have at least 8 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm your password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password !== $confirm_password)) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

         // If no errors, insert into database (dynamic query based on role)
     if (empty($role_err) && empty($username_err) && empty($email_err) && empty($full_name_err) && empty($lrn_err) && empty($year_level_err) && empty($password_err) && empty($confirm_password_err) && empty($signup_err)) {
         // NEW: Check for existing librarian (only allow one)
         if ($role === 'librarian') {
             $librarian_check_sql = "SELECT COUNT(*) FROM users WHERE role = 'librarian'";
             $librarian_check_result = $conn->query($librarian_check_sql);
             if ($librarian_check_result && $librarian_check_result->fetch_row()[0] > 0) {
                 $signup_err = "A librarian account already exists. Only one librarian is allowed per system.";
             }
         }

         // Proceed only if no librarian restriction error
         if (empty($signup_err)) {
             $hashed_password = password_hash($password, PASSWORD_DEFAULT);  // Secure bcrypt

             if ($role === 'student') {
                 // Full INSERT for students (includes lrn and year_level)
                 $sql = "INSERT INTO users (username, password_hash, email, role, lrn, full_name, year_level) VALUES (?, ?, ?, ?, ?, ?, ?)";
                 if ($stmt = $conn->prepare($sql)) {
                     $stmt->bind_param("sssssss", $param_username, $param_password, $param_email, $param_role, $param_lrn, $param_full_name, $param_year_level);
                     $param_username = $username;
                     $param_password = $hashed_password;
                     $param_email = $email;
                     $param_role = $role;
                     $param_lrn = $lrn;
                     $param_full_name = $full_name;
                     $param_year_level = $year_level;

                     // Debug: Log values (remove in production)
                     error_log("Student INSERT values: username=$username, email=$email, lrn=$lrn, year_level=$year_level");

                     if ($stmt->execute()) {
                         $new_user_id = $conn->insert_id;
                         if (function_exists('logAudit')) {
                             logAudit($new_user_id, 'signup', $new_user_id, "New $role account created: $username");
                         }
                         $success_msg = "Account created successfully! Redirecting to login...";
                         echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 2000);</script>";
                     } else {
                         $signup_err = "Student INSERT failed: " . $stmt->error . " | Full error: " . $conn->error;
                     }
                     $stmt->close();
                 } else {
                     $signup_err = "Student prepare failed: " . $conn->error;
                 }
             } else {
                 // Simplified INSERT for librarians (omits lrn and year_level to avoid NULL issues)
                 $sql = "INSERT INTO users (username, password_hash, email, role, full_name) VALUES (?, ?, ?, ?, ?)";
                 if ($stmt = $conn->prepare($sql)) {
                     $stmt->bind_param("sssss", $param_username, $param_password, $param_email, $param_role, $param_full_name);
                     $param_username = $username;
                     $param_password = $hashed_password;
                     $param_email = $email;
                     $param_role = $role;
                     $param_full_name = $full_name;

                     // Debug: Log values (remove in production)
                     error_log("Librarian INSERT values: username=$username, email=$email, role=$role");

                     if ($stmt->execute()) {
                         $new_user_id = $conn->insert_id;
                         if (function_exists('logAudit')) {
                             logAudit($new_user_id, 'signup', $new_user_id, "New $role account created: $username");
                         }
                         $success_msg = "Librarian account created successfully! Redirecting to login...";
                         echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 2000);</script>";
                     } else {
                         $signup_err = "Librarian INSERT failed: " . $stmt->error . " | Full error: " . $conn->error;
                     }
                     $stmt->close();
                 } else {
                     $signup_err = "Librarian prepare failed: " . $conn->error;
                 }
             }
         }
     }
     
    // Close connection after all operations
    if (isset($conn)) {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - BookHive</title>
    <style>
        :root {
            --primary: #BD1B19;
            --primary-dark: #A01513;
            --secondary: #D89233;
            --accent: #E8B14A;
            --background: #E7DFD2;
            --card-bg: #FDF9F3;
            --text-primary: #2B2B2B;
            --text-secondary: #666666;
            --text-muted: #8B7355;
            --border: #E5E5E5;
            --success: #28A745;
            --danger: #DC3545;
            --warning: #FFC107;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .min-h-screen {
            min-height: 100vh;
        }

        /* Header Styles */
        .login-header {
            background: var(--primary);
            padding: 20px 32px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        .back-button {
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .logo-section-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--secondary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: white;
        }

        /* Main Container */
        .flex {
            display: flex;
        }

        .items-center {
            align-items: center;
        }

        .justify-center {
            justify-content: center;
        }

        .px-4 {
            padding-left: 16px;
            padding-right: 16px;
        }

        .py-20 {
            padding-top: 80px;
            padding-bottom: 80px;
        }

        /* Auth Container */
        .auth-container {
            max-width: 480px;
            width: 100%;
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            padding: 48px;
            border: 1px solid rgba(216, 146, 51, 0.2);
        }

        .card {
            background: var(--card-bg);
        }

        /* Welcome Section */
        .space-y-1 > * + * {
            margin-top: 4px;
        }

        .text-center {
            text-align: center;
        }

        .pb-6 {
            padding-bottom: 24px;
        }

        .w-16 {
            width: 64px;
        }

        .h-16 {
            height: 64px;
        }

        .bg-gradient-to-br {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .rounded-2xl {
            border-radius: 16px;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .flex.items-center.justify-center {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .text-3xl {
            font-size: 28px;
            font-weight: 700;
        }

        .text-foreground {
            color: var(--text-primary);
        }

        .text-muted-foreground {
            color: var(--text-muted);
        }

        /* Form Styles */
        .space-y-4 > * + * {
            margin-top: 16px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .text-destructive {
            color: var(--danger);
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #E7DFD2;
            border-radius: 12px;
            background: #FDF9F3;
            color: var(--text-primary);
            font-size: 16px;
            transition: all 0.3s;
            line-height: 1.5;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(189, 27, 25, 0.1);
            transform: translateY(-1px);
        }

        .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        .is-invalid {
            border-color: var(--danger);
        }

        .invalid-feedback {
            display: block;
            color: var(--danger);
            font-size: 14px;
            margin-top: 6px;
            font-weight: 500;
        }

        small {
            font-size: 12px;
        }

        .block {
            display: block;
        }

        .mt-1 {
            margin-top: 4px;
        }

        /* Button */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 16px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(189, 27, 25, 0.4);
        }

        .w-full {
            width: 100%;
        }

        .py-6 {
            padding-top: 24px;
            padding-bottom: 24px;
        }

        .rounded-xl {
            border-radius: 12px;
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
            border: 1px solid;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.08);
            color: var(--success);
            border-color: rgba(40, 167, 69, 0.3);
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.08);
            color: var(--danger);
            border-color: rgba(220, 53, 69, 0.3);
        }

        .text-sm {
            font-size: 14px;
        }

        .hover\:underline:hover {
            text-decoration: underline;
        }

        /* Student Fields Animation */
        #student-fields {
            transition: all 0.3s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-container {
                margin: 16px;
                padding: 40px 32px;
                max-width: 100%;
            }

            .login-header {
                padding: 16px 24px;
            }

            .header-content {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }

            .logo-section-right {
                margin-left: 0;
                order: -1;
            }

            .back-button {
                align-self: flex-start;
            }

            .text-3xl {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .auth-container {
                padding: 32px 24px;
            }

            .py-20 {
                padding-top: 40px;
                padding-bottom: 40px;
            }

            .form-control {
                padding: 12px 14px;
                font-size: 15px;
            }

            .btn {
                padding: 14px 20px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-background">
    <!-- Header -->
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

    <!-- Main Content -->
    <div class="flex items-center justify-center px-4 py-20">
        <div class="auth-container card">
            <!-- Welcome Section -->
            <div class="space-y-1 text-center pb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-primary to-secondary rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
                    <i data-lucide="user-plus" class="w-8 h-8 text-white"></i>
                </div>
                <h2 class="text-3xl text-foreground">Join BookHive</h2>
                <p class="text-muted-foreground">
                    Create your account to start exploring our library
                </p>
            </div>

            <!-- Success/Error Messages -->
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success text-center mb-4"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if (!empty($signup_err)): ?>
                <div class="alert alert-danger text-center mb-4"><?php echo $signup_err; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4" novalidate>
                <!-- Role Selection -->
                <div class="form-group">
                    <label for="role">Account Type <span class="text-destructive">*</span></label>
                    <select name="role" id="role" class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>" required>
                        <option value="">Select Account Type</option>
                        <option value="student" <?php echo ($role == 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="librarian" <?php echo ($role == 'librarian') ? 'selected' : ''; ?>>Librarian (Admin)</option>
                    </select>
                    <span class="invalid-feedback"><?php echo $role_err; ?></span>
                    <small class="text-muted-foreground block mt-1">Librarian accounts have full administrative access</small>
                </div>

                <!-- Full Name -->
                <div class="form-group">
                    <label for="full_name">Full Name <span class="text-destructive">*</span></label>
                    <input type="text" name="full_name" id="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($full_name); ?>" placeholder="Enter your full name" required>
                    <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username <span class="text-destructive">*</span></label>
                    <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>" placeholder="Choose a username" required>
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address <span class="text-destructive">*</span></label>
                    <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>

                <!-- Student-Only Fields -->
                <div id="student-fields" style="display: block;">
                    <div class="form-group">
                        <label for="lrn">LRN (Learner Reference Number) <span class="text-destructive">*</span></label>
                        <input type="text" name="lrn" id="lrn" class="form-control <?php echo (!empty($lrn_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($lrn); ?>" placeholder="Enter 12-digit LRN" maxlength="12" pattern="\d{12}" title="LRN must be exactly 12 digits">
                        <span class="invalid-feedback"><?php echo $lrn_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label for="year_level">Year Level <span class="text-destructive">*</span></label>
                        <input type="text" name="year_level" id="year_level" class="form-control <?php echo (!empty($year_level_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($year_level); ?>" placeholder="e.g., Grade 12">
                        <span class="invalid-feedback"><?php echo $year_level_err; ?></span>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password <span class="text-destructive">*</span></label>
                    <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Create a password (min 8 characters)" minlength="8" required>
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="text-destructive">*</span></label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" placeholder="Confirm your password" required>
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary w-full py-4 rounded-xl shadow-lg">
                        Create Account
                    </button>
                </div>

                <!-- Login Link -->
                <p class="text-center text-sm text-muted-foreground">
                    Already have an account? 
                    <a href="login.php" class="text-primary hover:underline font-medium">Sign in here</a>
                </p>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
        
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const studentFields = document.getElementById('student-fields');
            const roleErrorSpan = document.querySelector('#role + .invalid-feedback');
            const submitBtn = document.querySelector('button[type="submit"]');

            function toggleStudentFields() {
                if (roleSelect.value === 'student') {
                    studentFields.style.display = 'block';
                    const lrnInput = document.querySelector('input[name="lrn"]');
                    const yearInput = document.querySelector('input[name="year_level"]');
                    if (lrnInput) lrnInput.required = true;
                    if (yearInput) yearInput.required = true;
                } else {
                    studentFields.style.display = 'none';
                    const lrnInput = document.querySelector('input[name="lrn"]');
                    const yearInput = document.querySelector('input[name="year_level"]');
                    if (lrnInput) {
                        lrnInput.value = '';
                        lrnInput.required = false;
                    }
                    if (yearInput) {
                        yearInput.value = '';
                        yearInput.required = false;
                    }
                }
            }

            function checkLibrarian() {
                if (roleSelect.value === 'librarian') {
                    fetch('signup.php?check_librarian=1')
                        .then(response => response.json())
                        .then(data => {
                            if (data.exists) {
                                roleErrorSpan.textContent = "A librarian account already exists. Only one librarian is allowed per system.";
                                roleSelect.classList.add('is-invalid');
                                submitBtn.disabled = true;
                            } else {
                                roleErrorSpan.textContent = "";
                                roleSelect.classList.remove('is-invalid');
                                submitBtn.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Error checking librarian:', error);
                            roleErrorSpan.textContent = "Error checking librarian status. Please try again.";
                        });
                } else {
                    roleErrorSpan.textContent = "";
                    roleSelect.classList.remove('is-invalid');
                    submitBtn.disabled = false;
                }
            }

            roleSelect.addEventListener('change', function() {
                toggleStudentFields();
                checkLibrarian();
            });

            toggleStudentFields();
        });
    </script>
</body>
</html>