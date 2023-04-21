<?php

session_start(); 
if(isset($_GET['eventID'])) {
    $_SESSION['eventID']=$_GET['eventID'];
    $_SESSION['sessionID'] = null;
}
if(isset($_GET['sessionID'])) {
    $_SESSION['sessionID']=$_GET['sessionID'];
    $_SESSION['eventID'] = null;
}

require_once('PDO.DB.class.php');
require_once('validations.php');

$db = new DB();

include 'Utils.php';

Utils::isUserLoggedIn();

$message = ""; //used for displaying error messages on form

// Check for Form Submission / Actions

// Editing event attendee
if (isset($_POST['editAttendee'])) {

    // fields: attendeeID, attendeeName, attendeePaid
    if (isset($_POST['attendeeID']) && isset($_POST['attendeeName']) && isset($_POST['attendeePaid'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if(($_POST['attendeeID'] != "") && (filter_var($_POST['attendeeID'], FILTER_VALIDATE_INT))) {
            $attendeeID = $_POST['attendeeID'];
        } else {
            $message .= "Attendee ID error. \n";
        }

        if(($_POST['attendeeName'] != "") && (strlen($_POST['attendeeName']) <= 100)) {
            $attendeeName = $_POST['attendeeName'];
        } else {
            $message .= "Attendee name must be populated and cannot exceed 100 characters. \n";
        }

        if(($_POST['attendeePaid'] == 0)|| ($_POST['attendeePaid'] == 1)) {
            $attendeePaid = $_POST['attendeePaid'];
        } else {
            $message .= "Paid must have a value of '0' for not paid or '1' for paid. \n";
        }

        // update event attendee
        if(isset($attendeeID) && isset($attendeeName) && isset($attendeePaid)) {
            if($db->updateEventAttendee($attendeeID, $attendeePaid) != -1) {
                $message .= "Event attendee updated successfully.";
            } else {
                $message .= "Unable to update event attendee.";
            }
        } else {
            $message .= "All fields must be populated.";
        }

    }

    // display any messages from form submissions / actions
    if ($message != "") {
        echo "<div class='alert alert-info' role='alert'>$message</div>";
    }

} // edit event attendee

// Deleting event attendee
if (isset($_POST['eventAttendeeID']) && isset($_SESSION['eventID'])) {

    // Sanitize & Validate
    foreach ($_POST as $key=>$value) {
        $_POST[$key] = sanitizeString($value);
    }

    if($_POST['eventAttendeeID'] != "") {
        $attendeeID = $_POST['eventAttendeeID'];
    } else {
        $message .= "Attendee ID error. \n";
    }

    if($_SESSION['eventID'] != "") {
        $eventID = $_SESSION['eventID'];
    } else {
        $message .= "Event ID error. \n";
    }

    // delete event attendee
    if(isset($attendeeID) && isset($eventID)) {
        if($db->deleteEventAttendee($attendeeID, $eventID) != -1) {
            $message .= "Event attendee removed successfully.";

            // delete attendee from any related sessions (attendee_session)
            $data = $db->getAllSessionsByEvent($eventID);
            foreach($data as $sess) {
                // delete from attendee_session
                $db->deleteSessionAttendee($attendeeID, $sess['idsession']);
            }

        } else {
            $message .= "Unable to removed attendee from event.";
        }
    }

    // display any messages from form submissions / actions
    if ($message != "") {
        echo "<div class='alert alert-info' role='alert'>$message</div>";
    }

} // deleting event attendee

// Deleting session attendee
if (isset($_POST['sessionAttendeeID']) && isset($_SESSION['sessionID'])) {

    // Sanitize & Validate
    foreach ($_POST as $key=>$value) {
        $_POST[$key] = sanitizeString($value);
    }

    if($_POST['sessionAttendeeID'] != "") {
        $attendeeID = $_POST['sessionAttendeeID'];
    } else {
        $message .= "Attendee ID error. \n";
    }

    if($_SESSION['sessionID'] != "") {
        $sessionID = $_SESSION['sessionID'];
    } else {
        $message .= "Session ID error. \n";
    }

    // delete session attendee
    if(isset($attendeeID) && isset($sessionID)) {
        if($db->deleteSessionAttendee($attendeeID, $sessionID) != -1) {
            $message .= "Session attendee removed successfully.";
        } else {
            $message .= "Unable to remove attendee from session.";
        }
    }

    // display any messages from form submissions / actions
    if ($message != "") {
        echo "<div class='alert alert-info' role='alert'>$message</div>";
    }

} // deleting session attendee

// Adding event attendee
if (isset($_POST['addAttendee'])) {

    // fields: attendeeAddName, attendeeAddPaid
    if (isset($_SESSION['eventID']) && isset($_POST['attendeeAddName']) && isset($_POST['attendeeAddPaid'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if($_SESSION['eventID'] != "") {
            $eventID = $_SESSION['eventID'];
        } else {
            $message .= "Event ID error. \n";
        }

        if(($_POST['attendeeAddName'] != "") && (strlen($_POST['attendeeAddName']) <= 100)) {
            $attendeeName = $_POST['attendeeAddName'];
            $attendeeID = $db->getAttendeeIdByName($attendeeName);
            if(!isset($attendeeID)) {
                $message .= "Cannot find attendee by that name.  Check spelling or add attendee as a user before adding to an event.\n";
            }
        } else {
            $message .= "Attendee name must be populated and cannot exceed 100 characters. \n";
        }

        if(($_POST['attendeeAddPaid'] == 0) || ($_POST['attendeeAddPaid'] == 1)) {
            $attendeePaid = $_POST['attendeeAddPaid'];
        } else {
            $message .= "Paid must have a value of '0' for not paid or '1' for paid. \n";
        }

        // add event attendee
        if(isset($eventID) && isset($attendeeID) && isset($attendeePaid)) {
            if($db->insertEventAttendee($eventID, $attendeeID, $attendeePaid) != -1) {
                $message .= "Attendee added to event successfully.";
            } else {
                $message .= "Unable to add attendee to event.";
            }
        }
    }

    // display any messages from form submissions / actions
    if ($message != "") {
        echo "<div class='alert alert-info' role='alert'>$message</div>";
    }

} // add event attendee


// Adding session attendee
if (isset($_POST['addSessionAttendee'])) {

    // fields: attendeeSessionAddName
    if (isset($_SESSION['sessionID']) && isset($_POST['attendeeSessionAddName'])) {

        // Sanitize & Validate
        foreach ($_POST as $key=>$value) {
            $_POST[$key] = sanitizeString($value);
        }

        if($_SESSION['sessionID'] != "") {
            $sessionID = $_SESSION['sessionID'];
        } else {
            $message .= "Session ID error. \n";
        }

        if(($_POST['attendeeSessionAddName'] != "") && (strlen($_POST['attendeeSessionAddName']) <= 100)) {
            $attendeeName = $_POST['attendeeSessionAddName'];
            $attendeeID = $db->getAttendeeIdByName($attendeeName);
            if(!isset($attendeeID)) {
                $message .= "Cannot find attendee by that name.  Check spelling or add attendee as a user before adding to an event/session.\n";
            }
        } else {
            $message .= "Attendee name must be populated and cannot exceed 100 characters. \n";
        }

        // add session attendee
        if(isset($sessionID) && isset($attendeeID)) {
            if($db->insertSessionAttendee($sessionID, $attendeeID) != -1) {
                $message .= "Attendee added to session successfully.";
            } else {
                $message .= "Unable to add attendee to session.";
            }
        }
    }

    // display any messages from form submissions / actions
    if ($message != "") {
        echo "<div class='alert alert-info' role='alert'>$message</div>";
    }

} // add session attendee

?>

<?php
	Utils::htmlHeader("Attendees");
?>

<div class="mainContainer">

<?php

    if(isset($_SESSION['eventID'])) {

        $data = $db->getEventById($_SESSION['eventID']);
        foreach($data as $evt) {
            echo "<h1>Event: " . $evt['name'] . "</h1>";
        }
    
        echo "<h2>Attendees</h2>";
    
        echo $db->getAttendeesByEventIdAsTable($_SESSION['eventID']);
    
        // button to add attendee to event
        echo "<div><input class='btn btn-primary btn-lg' type='button' value=':: Add Attendee To This Event ::' data-bs-toggle='modal' data-bs-target='#addAttendeeModal'></div>";
    }

    if(isset($_SESSION['sessionID'])) {

        $data = $db->getSessionById($_SESSION['sessionID']);
        foreach($data as $sess) {
            echo "<h1>Session: " . $sess['name'] . "</h1>";
        }

        echo "<h2>Attendees</h2>";

        echo $db->getAttendeesBySessionIdAsTable($_SESSION['sessionID']);

        // button to add attendee to session
        echo "<div><input class='btn btn-primary btn-lg' type='button' value=':: Add Attendee To This Session ::' data-bs-toggle='modal' data-bs-target='#addSessionAttendeeModal'></div>";
    }

?>

</div>

<!-- editAttendeeModal -->
<div class="modal fade" id="editAttendeeModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelEditAttendee" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelEditAttendee">
                    Edit Attendee
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="attendeeID">Attendee ID</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" 
                        id="attendeeID" name="attendeeID" readonly required/>
                    </div>
                  </div>
                  <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="attendeeName">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" 
                        id="attendeeName" name="attendeeName" readonly required />  
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="attendeePaid">Paid <br/> (0=No, 1=Yes)</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="attendeePaid" name="attendeePaid" min="0" max="1" required/>
                    </div>
                  </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Update Attendee" name="editAttendee" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end editAttendeeModal -->

<!-- addAttendeeModal -->
<div class="modal fade" id="addAttendeeModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelAddAttendee" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelAddAttendee">
                    Add Attendee
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                  <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="attendeeName">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" 
                        id="attendeeAddName" name="attendeeAddName" required />  
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label"
                          for="attendeePaid">Paid <br/> (0=No, 1=Yes)</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control"
                            id="attendeeAddPaid" name="attendeeAddPaid" min="0" max="1" required/>
                    </div>
                  </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Add Attendee" name="addAttendee" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end addAttendeeModal -->

<!-- addAttendeeSessionModal -->
<div class="modal fade" id="addSessionAttendeeModal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabelAddSessionAttendee" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" 
                   data-bs-dismiss="modal">
                       <span aria-hidden="true">&times;</span>
                       <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabelAddSessionAttendee">
                    Add Attendee
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                  <div class="form-group">
                    <label  class="col-sm-2 control-label"
                              for="attendeeAddSessionName">Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" 
                        id="attendeeAddSessionName" name="attendeeSessionAddName" required />  
                    </div>
                  </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-bs-dismiss="modal">
                            Close
                </button>
                <input type="submit" class="btn btn-success" value="Add Attendee" name="addSessionAttendee" />
            </div>
            
                </form>
            </div>
        </div>
    </div>
</div>
<!-- end addAttendeeSessionModal -->


<!-- link JS -->
<?php 
    Utils::linkJS(); 
?>

</body>
</html>