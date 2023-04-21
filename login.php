<?php
    session_start();

    require_once('PDO.DB.class.php');
    require_once('validations.php');

    $db = new DB();

    include 'Utils.php';

    // print message if form is submitted
	$message = ""; //used for displaying error messages on form

    // if user logged in before (if session variable exists)
    if (isset($_SESSION['userlogin'])) {
        // re-direct user to events.php
        header("Location: events.php");
        exit;
    }

    if (isset($_POST['username']) && isset($_POST['password'])) {
        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }
        
        // attendee name and password values cannot exceed 100
        if((strlen($_POST['username']) <= 100) && (strlen($_POST['password']) <= 100)) {
            // Getting name and password
            $username = $_POST['username'];
            // pw stored is hashed
            $password = hash("sha256", $_POST['password']);

            if($db->login($username, $password)) {
                // login successful, set session variables, direct user to event page
                $_SESSION['userlogin'] = $username;
                $_SESSION['role'] = $db->getRole($username, $password);
                $_SESSION['userid'] = $db->getUserId($username, $password);
                header("Location: events.php");
                exit;
            } else {
                $message .= "Invalid name and/or password";
            }
        } else {
            $message .= "Invalid name and/or password";  // length exceeded
        }
    }
?>

<?php
	Utils::htmlHeader("Registration Information");
?>

<body>

<?php
    // alert user of any errors
	if ($message != "") {
        echo "<div class='alert alert-danger' role='alert'>$message</div>";
    }
?>

<div class="mainContainer">

<h1>Login</h1>
<form id="loginForm" method="post">
    <div class="form-group">
        <label for="username">Name</label>
        <input class="form-control" type="text" id="username" name="username" placeholder="First and Last Name" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password" placeholder="Password" required>
    </div>
    <button id="loginbtn" class="btn btn-primary" type="submit" name="login">Sign In</button>
</form>

</div>

</body>
</html>