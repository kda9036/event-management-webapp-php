<?php

class Attendee_Session {
    private $session; // match database columns in terms of case sensitivity; session id
    private $attendee; // id
    private $attendeeName;

    function createRow() {
        $bigString = "<form action='' method='post'>
                        <tr>
                        <td><input type='hidden' name='sessionAttendeeID' value='{$this->attendee}'/>{$this->attendee}</td><td>{$this->attendeeName}</td>
                        <td><input class='btn btn-danger' type='submit' value='Delete'/></td></tr>
                    </form>";

        return $bigString;
    }
}//Attendee_Session