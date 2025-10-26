<?php
require_once 'header.php';

if (getUserRole() !== 'student') {
    header("Location: librarian_dashboard.php");
    exit();
}

$full_name = $_SESSION['full_name'] ?? '';
$year_level = "";
$name_change_err = $year_level_err = $success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["full_name"]))) {
        $name_change_err = "Please enter your new full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
    }

    if (empty(trim($_POST["year_level"]))) {
        $year_level_err = "Please enter your new year level.";
    } else {
        $year_level = trim($_POST["year_level"]);
    }

    if (empty($name_change_err) && empty($year_level_err)) {
        // Notify librarian about the change request using user_id
        $message = "Student {$full_name} has requested a name change to '{$full_name}' and year level change to '{$year_level}'.";
        notifyLibrarian($message); // Function to send notification

        $success_msg = "Your request has been submitted. The librarian will review it shortly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Change Request</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="profile-change-container">
        <h2>Request Profile Change</h2>
        <p>Submit your request for a name or year level change.</p>
        <?php if (!empty($name_change_err)): ?>
            <div class="alert alert-danger"><?php echo $name_change_err; ?></div>
        <?php endif; ?>
        <?php if (!empty($year_level_err)): ?>
            <div class="alert alert-danger"><?php echo $year_level_err; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="full_name" placeholder="Enter your new full name" value="<?php echo htmlspecialchars($full_name); ?>" required>
            <input type="text" name="year_level" placeholder="Enter your new year level" value="<?php echo htmlspecialchars($year_level); ?>" required>
            <button type="submit">Submit Request</button>
        </form>
    </div>
</body>
</html>