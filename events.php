<?php
session_start();

require_once('PDO.DB.class.php');
require_once('validations.php');

$db = new DB();

include 'Utils.php';

Utils::isUserLoggedIn();

$message = ""; //used for displaying error messages on form


// Check for Form Submission / Actions

// Registering logged in user for event
if (isset($_POST['registerEvent'])) {

    // fields: eventID, $_SESSION['userid']
    if (isset($_POST['eventID']) && isset($_SESSION['userid'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if($_POST['eventID'] != "") {
            $eventID = $_POST['eventID'];
        } else {
            $message .= "Event ID error. \n";
        }

        if($_SESSION['userid'] != "") {
            $userID = $_SESSION['userid'];
        } else {
            $message .= "User ID error. \n";
        }

        // register logged in user for event
        if(isset($eventID) && isset($userID)) {
            if($db->insertEventAttendee($eventID, $userID, 0) != -1) {
                $message .= "Registered successfully.";
            } else {
                $message .= "Unable to register.";
            }
        } else {
            $message .= "All fields must be populated.";
        }

    }

} // register for event


// Registering logged in user for session
if (isset($_POST['registerSession'])) {

    // fields: sessionID, $_SESSION['userid']
    if (isset($_POST['sessionID']) && isset($_SESSION['userid'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if($_POST['sessionID'] != "") {
            $sessionID = $_POST['sessionID'];
        } else {
            $message .= "Session ID error. \n";
        }

        if($_SESSION['userid'] != "") {
            $userID = $_SESSION['userid'];
        } else {
            $message .= "User ID error. \n";
        }

        // user must be registered for event to register for session
        // get event ID
        $data = $db->getSessionById($sessionID);
        foreach($data as $sess) {
            $eventID = $sess['event'];
        }
        
        // check if user is registerd for event
        if($db->isUserRegisteredEvent($eventID, $userID) != -1) {
            // register logged in user for session
            if(isset($sessionID) && isset($userID)) {
                if($db->insertSessionAttendee($sessionID, $userID) != -1) {
                    $message .= "Registered successfully.";
                } else {
                    $message .= "Unable to register.";
                }
            } else {
                $message .= "All fields must be populated.";
            }
        } else {
            $message .= "User must be registered for event containing this session first. \n";
        }
    }

} // register for session


// display any messages from form submissions / actions
if ($message != "") {
    echo "<div class='alert alert-info' role='alert'>$message</div>";
}

?>

<?php
	Utils::htmlHeader("Events");
?>

<div class="mainContainer">

<?php

echo "<h1>All Events</h1>";
echo $db->getAllEventsAsTableRegister();

echo "<h1>All Sessions</h1>";
echo $db->getAllSessionsAsTableRegister();

?>

</div>

</body>
</html>