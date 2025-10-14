<?php
// Temporary debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'functions.php';

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
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const studentFields = document.getElementById('student-fields');

            function toggleStudentFields() {
                if (roleSelect.value === 'student') {
                    studentFields.style.display = 'block';
                    // Make student fields required
                    const lrnInput = document.querySelector('input[name="lrn"]');
                    const yearInput = document.querySelector('input[name="year_level"]');
                    if (lrnInput) lrnInput.required = true;
                    if (yearInput) yearInput.required = true;
                } else {
                    studentFields.style.display = 'none';
                    // Clear and remove required for librarians
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

            roleSelect.addEventListener('change', toggleStudentFields);
            toggleStudentFields(); // Initial state (default: student, show fields)
        });
    </script>
</head>
<body>
    <div class="min-h-screen bg-gradient-to-br from-[var(--background)] via-[var(--muted)] to-[var(--accent)]">
        <!-- Header -->
        <header class="header-primary backdrop-blur-sm border-b border-border px-6 py-4">
            <div class="flex items-center justify-between max-w-7xl mx-auto">
                <a href="index.php" class="flex items-center space-x-2 text-primary-foreground hover:bg-primary/20 btn">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back to Home</span>
                </a>
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-[var(--secondary)] rounded-xl flex items-center justify-center shadow-lg">
                        <i data-lucide="book-open" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-semibold text-primary-foreground">BookHive</span>
                </div>
            </div>
        </header>

        <div class="flex items-center justify-center px-4 py-20">
            <div class="auth-container card">
                <div class="space-y-1 text-center pb-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-[var(--primary)] to-[var(--secondary)] rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
                        <i data-lucide="user-plus" class="w-8 h-8 text-primary-foreground"></i>
                    </div>
                    <h2 class="text-3xl text-foreground">Sign Up for BookHive</h2>
                    <p class="text-muted-foreground">
                        Please fill this form to create an account.
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
                        <label for="role">Role <span class="text-destructive">*</span></label>
                        <select name="role" id="role" class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>" required>
                            <option value="">Select Role</option>
                            <option value="student" <?php echo ($role == 'student') ? 'selected' : ''; ?>>Student</option>
                            <option value="librarian" <?php echo ($role == 'librarian') ? 'selected' : ''; ?>>Librarian (Admin Access)</option>
                        </select>
                        <span class="invalid-feedback"><?php echo $role_err; ?></span>
                        <small class="text-muted-foreground block mt-1">Librarian accounts have admin privileges. Use responsibly.</small>
                    </div>

                    <!-- Full Name (Always Visible, Required for Both) -->
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="text-destructive">*</span></label>
                        <input type="text" name="full_name" id="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($full_name); ?>" placeholder="Enter your full name (e.g., John Doe)" required>
                        <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
                    </div>

                    <!-- Username -->
                    <div class="form-group">
                        <label for="username">Username <span class="text-destructive">*</span></label>
                        <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter a unique username" required>
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email <span class="text-destructive">*</span></label>
                        <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email address" required>
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>

                    <!-- Student-Only Fields (Toggled) -->
                    <div id="student-fields" style="display: block;">
                        <div class="form-group">
                            <label for="lrn">LRN (Learner Reference Number) <span class="text-destructive">*</span></label>
                            <input type="text" name="lrn" id="lrn" class="form-control <?php echo (!empty($lrn_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($lrn); ?>" placeholder="Enter 12-digit LRN" maxlength="12" pattern="\d{12}" title="LRN must be exactly 12 digits">
                            <span class="invalid-feedback"><?php echo $lrn_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label for="year_level">Year Level <span class="text-destructive">*</span></label>
                            <input type="text" name="year_level" id="year_level" class="form-control <?php echo (!empty($year_level_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($year_level); ?>" placeholder="e.g., Grade 12 or Year 4">
                            <span class="invalid-feedback"><?php echo $year_level_err; ?></span>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Password <span class="text-destructive">*</span></label>
                        <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Enter a password (min 8 characters)" minlength="8" required>
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="text-destructive">*</span></label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" placeholder="Confirm your password" required>
                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-primary w-full py-6 rounded-xl shadow-lg" value="Sign Up">
                    </div>

                    <p class="text-center text-sm text-muted-foreground">Already have an account? <a href="login.php" class="text-primary hover:underline">Login here</a>.</p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>