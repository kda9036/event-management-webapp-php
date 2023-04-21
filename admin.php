<?php
session_start();

require_once('PDO.DB.class.php');
require_once('validations.php');

$db = new DB();

include 'Utils.php';

Utils::isUserLoggedIn();


$message = ""; //used for displaying error messages on form


// Check for Form Submission / Actions

// Adding new user
if (isset($_POST['addUser'])) {

    // fields: newUserName, newUserPassword, newUserRole
    if (isset($_POST['newUserName']) && isset($_POST['newUserPassword']) && isset($_POST['newUserRole'])) {
        
        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if(($_POST['newUserName'] != "") && (strlen($_POST['newUserName']) <= 100)) {
            $username = $_POST['newUserName'];
        } else {
            $message .= "User first and last name must be entered. Name cannot exceed 100 characters. \n";
        }

        if(($_POST['newUserPassword'] != "") && (strlen($_POST['newUserPassword']) <= 100)) {
            // store hashed pw
            $password = hash("sha256", $_POST['newUserPassword']);
        } else {
            $message .= "User password must be entered and cannot exceed 100 characters. \n";
        }

        if(($_POST['newUserRole'] != "") && (filter_var($_POST['newUserRole'], FILTER_VALIDATE_INT))) {
            $role = $_POST['newUserRole'];
        } else {
            $message .= "User role must be selected. \n";
        }

        // insert new user
        if(isset($username) && isset($password) && isset($role)) {
            if($db->insertNewUser($username, $password, $role) != -1) {
                $message .= "User added successfully.";
            } else {
                $message .= "Unable to add user.";
            }
        } else {
            $message .= "All fields must be populated and valid.";
        }

    }

} // adding new user

// Adding new venue
if (isset($_POST['addVenue'])) {

    // fields: newVenueName, newVenueCapacity
    if (isset($_POST['newVenueName']) && isset($_POST['newVenueCapacity'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if(($_POST['newVenueName'] != "") && (strlen($_POST['newVenueName']) <= 50)) {
            $venuename = $_POST['newVenueName'];
        } else {
            $message .= "Venue name must be entered and cannot exceed 50 characters. \n";
        }

        if(($_POST['newVenueCapacity'] != "") && (filter_var($_POST['newVenueCapacity'], FILTER_VALIDATE_INT))) {
            $capacity = $_POST['newVenueCapacity'];
        } else {
            $message .= "Venue capacity must be entered. \n";
        }

        // insert new venue
        if(isset($venuename) && isset($capacity)) {
            if($db->insertNewVenue($venuename, $capacity) != -1) {
                $message .= "Venue added successfully.";
            } else {
                $message .= "Unable to add venue.";
            }
        } else {
            $message .= "All fields must be populated and valid.";
        }

    }

} // adding new venue

// Adding new event
if (isset($_POST['addEvent'])) {

    // fields: newEventName, newDateStart, newTimeStart, newDateEnd, newTimeEnd, newNumberAllowed, newVenueID
    if (isset($_POST['newEventName']) && isset($_POST['newDateStart']) && isset($_POST['newTimeStart']) && isset($_POST['newDateEnd']) && isset($_POST['newDateEnd']) && isset($_POST['newNumberAllowed']) && isset($_POST['newVenueID'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if(($_POST['newEventName'] != "") && (strlen($_POST['newEventName']) <= 50)) {
            $eventName = $_POST['newEventName'];
        } else {
            $message .= "Event name must be entered and cannot exceed 50 characters. \n";
        }

        if($_POST['newDateStart'] != "") {
            $eventDateStart = $_POST['newDateStart'];
        } else {
            $message .= "Event must have a start date. \n";
        }

        if($_POST['newTimeStart'] != "") {
            $eventTimeStart = $_POST['newTimeStart'];
        } else {
            $message .= "Event must have a start time. \n";
        }

        if($_POST['newDateEnd'] != "") {
            $eventDateEnd = $_POST['newDateEnd'];
        } else {
            $message .= "Event must have an end date. \n";
        }

        if($_POST['newTimeEnd'] != "") {
            $eventTimeEnd = $_POST['newTimeEnd'];
        } else {
            $message .= "Event must have an end time. \n";
        }

        if(($_POST['newVenueID'] != "")  && (filter_var($_POST['newVenueID'], FILTER_VALIDATE_INT))) {
            $venueID = $_POST['newVenueID'];
        } else {
            $message .= "Venue ID Error. \n";
        }

        if(($_POST['newNumberAllowed'] != "") && (filter_var($_POST['newNumberAllowed'], FILTER_VALIDATE_INT))) {
            // use venue ID to get venue capacity
            // make sure event numberallowed is <= venue capacity
            if(isset($venueID)) {
                $venue = $db->getVenueById($venueID);
                if($venue['capacity'] >= $_POST['newNumberAllowed']) {
                    $eventNumberAllowed = $_POST['newNumberAllowed'];
                } else {
                    $message .= "Number allowed cannot exceed the capacity of the venue. \n";
                }
            }
        } else {
            $message .= "Number allowed must be entered. \n";
        }

        // insert new event
        if(isset($eventName) && isset($eventDateStart) && isset($eventTimeStart) && isset($eventDateEnd) && isset($eventTimeEnd) && isset($eventNumberAllowed) && isset($venueID)) {
            
            // combine date and time to get correctly formatted datetime for database
            $input = "$eventDateStart $eventTimeStart";
            $date = strtotime($input);
            $datestart = date('Y-m-d H:i:s', $date);

            $input = "$eventDateEnd $eventTimeEnd";
            $date = strtotime($input);
            $dateend = date('Y-m-d H:i:s', $date);

            if($db->insertNewEvent($eventName, $datestart, $dateend, $eventNumberAllowed, $venueID) != -1) {
                $message .= "Event added successfully.";

                // if event added by event manager, add event and manager to manager_event table
                if($_SESSION['role'] == 2) {
                    // get ID of event added
                    $event = $db->getEventByName($eventName);
                    foreach($event as $evt) {
                        $eventID = $evt['idevent'];
                    }
                    // insert into manager_event
                    if($db->insertEventManager($eventID, $_SESSION['userid']) != -1) {
                        // Do nothing
                    } else {
                        $message .= "Error adding manager to event. \n";
                    }
                }

            } else {
                $message .= "Unable to add event.";
            }
        } else {
            $message .= "All fields must be populated and valid.";
        }

    }

} // adding new event

// Adding new session
if (isset($_POST['addSession'])) {

    // fields: newSessionName, newSessionNumberAllowed, newEventID, newSessionDateStart, newSessionTimeStart, newSessionDateEnd, newSessionTimeEnd 
    if (isset($_POST['newSessionName']) && isset($_POST['newSessionNumberAllowed']) && isset($_POST['newEventID']) && isset($_POST['newSessionDateStart']) && isset($_POST['newSessionTimeStart']) && isset($_POST['newSessionDateEnd']) && isset($_POST['newSessionTimeEnd'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if(($_POST['newSessionName'] != "") && (strlen($_POST['newSessionName']) <= 50)) {
            $sessionName = $_POST['newSessionName'];
        } else {
            $message .= "Session name must be entered and cannot exceed 50 characters. \n";
        }

        if(($_POST['newEventID'] != "") && (filter_var($_POST['newEventID'], FILTER_VALIDATE_INT))) {
            $eventID = $_POST['newEventID'];
        } else {
            $message .= "Event ID Error. \n";
        }

        if(($_POST['newSessionNumberAllowed'] != "") && (filter_var($_POST['newSessionNumberAllowed'], FILTER_VALIDATE_INT))) {
            // use event ID to get event number allowed
            // make sure session numberallowed is <= event number allowed
            if(isset($eventID)) {
                $data = $db->getEventById($eventID);
                foreach($data as $evt) {
                    if($evt['numberallowed'] >= $_POST['newSessionNumberAllowed']) {
                        $sessionNumAllowed = $_POST['newSessionNumberAllowed'];
                    } else {
                        $message .= "Number allowed cannot exceed the number allowed of the event. \n";
                    }
                }
            }
        } else {
            $message .= "Number allowed must be entered. \n";
        }

        if($_POST['newSessionDateStart'] != "") {
            $sessionDateStart = $_POST['newSessionDateStart'];
        } else {
            $message .= "Session must have a start date. \n";
        }

        if($_POST['newSessionTimeStart'] != "") {
            $sessionTimeStart = $_POST['newSessionTimeStart'];
        } else {
            $message .= "Session must have a start time. \n";
        }

        if($_POST['newSessionDateEnd'] != "") {
            $sessionDateEnd = $_POST['newSessionDateEnd'];
        } else {
            $message .= "Session must have an end date. \n";
        }

        if($_POST['newSessionTimeEnd'] != "") {
            $sessionTimeEnd = $_POST['newSessionTimeEnd'];
        } else {
            $message .= "Session have an end time. \n";
        }

        // insert new session
        if(isset($sessionName) && isset($sessionNumAllowed) && isset($eventID) && isset($sessionDateStart) && isset($sessionTimeStart) && isset($sessionDateEnd) && isset($sessionTimeEnd)) {
            
            // combine date and time to get correctly formatted datetime for database
            $input = "$sessionDateStart $sessionTimeStart";
            $date = strtotime($input);
            $startdate = date('Y-m-d H:i:s', $date);

            $input = "$sessionDateEnd $sessionTimeEnd";
            $date = strtotime($input);
            $enddate = date('Y-m-d H:i:s', $date);

            if($db->insertNewSession($sessionName, $sessionNumAllowed, $eventID, $startdate, $enddate) != -1) {
                $message .= "Session added successfully.";
            } else {
                $message .= "Unable to add session.";
            }
        } else {
            $message .= "All fields must be populated and valid.";
        }

    }

} // adding new session


// Editing/Updating user
if (isset($_POST['updateUser'])) {

    // fields: updatedUserID, updatedUserName, updatedUserPassword, updatedUserRole
    if (isset($_POST['updatedUserID']) && isset($_POST['updatedUserName']) && isset($_POST['updatedUserPassword']) && isset($_POST['updatedUserRole'])) {
        
        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if($_POST['updatedUserID'] != "") {
            $userID = $_POST['updatedUserID'];
        } else {
            $message .= "User ID error. \n";
        }

        if(($_POST['updatedUserName'] != "") && (strlen($_POST['updatedUserName']) <= 100)) {
            $username = $_POST['updatedUserName'];
        } else {
            $message .= "User first and last name must be entered and cannot exceed 100 characters. \n";
        }

        if(($_POST['updatedUserPassword'] != "") && (strlen($_POST['updatedUserPassword']) <= 100)) {
            $password = hash("sha256", $_POST['updatedUserPassword']);
        } else {
            $message .= "User password must be entered and cannot exceed 100 characters. \n";
        }

        if(($_POST['updatedUserRole'] != "") && (filter_var($_POST['updatedUserRole'], FILTER_VALIDATE_INT))) {
            $role = $_POST['updatedUserRole'];
        } else {
            $message .= "User role must be selected. \n";
        }

        // update user
        if(isset($userID) && isset($username) && isset($password) && isset($role)) {
            if($db->updateUser($userID, $username, $password, $role) != -1) {
                $message .= "User updated successfully.";
            } else {
                $message .= "Unable to update user.";
            }
        } else {
            $message .= "All fields must be populated and valid.";
        }

    }

} // editing user


// Editing venue
if (isset($_POST['editVenue'])) {

    // fields: updatedVenueID, updatedVenueName, updatedVenueCapacity
    if (isset($_POST['updatedVenueID']) && isset($_POST['updatedVenueName']) && isset($_POST['updatedVenueCapacity'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if($_POST['updatedVenueID'] != "") {
            $venueID = $_POST['updatedVenueID'];
        } else {
            $message .= "Venue ID error. \n";
        }

        if(($_POST['updatedVenueName'] != "") && (strlen($_POST['updatedVenueName']) <= 50)) {
            $venuename = $_POST['updatedVenueName'];
        } else {
            $message .= "Venue name must be entered and cannot exceed 50 characters. \n";
        }

        if(($_POST['updatedVenueCapacity'] != "") && (filter_var($_POST['updatedVenueCapacity'], FILTER_VALIDATE_INT))) {
            $capacity = $_POST['updatedVenueCapacity'];
        } else {
            $message .= "Venue capacity must be entered. \n";
        }

        // update venue
        if(isset($venueID) && isset($venuename) && isset($capacity)) {
            if($db->updateVenue($venueID, $venuename, $capacity) != -1) {
                $message .= "Venue updated successfully.";
            } else {
                $message .= "Unable to update venue.";
            }
        } else {
            $message .= "All fields must be populated and valid.";
        }

    }

} // editing venue


// Editing event
if (isset($_POST['editEvent'])) {

    // fields: updatedEventID, updatedEventName, updatedDateStart, updatedTimeStart, updatedDateEnd, updatedTimeEnd, updatedNumberAllowed, updatedVenueID
    if (isset($_POST['updatedEventID']) && isset($_POST['updatedEventName']) && isset($_POST['updatedDateStart']) && isset($_POST['updatedTimeStart']) && isset($_POST['updatedDateEnd']) && isset($_POST['updatedTimeEnd']) && isset($_POST['updatedNumberAllowed']) && isset($_POST['updatedVenueID'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if($_POST['updatedEventID'] != "") {
            $eventID = $_POST['updatedEventID'];
        } else {
            $message .= "Event ID error. \n";
        }

        if(($_POST['updatedEventName'] != "") && (strlen($_POST['updatedEventName']) <= 50)) {
            $eventName = $_POST['updatedEventName'];
        } else {
            $message .= "Event name must be entered and cannot exceed 50 characters. \n";
        }

        if($_POST['updatedDateStart'] != "") {
            $eventDateStart = $_POST['updatedDateStart'];
        } else {
            $message .= "Event must have a start date. \n";
        }

        if($_POST['updatedTimeStart'] != "") {
            $eventTimeStart = $_POST['updatedTimeStart'];
        } else {
            $message .= "Event must have a start time. \n";
        }

        if($_POST['updatedDateEnd'] != "") {
            $eventDateEnd = $_POST['updatedDateEnd'];
        } else {
            $message .= "Event must have an end date. \n";
        }

        if($_POST['updatedTimeEnd'] != "") {
            $eventTimeEnd = $_POST['updatedTimeEnd'];
        } else {
            $message .= "Event must have an end time. \n";
        }

        if(($_POST['updatedNumberAllowed'] != "") && (filter_var($_POST['updatedNumberAllowed'], FILTER_VALIDATE_INT))) {
            $eventNumberAllowed = $_POST['updatedNumberAllowed'];
        } else {
            $message .= "Number allowed must be entered. \n";
        }

        if(($_POST['updatedVenueID'] != "") && (filter_var($_POST['updatedVenueID'], FILTER_VALIDATE_INT))) {
            $venueID = $_POST['updatedVenueID'];
        } else {
            $message .= "Venue ID Error. \n";
        }

        // update event
        if(isset($eventID) && isset($eventName) && isset($eventDateStart) && isset($eventTimeStart) && isset($eventDateEnd) && isset($eventTimeEnd) && isset($eventNumberAllowed) && isset($venueID)) {
            
            // combine date and time to get correctly formatted datetime for database
            $input = "$eventDateStart $eventTimeStart";
            $date = strtotime($input);
            $datestart = date('Y-m-d H:i:s', $date);

            $input = "$eventDateEnd $eventTimeEnd";
            $date = strtotime($input);
            $dateend = date('Y-m-d H:i:s', $date);

            if($db->updateEvent($eventID, $eventName, $datestart, $dateend, $eventNumberAllowed, $venueID) != -1) {
                $message .= "Event updated successfully.";
            } else {
                $message .= "Unable to update event.";
            }
        } else {
            $message .= "All fields must be populated and valid.";
        }

    }

} // Editing event


// Editing session
if (isset($_POST['editSession'])) {

    // fields: updatedSessionID, updatedSessionName, updatedSessionNumberAllowed, updatedEventID, updatedSessionDateStart, updatedSessionTimeStart, updatedSessionDateEnd, updatedSessionTimeEnd 
    if (isset($_POST['updatedSessionID']) && isset($_POST['updatedSessionName']) && isset($_POST['updatedSessionNumberAllowed']) && isset($_POST['updatedEventID']) && isset($_POST['updatedSessionDateStart']) && isset($_POST['updatedSessionTimeStart']) && isset($_POST['updatedSessionDateEnd']) && isset($_POST['updatedSessionTimeEnd'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if($_POST['updatedSessionID'] != "") {
            $sessionID = $_POST['updatedSessionID'];
        } else {
            $message .= "Session ID error. \n";
        }

        if(($_POST['updatedSessionName'] != "") && (strlen($_POST['updatedSessionName']) <= 50)) {
            $sessionName = $_POST['updatedSessionName'];
        } else {
            $message .= "Session name must be entered and cannot exceed 50 characters. \n";
        }

        if(($_POST['updatedEventID'] != "") && (filter_var($_POST['updatedEventID'], FILTER_VALIDATE_INT))) {
            $eventID = $_POST['updatedEventID'];
        } else {
            $message .= "Event ID Error. \n";
        }

        if(($_POST['updatedSessionNumberAllowed'] != "") && (filter_var($_POST['updatedSessionNumberAllowed'], FILTER_VALIDATE_INT))) {
            // use event ID to get event number allowed
            // make sure session numberallowed is <= event number allowed
            if(isset($eventID)) {
                $data = $db->getEventById($eventID);
                foreach($data as $evt) {
                    if($evt['numberallowed'] >= $_POST['updatedSessionNumberAllowed']) {
                        $sessionNumAllowed = $_POST['updatedSessionNumberAllowed'];
                    } else {
                        $message .= "Number allowed cannot exceed the number allowed of the event. \n";
                    }
                }
            }
        } else {
            $message .= "Number allowed must be entered. \n";
        }

        if($_POST['updatedSessionDateStart'] != "") {
            $sessionDateStart = $_POST['updatedSessionDateStart'];
        } else {
            $message .= "Session must have a start date. \n";
        }

        if($_POST['updatedSessionTimeStart'] != "") {
            $sessionTimeStart = $_POST['updatedSessionTimeStart'];
        } else {
            $message .= "Session must have a start time. \n";
        }

        if($_POST['updatedSessionDateEnd'] != "") {
            $sessionDateEnd = $_POST['updatedSessionDateEnd'];
        } else {
            $message .= "Session must have an end date. \n";
        }

        if($_POST['updatedSessionTimeEnd'] != "") {
            $sessionTimeEnd = $_POST['updatedSessionTimeEnd'];
        } else {
            $message .= "Session have an end time. \n";
        }

        // update session
        if(isset($sessionID) && isset($sessionName) && isset($sessionNumAllowed) && isset($eventID) && isset($sessionDateStart) && isset($sessionTimeStart) && isset($sessionDateEnd) && isset($sessionTimeEnd)) {
            
            // combine date and time to get correctly formatted datetime for database
            $input = "$sessionDateStart $sessionTimeStart";
            $date = strtotime($input);
            $startdate = date('Y-m-d H:i:s', $date);

            $input = "$sessionDateEnd $sessionTimeEnd";
            $date = strtotime($input);
            $enddate = date('Y-m-d H:i:s', $date);

            if($db->updateSession($sessionID, $sessionName, $sessionNumAllowed, $eventID, $startdate, $enddate) != -1) {
                $message .= "Session updated successfully.";
            } else {
                $message .= "Unable to update session.";
            }
        } else {
            $message .= "All fields must be populated and valid.";
        }

    }

} // editing session


// Deleting user
if (isset($_POST['userID'])) {

    // Sanitize & Validate
    foreach ($_POST as $key=>$value) {
        $_POST[$key] = sanitizeString($value);
    }
    
    if($_POST['userID'] != "") {
        $userID = $_POST['userID'];
    } else {
        $message .= "User ID error. \n";
    }

    // delete user
    if(isset($userID)) {
        if($db->deleteUser($userID) != -1) {

            // delete from manager_event if user was a manager
            $db->deleteEventManagerByUser($userID);
            
            $message .= "User deleted successfully.";
        } else {
            $message .= "Unable to delete user.";
        }
    }

} // deleting user


// Deleting venue
if (isset($_POST['venueID'])) {

    // Sanitize & Validate
    foreach ($_POST as $key=>$value) {
        $_POST[$key] = sanitizeString($value);
    }
    
    if($_POST['venueID'] != "") {
        $venueID = $_POST['venueID'];
    } else {
        $message .= "Venue ID error. \n";
    }

    // delete venue
    if(isset($venueID)) {
        if($db->deleteVenue($venueID) != -1) {
            $message .= "Venue deleted successfully.";

            // check if venue deleted had events to delete
            $data = $db->getEventIdByVenueId($venueID);
            foreach($data as $evt) {
                // delete related event(s)
                if($db->deleteEvent($evt['idevent'])) {
                    // delete from attendee_event
                    $db->deleteEventAttendeeByEvent($evt['idevent']);

                    // delete from manager_event
                    $db->deleteEventManager($evt['idevent']);

                    // check if event deleted had sessions to delete
                    $data = $db->getAllSessionsByEvent($evt['idevent']);
                    foreach($data as $sess) {
                        if($db->deleteSession($sess['idsession'])) {
                            // delete from attendee_session
                            $db->deleteSessionAttendeeBySession($sess['idsession']);
                        }
                    }
                }
            }

        } else {
            $message .= "Unable to delete venue.";
        }
    }

} // deleting venue

// Deleting session
if (isset($_POST['sessionID'])) {

    // Sanitize & Validate
    foreach ($_POST as $key=>$value) {
        $_POST[$key] = sanitizeString($value);
    }
    
    if($_POST['sessionID'] != "") {
        $sessionID = $_POST['sessionID'];
    } else {
        $message .= "Session ID error. \n";
    }

    // delete session
    if(isset($sessionID)) {
        if($db->deleteSession($sessionID) != -1) {
            $message .= "Session deleted successfully.";

            // delete from attendee_session
            $db->deleteSessionAttendeeBySession($sessionID);

        } else {
            $message .= "Unable to delete session.";
        }
    }

} // deleting session

// Deleting event (and any sessions it contains, and event manager)
if (isset($_POST['eventID'])) {

    // Sanitize & Validate
    foreach ($_POST as $key=>$value) {
        $_POST[$key] = sanitizeString($value);
    }
    
    if($_POST['eventID'] != "") {
        $eventID = $_POST['eventID'];
    } else {
        $message .= "Event ID error. \n";
    }

    // delete event
    if(isset($eventID)) {
        if($db->deleteEvent($eventID) != -1) {
            $message .= "Event deleted successfully.\n";

            // delete from attendee_event
            $db->deleteEventAttendeeByEvent($eventID);

            // delete from manager_event
            $db->deleteEventManager($eventID);

            // check if event deleted had sessions to delete
            $data = $db->getAllSessionsByEvent($eventID);
            foreach($data as $sess) {
                if($db->deleteSession($sess['idsession'])) {
                    // delete from attendee_session
                    $db->deleteSessionAttendeeBySession($sess['idsession']);
                }
            }

        } else {
            $message .= "Unable to delete event.";
        }
    }

} // deleting event (and any sessions it contains)


// Adding event manager
if (isset($_POST['addEventManager'])) {

    // fields: eventManagerId, eventId
    if (isset($_POST['eventManagerId']) && isset($_POST['eventId'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if(($_POST['eventManagerId'] != "") && (filter_var($_POST['eventManagerId'], FILTER_VALIDATE_INT))) {
            $managerID = $_POST['eventManagerId'];
        } else {
            $message .= "Event Manager ID error. \n";
        }

        if(($_POST['eventId'] != "") && (filter_var($_POST['eventId'], FILTER_VALIDATE_INT))) {
            $eventID = $_POST['eventId'];
        } else {
            $message .= "Event ID error. \n";
        }

        // add user as manager of event
        if(isset($managerID) && isset($eventID)) {
            if($db->insertEventManager($eventID, $managerID) != -1) {
                $message .= "Event Manager added successfully.";
            } else {
                $message .= "Unable to add manager to event.";
            }
        } else {
            $message .= "All fields must be populated and valid.";
        }

    }

} // adding event manager


// display any messages from form submissions / actions
if ($message != "") {
    echo "<div class='alert alert-info' role='alert'>$message</div>";
}

?>

<?php
	Utils::htmlHeader("Admin Page");
?>

<div class="mainContainer">

<?php

// user must have admin (1) role
if($_SESSION['role'] == '1') {

    echo "<h1>Welcome, Admin</h1>";

    echo "<h2>Users</h2>";

    echo $db->getAllUsersAsTable();

    if($_SESSION['role'] == 1) {
        // button to trigger addUserModal
        echo "<div><input class='btn btn-primary btn-lg' type='button' value=':: Add User ::' data-bs-toggle='modal' data-bs-target='#addUserModal'></div>";
    }

    echo "<h2>Venues</h2>";

    echo $db->getAllVenuesAsTable();

    // button to add a venue
    echo "<div><input class='btn btn-primary btn-lg' type='button' value=':: Add Venue ::' data-bs-toggle='modal' data-bs-target='#addVenueModal'></div>";

    echo "<h2>Events</h2>";
    echo "<h4>(Click Event Name to View/Edit Attendees)</h4>";

    echo $db->getAllEventsAsTable();

    echo "<div><input class='btn btn-primary btn-lg' type='button' value=':: Add Event Manager ::' data-bs-toggle='modal' data-bs-target='#addEventManagerModal'></div>";

    echo "<h2>Sessions</h2>";
    echo "<h4>(Click Session Name to View/Edit Attendees)</h4>";

    echo $db->getAllSessionsAsTable();

}//admin role = 1

// user must event manager (2) role
if($_SESSION['role'] == 2) {

    echo "<h1>Welcome, Event Manager</h1>";

    echo "<h2>Venues</h2>";

    echo $db->getAllVenuesAsTableForManager();

    echo "<h2>Events Managed</h2>";
    echo "<h4>(Click Event Name to View/Edit Attendees)</h4>";

    echo $db->getManagerEventsAsTable($_SESSION['userid']);

    // echo $db->getAllEventsAsTable();

    echo "<h2>Sessions Managed</h2>";
    echo "<h4>(Click Session Name to View/Edit Attendees)</h4>";

    echo $db->getManagerSessionsAsTable($_SESSION['userid']);

}//event manager role = 2

?>

</div>

<!-- MODALS -->

<!-- addUserModal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelAddUser" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelAddUser">
                    Add User
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                  <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="inputName">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" 
                        id="inputName" placeholder="First and Last Name" name="newUserName" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputPassword">Password</label>
                    <div class="col-sm-10">
                        <input type="password" class="form-control"
                            id="inputPassword" placeholder="Password" name="newUserPassword" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="roles">Role</label>
                    <div class="col-sm-10">
                    <select id="roles" name="newUserRole" required>
                        <option value="" selected>Select a role:</option>
                        <option value="1">1 - admin</option>
                        <option value="2">2 - event manager</option>
                        <option value="3">3 - attendee</option>
                        </select>
                    </div>
                  </div>


            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Add User" name="addUser" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div> 
<!-- end addUserModal -->

<!-- addVenueModal -->
<div class="modal fade" id="addVenueModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelAddVenue" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelAddVenue">
                    Add Venue
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                  <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="inputVenue">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" 
                        id="inputVenue" placeholder="Venue Name" name="newVenueName" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputCapacity">Capacity</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputCapacity" name="newVenueCapacity" min="1" required />
                    </div>
                  </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Add Venue" name="addVenue" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end addVenueModal -->


<!-- addEventModal -->
<div class="modal fade" id="addEventModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelAddEvent" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelAddEvent">
                    Add Event
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                  <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="inputEvent">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" 
                        id="inputEvent" placeholder="Event Name" name="newEventName" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputDateStart">Date Start</label>
                    <div class="col-sm-10">
                        <input type="date" class="form-control"
                            id="inputDateStart" name="newDateStart" min="<?= date('Y-m-d'); ?>" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputTimeStart">Time Start</label>
                    <div class="col-sm-10">
                        <input type="time" class="form-control"
                            id="inputTimeStart" name="newTimeStart" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputDateEnd">Date End</label>
                    <div class="col-sm-10">
                        <input type="date" class="form-control"
                            id="inputDateEnd" name="newDateEnd" min="<?= date('Y-m-d'); ?>" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputTimeEnd">Time End</label>
                    <div class="col-sm-10">
                        <input type="time" class="form-control"
                            id="inputTimeEnd" name="newTimeEnd" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputNumberAllowed">Number Allowed</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputNumberAllowed" name="newNumberAllowed" min="1" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputVenueID">Venue ID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputVenueID" name="newVenueID" readonly required/>
                    </div>
                  </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Add Event" name="addEvent" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end addEventModal -->

<!-- addSessionModal -->
<div class="modal fade" id="addSessionModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelAddSession" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelAddSession">
                    Add Session
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                  <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="inputEvent">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" 
                        id="inputSession" placeholder="Session Name" name="newSessionName" required/>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputSessionNumberAllowed">Number Allowed</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputSessionNumberAllowed" name="newSessionNumberAllowed" min="1" required/>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputEventID">Event ID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputEventID" name="newEventID" readonly required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputSessionDateStart">Date Start</label>
                    <div class="col-sm-10">
                        <input type="date" class="form-control"
                            id="inputSessionDateStart" name="newSessionDateStart" min="<?= date('Y-m-d'); ?>" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputSessionTimeStart">Time Start</label>
                    <div class="col-sm-10">
                        <input type="time" class="form-control"
                            id="inputSessionTimeStart" name="newSessionTimeStart" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputSessionDateEnd">Date End</label>
                    <div class="col-sm-10">
                        <input type="date" class="form-control"
                            id="inputSessionDateEnd" name="newSessionDateEnd" min="<?= date('Y-m-d'); ?>" required/>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputSessionTimeEnd">Time End</label>
                    <div class="col-sm-10">
                        <input type="time" class="form-control"
                            id="inputSessionTimeEnd" name="newSessionTimeEnd" required />
                    </div>
                  </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Add Session" name="addSession" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end addSessionModal -->


<!-- editUserModal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelEditUser" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelEditUser">
                    Edit User
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="form-group">
                    <label class="col-sm-2 control-label"
                              for="updatedUserID">ID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" 
                        id="updatedUserID" name="updatedUserID" readonly required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                              for="updatedUserName">User Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" 
                        id="updatedUserName" placeholder="First and Last Name" name="updatedUserName" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="updatedUserPassword">Password</label>
                    <div class="col-sm-10">
                        <input type="password" class="form-control"
                            id="updatedUserPassword" name="updatedUserPassword" required />
                    </div>
                  </div>
                  <div class="form-group">
                  <label class="col-sm-2 control-label"
                          for="updatedUserRole">Role</label>
                    <div class="col-sm-10">
                    <select id="updatedUserRole" name="updatedUserRole" required>
                        <option value="" selected>Select a role:</option>
                        <option value="1">1 - admin</option>
                        <option value="2">2 - event manager</option>
                        <option value="3">3 - attendee</option>
                        </select>
                    </div>
                  </div>
            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Update User" name="updateUser" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end editUserModal -->


<!-- editVenueModal -->
<div class="modal fade" id="editVenueModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelEditVenue" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelEditVenue">
                    Edit Venue
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="inputUpdatedVenueID">Venue ID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" 
                        id="inputUpdatedVenueID" name="updatedVenueID" readonly required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="inputVenue">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" 
                        id="inputUpdatedVenue" name="updatedVenueName" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedCapacity">Capacity</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputUpdatedCapacity" name="updatedVenueCapacity" min="1" required />
                    </div>
                  </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Update Venue" name="editVenue" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end editVenueModal -->


<!-- editEventModal -->
<div class="modal fade" id="editEventModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelEditEvent" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelEditEvent">
                    Edit Event
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="inputUpdatedEventID">Event ID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" 
                        id="inputUpdatedEventID" name="updatedEventID" readonly required />
                    </div>
                  </div>
                <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="inputUpdatedEvent">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" 
                        id="inputUpdatedEvent" name="updatedEventName" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedDateStart">Date Start</label>
                    <div class="col-sm-10">
                        <input type="date" class="form-control"
                            id="inputUpdatedDateStart" name="updatedDateStart" min="<?= date('Y-m-d'); ?>" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedTimeStart">Time Start</label>
                    <div class="col-sm-10">
                        <input type="time" class="form-control"
                            id="inputUpdatedTimeStart" name="updatedTimeStart" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedDateEnd">Date End</label>
                    <div class="col-sm-10">
                        <input type="date" class="form-control"
                            id="inputUpdatedDateEnd" name="updatedDateEnd" min="<?= date('Y-m-d'); ?>" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedTimeEnd">Time End</label>
                    <div class="col-sm-10">
                        <input type="time" class="form-control"
                            id="inputUpdatedTimeEnd" name="updatedTimeEnd" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedNumberAllowed">Number Allowed</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputUpdatedNumberAllowed" name="updatedNumberAllowed" min="1" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedEventVenueID">Venue ID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputUpdatedEventVenueID" name="updatedVenueID" min="1" required/>
                    </div>
                  </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Update Event" name="editEvent" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end editEventModal -->


<!-- editSessionModal -->
<div class="modal fade" id="editSessionModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelEditSession" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelEditSession">
                    Edit Session
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="inputUpdatedSessionID">Session ID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" 
                        id="inputUpdatedSessionID" name="updatedSessionID" readonly required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="inputEvent">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" 
                        id="inputUpdatedSession" name="updatedSessionName" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedSessionNumberAllowed">Number Allowed</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputUpdatedSessionNumberAllowed" name="updatedSessionNumberAllowed" min="1" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedSessionEventID">Event ID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputUpdatedSessionEventID" name="updatedEventID" min="1" required/>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedSessionDateStart">Date Start</label>
                    <div class="col-sm-10">
                        <input type="date" class="form-control"
                            id="inputUpdatedSessionDateStart" name="updatedSessionDateStart" min="<?= date('Y-m-d'); ?>" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedSessionTimeStart">Time Start</label>
                    <div class="col-sm-10">
                        <input type="time" class="form-control"
                            id="inputUpdatedSessionTimeStart" name="updatedSessionTimeStart" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedSessionDateEnd">Date End</label>
                    <div class="col-sm-10">
                        <input type="date" class="form-control"
                            id="inputUpdatedSessionDateEnd" name="updatedSessionDateEnd" min="<?= date('Y-m-d'); ?>" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputUpdatedSessionTimeEnd">Time End</label>
                    <div class="col-sm-10">
                        <input type="time" class="form-control"
                            id="inputUpdatedSessionTimeEnd" name="updatedSessionTimeEnd" required />
                    </div>
                  </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Update Session" name="editSession" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end editSessionModal -->


<!-- addEventManagerModal -->
<div class="modal fade" id="addEventManagerModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelAddEventManager" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelAddEventManager">
                    Add Event Manager
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputEventManagerId">Event Manager User ID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputEventManagerId" name="eventManagerId" min="1" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="inputEventManagedId">Event ID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="inputEventManagedId" name="eventId" min="1" required />
                    </div>
                  </div>


            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Add Event Manager" name="addEventManager" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div> 
<!-- end addEventManagerModal -->



<!-- link JS -->
<?php 
    Utils::linkJS(); 
?>

</body>
</html>