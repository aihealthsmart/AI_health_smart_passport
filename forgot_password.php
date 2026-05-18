<?php
session_start();
include('db.php');

if(isset($_POST['reset_request'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Check both tables (users and patients) to find the account
    $check_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $check_patient = mysqli_query($conn, "SELECT * FROM patients WHERE national_id='$username'");

    if(mysqli_num_rows($check_user) > 0 || mysqli_num_rows($check_patient) > 0) {
        // In a real system, you would send an email here. 
        // For your project, we will provide a secure "reset" link for the demo.
        $success = "Account verified. Use the form below to set a new password.";
        $show_reset_form = true;
        $target_user = $username;
    } else {
        $error = "No account found with that ID or Username.";
    }
}

if(isset($_POST['update_password'])) {
    $new_pass = mysqli_real_escape_string($conn, $_POST['new_password']);
    $user_id = $_POST['user_id'];

    // Update the password in both possible tables
    mysqli_query($conn, "UPDATE users SET password='$new_pass' WHERE username='$user_id'");
    mysqli_query($conn, "UPDATE patients SET password='$new_pass' WHERE national_id='$user_id'");

    $_SESSION['msg'] = "Password Updated Successfully! Please Login.";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recover Password | Smart Health</title>
</head>
<body style="background: #f0f2f5; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0;">

    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 350px;">
        <h2 style="text-align: center; color: #333;">Password Recovery</h2>
        
        <?php if(isset($error)): ?>
            <p style="color:red; text-align:center; font-size:14px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if(!isset($show_reset_form)): ?>
            <form method="POST">
                <p style="font-size:14px; color:#666;">Enter your Username or National ID to recover your account.</p>
                <input type="text" name="username" placeholder="Username / ID Number" required 
                       style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">
                <button type="submit" name="reset_request" style="width:100%; padding:10px; background:#007bff; color:white; border:none; border-radius:4px; cursor:pointer;">Verify Account</button>
            </form>
        <?php else: ?>
            <form method="POST">
                <p style="color:green; font-size:14px;"><?php echo $success; ?></p>
                <input type="hidden" name="user_id" value="<?php echo $target_user; ?>">
                <input type="password" name="new_password" placeholder="New Password" required 
                       style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box;">
                <button type="submit" name="update_password" style="width:100%; padding:10px; background:#28a745; color:white; border:none; border-radius:4px; cursor:pointer;">Update Password</button>
            </form>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 15px;">
            <a href="login.php" style="font-size: 13px; color: #666; text-decoration: none;">&larr; Back to Login</a>
        </div>
    </div>
</body>
</html>