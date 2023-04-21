<?php

class DB {

    private $dbh;

    function __construct() {

        try {

            $this->dbh = new PDO("mysql:host={$_SERVER['DB_SERVER']};dbname={$_SERVER['DB']}", $_SERVER['DB_USER'], $_SERVER['DB_PASSWORD']);

            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch(PDOException $pe) {
            echo $pe->getMessage(); // don't do this in production systems
            die("Bad database connection");
        }

    }//construct

// ********** EVENT **********

    // Event - READ/GET

    function getAllEventObjects() {

        $data = [];

        try {

            include_once "Event.class.php";

            $stmt = $this->dbh->prepare("SELECT event.idevent, event.name, datestart, dateend, numberallowed, event.venue, venue.name AS venueName
            FROM event JOIN venue ON event.venue = venue.idvenue ORDER BY event.idevent");

            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, "Event");  // class is case sensitive

            while ($event = $stmt->fetch()) {
                $data[] = $event;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getAllEventObjects


    function getEventObjectsById($id) {

        $data = [];

        try {

            include_once "Event.class.php";

            $stmt = $this->dbh->prepare("SELECT event.idevent, event.name, datestart, dateend, numberallowed, venue.name AS venue
            FROM event JOIN venue ON event.venue = venue.idvenue WHERE idevent = :id");

            $stmt->execute(["id"=>$id]);

            $stmt->setFetchMode(PDO::FETCH_CLASS, "Event");  // class is case sensitive

            while ($event = $stmt->fetch()) {
                $data[] = $event;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getEventObjectsById


    function getAllEventsAsTable() {

        $data = $this->getAllEventObjects();

        if ($data) {

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
           <tr><th>ID</th><th>Event Name</th><th>Start Date</th><th>End Date</th><th>Max Attendees</th><th>Venue ID</th><th>Venue Name</th><th>Edit</th><th>Delete</th><th>Add Session</th></tr>\n";

            foreach($data as $event) {
                $bigString .= $event->createRow();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>No events exist.</h2>";
        }

        return $bigString;

    }//getAllEventsAsTable


    function getAllEventsAsTableRegister() {

        $data = $this->getAllEventObjects();

        if ($data) {

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
            <tr><th>ID</th><th>Event Name</th><th>Start Date</th><th>End Date</th><th>Max Attendees</th><th>Venue</th><th>Register</th></tr>\n";

            foreach($data as $event) {
                $bigString .= $event->createRowRegister();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>No events exist.</h2>";
        }

        return $bigString;

    }//getAllEventsAsTableRegister


    function getEventsAsTableDelete($id) {

        $data = $this->getEventObjectsById($id);

        if ($data) {

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
                <tr><th>ID</th><th>Event Name</th><th>Start Date</th><th>End Date</th><th>Max Attendees</th><th>Venue</th><th>Delete</th></tr>\n";

            foreach($data as $event) {
                $bigString .= $event->createRowDelete();
            }

            $bigString .= "</table>";

        } else {
            echo "in else";
            $bigString = "<h2>No events exist.</h2>";
        }

        return $bigString;

    }//getEventsAsTableDelete


    function getEventNameById($id) {
       
        $data = [];
        
        try {

            $stmt = $this->dbh->prepare("SELECT name FROM event WHERE idevent = :id LIMIT 1"); // should only be 1 event with id passed in, but limit just in case
            $stmt->execute(["id"=>$id]);
            $data = $stmt->fetch();
            $data = $data['name'];

        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getEventNameById


    function getEventById($id) {
       
        $data = [];
        
        try {

            $stmt = $this->dbh->prepare("SELECT * FROM event WHERE idevent = :id");
            $stmt->execute(["id"=>$id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getEventById


    function getEventByName($eventName) {
       
        $data = [];
        
        try {

            $stmt = $this->dbh->prepare("SELECT * FROM event WHERE name = :eventName");
            $stmt->execute(["eventName"=>$eventName]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getEventByName


    function getEventIdByVenueId($id) {
       
        $data = [];
        
        try {

            $stmt = $this->dbh->prepare("SELECT idevent FROM event WHERE venue = :id");
            $stmt->execute(["id"=>$id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getEventIdByVenueId


    // Event - INSERT/ADD

    function insertNewEvent($name, $datestart, $dateend, $numallowed, $venueid) {

        try {

            $stmt = $this->dbh->prepare("INSERT INTO event (name, datestart, dateend, numberallowed, venue) VALUES (:name, :datestart, :dateend, :numallowed, :venueid)");

            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":datestart", $datestart, PDO::PARAM_STR);
            $stmt->bindParam(":dateend", $dateend, PDO::PARAM_STR);
            $stmt->bindParam(":numallowed", $numallowed, PDO::PARAM_INT);
            $stmt->bindParam(":venueid", $venueid, PDO::PARAM_INT);

            $stmt->execute();

            return $this->dbh->lastInsertId();

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//insertNewEvent


    // Event - UPDATE/EDIT

    function updateEvent($idevent, $name, $datestart, $dateend, $numberallowed, $venue) {

        try {

            $stmt = $this->dbh->prepare("UPDATE event SET name = :name, datestart = :datestart, dateend = :dateend, numberallowed = :numberallowed, venue = :venue WHERE idevent = :idevent");

            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":datestart", $datestart, PDO::PARAM_STR);
            $stmt->bindParam(":dateend", $dateend, PDO::PARAM_STR);
            $stmt->bindParam(":numberallowed", $numberallowed, PDO::PARAM_INT);
            $stmt->bindParam(":venue", $venue, PDO::PARAM_INT);
            $stmt->bindParam(":idevent", $idevent, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//updateEvent


    // Event - DELETE

    function deleteEvent($eventID) {

        try {

            $stmt = $this->dbh->prepare("DELETE FROM event WHERE idevent = :eventID");

            $stmt->bindParam(":eventID", $eventID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//deleteEvent
    

// ********** VENUE **********

    // Venue - READ/GET

    function getAllVenueObjects() {

        $data = [];

        try {

            include_once "Venue.class.php";

            $stmt = $this->dbh->prepare("SELECT * FROM venue ORDER BY idvenue");

            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, "Venue");  // class is case sensitive

            while ($venue = $stmt->fetch()) {
                $data[] = $venue;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getAllVenueObjects


    function getAllVenuesAsTable() {

        $data = $this->getAllVenueObjects();

        if ($data) {

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
                <tr><th>ID</th><th>Venue Name</th><th>Capacity</th><th>Edit</th><th>Delete</th><th>Add Event</th></tr>\n";

            foreach($data as $venue) {
                $bigString .= $venue->createRow();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>No venues exist.</h2>";
        }

        return $bigString;

    }//getAllVenuesAsTable

    function getAllVenuesAsTableForManager() {

        $data = $this->getAllVenueObjects();

        if ($data) {

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
                <tr><th>ID</th><th>Venue Name</th><th>Capacity</th><th>Add Event</th></tr>\n";

            foreach($data as $venue) {
                $bigString .= $venue->createRowForManager();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>No venues exist.</h2>";
        }

        return $bigString;

    }//getAllVenuesAsTableForManager


    function getVenueById($id) {

        $data = [];
            
        try {

            $stmt = $this->dbh->prepare("SELECT * FROM venue WHERE idvenue = :id LIMIT 1"); // should only be 1 venue with id passed in, but limit just in case
            $stmt->execute(["id"=>$id]);
            $data = $stmt->fetch();

        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getVenueById()


    // Venue - INSERT/ADD

    function insertNewVenue($name, $capacity) {

        try {

            $stmt = $this->dbh->prepare("INSERT INTO venue (name, capacity) VALUES (:name, :capacity)");

            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":capacity", $capacity, PDO::PARAM_INT);

            $stmt->execute();

            return $this->dbh->lastInsertId();

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//insertNewVenue

    
    // Venue - UPDATE/EDIT

    function  updateVenue($id, $name, $capacity) {

        try {

            $stmt = $this->dbh->prepare("UPDATE venue SET name = :name, capacity = :capacity WHERE idvenue = :id");

            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":capacity", $capacity, PDO::PARAM_INT);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//updateVenue


    // Venue - DELETE

    function deleteVenue($venueID) {

        try {

            $stmt = $this->dbh->prepare("DELETE FROM venue WHERE idvenue = :venueID");

            $stmt->bindParam(":venueID", $venueID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//deleteVenue


//  ********* SESSION **********

    // Session - READ/GET

    function getAllSessionObjects() {

        $data = [];

        try {

            include_once "Session.class.php";

            $stmt = $this->dbh->prepare("SELECT session.idsession, session.name AS name, session.numberallowed, session.event, event.name AS eventName, session.startdate, session.enddate FROM session
                                        JOIN event ON event.idevent = session.event");

            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, "Session");  // class is case sensitive

            while ($session = $stmt->fetch()) {
                $data[] = $session;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getAllSessionObjects


    function getSessionObjectsById($id) {

        $data = [];

        try {

            include_once "Session.class.php";

            $stmt = $this->dbh->prepare("SELECT session.idsession, session.name AS name, session.numberallowed, session.event, event.name AS eventName, session.startdate, session.enddate FROM session
                                        JOIN event ON event.idevent = session.event 
                                        WHERE idsession = :id");

            $stmt->execute(["id"=>$id]);

            $stmt->setFetchMode(PDO::FETCH_CLASS, "Session");  // class is case sensitive

            while ($session = $stmt->fetch()) {
                $data[] = $session;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getSessionObjectsById


    function getAllSessionsByEvent($eventId) {

        $data = [];
        
        try {
            // select all sessions with event of passed in id
            $stmt = $this->dbh->prepare("SELECT session.idsession, session.name, session.numberallowed, session.event, session.startdate, session.enddate
            FROM session WHERE event = :eventId");
            $stmt->execute(["eventId"=>$eventId]);

            while ($row = $stmt->fetch()) {
                $data[] = $row; // append to data array
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getAllSessionsByEvent


    function getAllSessionsAsTableByEvent($event) {

        $data = $this->getAllSessionsByEvent($event);

        if ($data) {

            // call function to get event name from event id
            $eventName = $this->getEventNameById($event);

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
                <tr><th>ID</th><th>Session Name</th><th>Max Attendees</th><th>Event ID</th><th>Event Name</th><th>Start</th><th>End</th></tr>\n";

            foreach($data as $row) {
                
                $startDate = date("g:ia", strtotime($row['startdate']));
                $endDate = date("g:ia", strtotime($row['enddate']));
                $bigString .= "<tr><td>{$row['idsession']}</td>
                            <td>{$row['name']}</td><td>{$row['numberallowed']}</td><td>{$eventName}</td>
                                <td>{$startDate}</td><td>{$endDate}</td>
                                </tr>\n";
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>No sessions exist.</h2>";
        }

        return $bigString;

    }//getAllSessionsAsTableByEvent


    function getAllSessions() {

        $data = [];
        
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM session");
            $stmt->execute();

            while ($row = $stmt->fetch()) {
                $data[] = $row; // append to data array
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getAllSessions


    function getAllSessionsAsTable() {

        $data = $this->getAllSessionObjects();

        if ($data) {

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
                <tr><th>ID</th><th>Session Name</th><th>Max Attendees</th><th>Event ID</th><th>Event Name</th><th>Start</th><th>End</th><th>Edit</th><th>Delete</th></tr>\n";

            foreach($data as $session) {
                $bigString .= $session->createRow();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>No sessions exist.</h2>";
        }

        return $bigString;

    }//getAllSessionsAsTable


    function getAllSessionsAsTableRegister() {

        $data = $this->getAllSessionObjects();

        if ($data) {

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
                <tr><th>ID</th><th>Session Name</th><th>Max Attendees</th><th>Event ID</th><th>Event Name</th><th>Start</th><th>End</th><th>Register</th></tr>\n";

            foreach($data as $session) {
                $bigString .= $session->createRowRegister();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>No sessions exist.</h2>";
        }

        return $bigString;

    }//getAllSessionsAsTableRegister


    function getSessionsAsTableDelete($id) {

        $data = $this->getSessionObjectsById($id);

        if ($data) {

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
                <tr><th>ID</th><th>Session Name</th><th>Max Attendees</th><th>Event ID</th><th>Event Name</th><th>Start</th><th>End</th><th>Delete</th></tr>\n";

            foreach($data as $session) {
                $bigString .= $session->createRowDelete();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>No sessions exist.</h2>";
        }

        return $bigString;

    }//getSessionsAsTableDelete


    function getSessionById($id) {
        
        $data = [];
        
        try {

            $stmt = $this->dbh->prepare("SELECT * FROM session WHERE idsession = :id");
            $stmt->execute(["id"=>$id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getSessionById


    // Session - INSERT/ADD

    function insertNewSession($name, $numallowed, $eventid, $startdate, $enddate) {

        try {

            $stmt = $this->dbh->prepare("INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES (:name, :numallowed, :eventid, :startdate, :enddate)");

            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":numallowed", $numallowed, PDO::PARAM_INT);
            $stmt->bindParam(":eventid", $eventid, PDO::PARAM_INT);
            $stmt->bindParam(":startdate", $startdate, PDO::PARAM_STR);
            $stmt->bindParam(":enddate", $enddate, PDO::PARAM_STR);

            $stmt->execute();

            return $this->dbh->lastInsertId();

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//insertNewSession


    // Session - UPDATE/EDIT

    function updateSession($id, $name, $numallowed, $eventid, $startdate, $enddate) {

        try {

            $stmt = $this->dbh->prepare("UPDATE session SET name = :name, numberallowed = :numallowed, event = :eventid, startdate = :startdate, enddate = :enddate WHERE idsession = :id");

            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":numallowed", $numallowed, PDO::PARAM_INT);
            $stmt->bindParam(":eventid", $eventid, PDO::PARAM_INT);
            $stmt->bindParam(":startdate", $startdate, PDO::PARAM_STR);
            $stmt->bindParam(":enddate", $enddate, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//updateSession

    
    // Session - DELETE

    function deleteSession($sessionID) {

        try {

            $stmt = $this->dbh->prepare("DELETE FROM session WHERE idsession = :sessionID");

            $stmt->bindParam(":sessionID", $sessionID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//deleteSession


// ********** ATTENDEE (User) **********

    // Attendee - READ/GET

    function getAttendeeIdByName($name) {

        $data = [];
        
        try {
    
            $stmt = $this->dbh->prepare("SELECT idattendee FROM attendee WHERE name = :name");
            $stmt->execute(["name"=>$name]);
            $data = $stmt->fetch();
            if($data) {
                $data = $data['idattendee'];
            } else {
                $data = null;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getAttendeeIdByName


    function getAllUserObjects() {

        $data = [];

        try {

            include_once "User.class.php";

            $stmt = $this->dbh->prepare("SELECT * FROM attendee");

            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, "User");  // class is case sensitive

            while ($session = $stmt->fetch()) {
                $data[] = $session;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getAllUserObjects


    function getAllUsersAsTable() {

        $data = $this->getAllUserObjects();

        if ($data) {

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
                <tr><th>ID</th><th>User Name</th><th>Password</th><th>Role</th><th>Edit</th><th>Delete</th></tr>\n";

            foreach($data as $user) {
                $bigString .= $user->createRow();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>No users exist.</h2>";
        }

        return $bigString;

    }//getAllUsersAsTable


    // Attendee - INSERT/ADD

    function insertNewUser($name, $password, $role) {

        try {

            $stmt = $this->dbh->prepare("INSERT INTO attendee (name, password, role) VALUES (:name, :password,:role)");

            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":password", $password, PDO::PARAM_STR);
            $stmt->bindParam(":role", $role, PDO::PARAM_INT);

            $stmt->execute();

            return $this->dbh->lastInsertId();

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//insertNewUser


    // Attendee - UPDATE/EDIT

    function updateUser($userID, $name, $password, $role) {

        try {

            $stmt = $this->dbh->prepare("UPDATE attendee SET name = :name, password = :password, role = :role WHERE idattendee = :userID");

            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":password", $password, PDO::PARAM_STR);
            $stmt->bindParam(":role", $role, PDO::PARAM_INT);
            $stmt->bindParam(":userID", $userID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//updateUser


    // Attendee - DELETE

    function deleteUser($userID) {

        try {

            $stmt = $this->dbh->prepare("DELETE FROM attendee WHERE idattendee = :userID");

            $stmt->bindParam(":userID", $userID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//deleteUser


// ********** ATTENDEE_EVENT **********

    // Attendee_Event - READ/GET

    function getAttendeeEventIds($name) {

        $data = [];

        // call function to get attendee ID from attendee name
        $attendee = $this->getAttendeeIdByName($name);
            
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM attendee_event WHERE attendee = :attendee");
            $stmt->bindParam(":attendee", $attendee, PDO::PARAM_INT);
            $stmt->execute();

            while ($row = $stmt->fetch()) {
                $data[] = $row; // append to data array
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getAttendeeEventIds


    function getAttendeeEventObjectsById($id) {

        $data = [];

        try {

            include_once "Attendee_Event.class.php";

            $stmt = $this->dbh->prepare("SELECT attendee_event.attendee, attendee.name AS attendeeName, attendee_event.paid FROM attendee_event
                                        JOIN attendee ON attendee.idattendee = attendee_event.attendee
                                            WHERE attendee_event.event = :id");

            $stmt->execute(["id"=>$id]);

            $stmt->setFetchMode(PDO::FETCH_CLASS, "Attendee_Event");  // class is case sensitive

            while ($attendeeEvent = $stmt->fetch()) {
                $data[] = $attendeeEvent;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getAttendeeEventObjectsById


    function getAttendeesByEventIdAsTable($eventID) {

        $data = $this->getAttendeeEventObjectsById($eventID);

        if ($data) {
            
            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
                <tr><th>Attendee ID</th><th>Attendee Name</th><th>Paid</th><th>Edit</th><th>Delete</th></tr>\n";

            foreach($data as $attendeeEvent) {
                $bigString .= $attendeeEvent->createRow();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>There are no attendees for this event.</h2>";
        }

        return $bigString;

    }//getAttendeesByEventIdAsTable


    function isUserRegisteredEvent($eventID, $userID) {
            
        try {

            $stmt = $this->dbh->prepare("SELECT * FROM attendee_event WHERE event = :eventID AND attendee = :userID");
            $stmt->bindParam(":eventID", $eventID, PDO::PARAM_INT);
            $stmt->bindParam(":userID", $userID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else {
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//isUserRegisteredEvent


    // Attendee_Event - INSERT/ADD

    function insertEventAttendee($eventID, $attendeeID, $attendeePaid) {

        try {

            $stmt = $this->dbh->prepare("INSERT INTO attendee_event (event, attendee, paid) VALUES (:eventID, :attendeeID, :attendeePaid)");

            $stmt->bindParam(":eventID", $eventID, PDO::PARAM_INT);
            $stmt->bindParam(":attendeeID", $attendeeID, PDO::PARAM_INT);
            $stmt->bindParam(":attendeePaid", $attendeePaid, PDO::PARAM_INT);

            $stmt->execute();

            return $this->dbh->lastInsertId();

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//insertEventAttendee

    
    // Attendee_Event - UPDATE/EDIT

    function updateEventAttendee($attendeeID, $attendeePaid) {

        try {

            $stmt = $this->dbh->prepare("UPDATE attendee_event SET paid = :attendeePaid WHERE attendee = :attendeeID");

            $stmt->bindParam(":attendeePaid", $attendeePaid, PDO::PARAM_INT);
            $stmt->bindParam(":attendeeID", $attendeeID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//updateEventAttendee


    // Attendee_Event - DELETE

    function deleteEventAttendee($attendeeID, $eventID) {

        try {

            $stmt = $this->dbh->prepare("DELETE FROM attendee_event WHERE attendee = :attendeeID AND event = :eventID");

            $stmt->bindParam(":attendeeID", $attendeeID, PDO::PARAM_INT);
            $stmt->bindParam(":eventID", $eventID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//deleteEventAttendee

    function deleteEventAttendeeByEvent($eventID) {

        try {

            $stmt = $this->dbh->prepare("DELETE FROM attendee_event WHERE event = :eventID");

            $stmt->bindParam(":eventID", $eventID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//deleteEventAttendeeByEvent


// ********** ATTENDEE_SESSION *********

    // Attendee_Session - READ/GET

    function getAttendeeSessionIds($name) {

        $data = [];

        // call function to get attendee ID from attendee name
        $attendee = $this->getAttendeeIdByName($name);
            
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM attendee_session WHERE attendee = :attendee");
            $stmt->bindParam(":attendee", $attendee, PDO::PARAM_INT);
            $stmt->execute();

            while ($row = $stmt->fetch()) {
                $data[] = $row; // append to data array
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getAttendeeSessionIds


    function getAttendeeSessionObjectsById($id) {

        $data = [];

        try {

            include_once "Attendee_Session.class.php";

            $stmt = $this->dbh->prepare("SELECT attendee_session.session, attendee_session.attendee, attendee.name AS attendeeName FROM attendee_session
                                        JOIN attendee ON attendee.idattendee = attendee_session.attendee
                                            WHERE attendee_session.session = :id");

            $stmt->execute(["id"=>$id]);

            $stmt->setFetchMode(PDO::FETCH_CLASS, "Attendee_Session");  // class is case sensitive

            while ($attendeeSession = $stmt->fetch()) {
                $data[] = $attendeeSession;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getAttendeeSessionObjectsById


    function getAttendeesBySessionIdAsTable($sessionID) {

        $data = $this->getAttendeeSessionObjectsById($sessionID);

        if ($data) {
            
            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
                <tr><th>Attendee ID</th><th>Attendee Name</th><th>Delete</th></tr>\n";

            foreach($data as $attendeeSession) {
                $bigString .= $attendeeSession->createRow();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>There are no attendees for this session.</h2>";
        }

        return $bigString;

    }//getAttendeesBySessionIdAsTable


    // Attendee_Session - INSERT/ADD

    function insertSessionAttendee($sessionID, $attendeeID) {

        try {

            $stmt = $this->dbh->prepare("INSERT INTO attendee_session (session, attendee) VALUES (:sessionID, :attendeeID)");

            $stmt->bindParam(":sessionID", $sessionID, PDO::PARAM_INT);
            $stmt->bindParam(":attendeeID", $attendeeID, PDO::PARAM_INT);

            $stmt->execute();

            return $this->dbh->lastInsertId();

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//insertSessionAttendee


    // Attendee_Session - DELETE

    function deleteSessionAttendee($attendeeID, $sessionID) {

        try {

            $stmt = $this->dbh->prepare("DELETE FROM attendee_session WHERE attendee = :attendeeID AND session = :sessionID");

            $stmt->bindParam(":attendeeID", $attendeeID, PDO::PARAM_INT);
            $stmt->bindParam(":sessionID", $sessionID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//deleteSessionAttendee
    

    function deleteSessionAttendeeBySession($sessionID) {

        try {

            $stmt = $this->dbh->prepare("DELETE FROM attendee_session WHERE session = :sessionID");

            $stmt->bindParam(":sessionID", $sessionID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//deleteSessionAttendeeBySession


// ********** LOGIN **********

    function login($username, $password) {

        try {
            $stmt = $this->dbh->prepare("SELECT name, password FROM attendee WHERE name =:name AND password =:password");
            $stmt->bindParam(':name', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->execute();

            $data = $stmt->fetch();
            if ($data) {
                return true;
            }
        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return false;

    }//login


    function getRole($username, $password) {

        try {
            $stmt = $this->dbh->prepare("SELECT role FROM attendee WHERE name =:name AND password =:password");
            $stmt->bindParam(':name', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->execute();

            $data = $stmt->fetch();

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data["role"];

    }//getRole


    function getUserId($username, $password) {

        try {
            $stmt = $this->dbh->prepare("SELECT idattendee FROM attendee WHERE name =:name AND password =:password");
            $stmt->bindParam(':name', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->execute();

            $data = $stmt->fetch();

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data["idattendee"];

    }//getUserId


// ********** EVENT MANAGER *********

    function getEventObjectsByManagerId($id) {

        $data = [];

        try {

            include_once "Event.class.php";

            $stmt = $this->dbh->prepare("SELECT event.idevent, event.name, datestart, dateend, numberallowed, venue, venue.name AS venueName
            FROM event JOIN venue ON event.venue = venue.idvenue 
            JOIN manager_event ON event.idevent = manager_event.event
            WHERE manager = :id");

            $stmt->execute(["id"=>$id]);

            $stmt->setFetchMode(PDO::FETCH_CLASS, "Event");  // class is case sensitive

            while ($event = $stmt->fetch()) {
                $data[] = $event;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getEventObjectsByManagerId


    function getManagerEventsAsTable($id) {

        $data = $this->getEventObjectsByManagerId($id);

        if ($data) {

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
            <tr><th>ID</th><th>Event Name</th><th>Start Date</th><th>End Date</th><th>Max Attendees</th><th>Venue ID</th><th>Venue Name</th><th>Edit</th><th>Delete</th><th>Add Session</th></tr>\n";

            foreach($data as $event) {
                $bigString .= $event->createRow();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>No events exist.</h2>";
        }

        return $bigString;

    }//getManagerEventsAsTable


    function getSessionObjectsByManagerId($id) {

        $data = [];

        try {

            include_once "Session.class.php";

            $stmt = $this->dbh->prepare("SELECT session.idsession, session.name AS name, session.numberallowed, session.event, event.name AS eventName, session.startdate, session.enddate FROM session
                                        JOIN event ON event.idevent = session.event 
                                        JOIN venue ON event.venue = venue.idvenue
                                        JOIN manager_event ON event.idevent = manager_event.event 
                                        WHERE manager = :id");

            $stmt->execute(["id"=>$id]);

            $stmt->setFetchMode(PDO::FETCH_CLASS, "Session");  // class is case sensitive

            while ($session = $stmt->fetch()) {
                $data[] = $session;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
        }

        return $data;

    }//getSessionObjectsByManagerId


    function getManagerSessionsAsTable($id) {

        $data = $this->getSessionObjectsByManagerId($id);

        if ($data) {

            $bigString = "<table class='table table-striped table-bordered table-hover'>\n 
                <tr><th>ID</th><th>Session Name</th><th>Max Attendees</th><th>Event ID</th><th>Event Name</th><th>Start</th><th>End</th><th>Edit</th><th>Delete</th></tr>\n";

            foreach($data as $session) {
                $bigString .= $session->createRow();
            }

            $bigString .= "</table>";

        } else {
            $bigString = "<h2>No sessions exist.</h2>";
        }

        return $bigString;

    }//getManagerSessionsAsTable


// ********** MANAGER_EVENT **********

    function insertEventManager($eventID, $managerID) {

        try {

            $stmt = $this->dbh->prepare("INSERT INTO manager_event (event, manager) VALUES (:eventID, :managerID)");

            $stmt->bindParam(":eventID", $eventID, PDO::PARAM_INT);
            $stmt->bindParam(":managerID", $managerID, PDO::PARAM_INT);

            $stmt->execute();

            return $this->dbh->lastInsertId();

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//insertEventManager


    function deleteEventManager($eventID) {

        try {

            $stmt = $this->dbh->prepare("DELETE FROM manager_event WHERE event = :eventID");

            $stmt->bindParam(":eventID", $eventID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//deleteEventManager


    function deleteEventManagerByUser($managerID) {

        try {

            $stmt = $this->dbh->prepare("DELETE FROM manager_event WHERE manager = :managerID");

            $stmt->bindParam(":managerID", $managerID, PDO::PARAM_INT);

            $stmt->execute();

            $count = $stmt->rowCount();

            if($count =='0'){
                return -1;
            }
            else{
                return 1;
            }

        } catch(PDOException $e) {
            echo $e->getMessage();
            return -1;
        }

    }//deleteEventManagerByUser


}//DB