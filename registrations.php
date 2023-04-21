<?php
session_start();

require_once('PDO.DB.class.php');

$db = new DB();

include 'Utils.php';

Utils::isUserLoggedIn();


$message = ""; //used for displaying error messages on form

// Check for Form Submission / Actions
// all values are pulled from the database and are not from user input on this page

// Deleting current user event (and any sessions it contains)
if (isset($_POST['deleteEventID']) && isset($_SESSION['userid'])) {
    
    if($_POST['deleteEventID'] != "") {
        $eventID = $_POST['deleteEventID'];
    } else {
        $message .= "Event ID error. \n";
    }

    if($_SESSION['userid'] != "") {
        $userID = $_SESSION['userid'];
    } else {
        $message .= "User ID error. \n";
    }

    // delete event for current user
    if(isset($eventID) && isset($userID)) {
        if($db->deleteEventAttendee($userID, $eventID) != -1) {
            $message .= "Unregistered for event successfully.\n";
        } else {
            $message .= "Unable to unregister from event.";
        }
    }

} // deleting event (and any sessions it contains)

// Deleting current user session
if (isset($_POST['deleteSessionID']) && isset($_SESSION['userid'])) {
    
    if($_POST['deleteSessionID'] != "") {
        $sessionID = $_POST['deleteSessionID'];
    } else {
        $message .= "Session ID error. \n";
    }

    if($_SESSION['userid'] != "") {
        $userID = $_SESSION['userid'];
    } else {
        $message .= "User ID error. \n";
    }

    // delete session for current user
    if(isset($sessionID) && isset($userID)) {
        if($db->deleteSessionAttendee($userID, $sessionID) != -1) {
            $message .= "Unregistered for session successfully.\n";
        } else {
            $message .= "Unable to unregister from session.";
        }
    }

} // deleting session for current user


// display any messages from form submissions / actions
if ($message != "") {
    echo "<div class='alert alert-info' role='alert'>$message</div>";
}

?>

<?php
	Utils::htmlHeader("Registration Information");
?>

<div class="mainContainer">

<?php

	echo "<h1>Registration Information</h1>";

	echo "<h2>Your Events</h2>";

	$data = $db->getAttendeeEventIds($_SESSION['userlogin']);

	foreach($data as $evt) {
		echo $db->getEventsAsTableDelete($evt['event']);
		echo "<br />";
	}

?>

<!-- button to redirect to event page to register for another event -->
<div><input class='btn btn-success btn-lg' type=button onClick="location.href='events.php'" value='Click Here To Register For An Event'></div>

<?php

	echo "<h2>Your Sessions</h2>";

	$data = $db->getAttendeeSessionIds($_SESSION['userlogin']);

	foreach($data as $sess) {
		echo $db->getSessionsAsTableDelete($sess['session']);
		echo "<br />";
	}

?>

<!-- button to redirect to event page to register for another session -->
<div><input class='btn btn-success btn-lg' type='button' onClick='location.href="events.php"' value='Click Here To Register For A Session'></div>

</div>

</body>
</html>