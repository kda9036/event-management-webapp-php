<?php

class Attendee_Event {
    private $event; // match database columns in terms of case sensitivity; event id
    private $attendee; // id
    private $paid; // 0 no, 1 yes
    private $attendeeName;

    function createRow() {
        $bigString = "<form action='' method='post'>
                        <tr>
                        <td><input type='hidden' name='eventAttendeeID' value='{$this->attendee}'/>{$this->attendee}</td><td>{$this->attendeeName}</td><td>{$this->paid}</td>
                        <td><input class='btn btn-primary' type='button' value='Edit' data-attendeeid='{$this->attendee}' data-attendeename='{$this->attendeeName}' data-bs-toggle='modal' data-bs-target='#editAttendeeModal' /></td>
                        <td><input class='btn btn-danger' type='submit' value='Delete'/></td></tr>
                    </form>";

        return $bigString;
    }
}//Attendee_Event