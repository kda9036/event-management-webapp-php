<?php

class Event {
    private $idevent; // match database columns in terms of case sensitivity
    private $name;
    private $datestart;
    private $dateend;
    private $numberallowed;
    private $venue; // id
    private $venueName;

    function createRow() {
        $startDate = date("m/d/Y", strtotime($this->datestart));
        $startTime = date("g:ia", strtotime($this->datestart));
        $endDate = date("m/d/Y", strtotime($this->dateend));
        $endTime = date("g:ia", strtotime($this->dateend));
        $bigString = "<form action='' method='post'>
                        <tr><td><input type='hidden' name='eventID' value='{$this->idevent}'/>{$this->idevent}</td><td><a href='attendees.php?eventID={$this->idevent}'>{$this->name}</a></td>
                        <td>{$startDate} {$startTime}</td> <td>{$endDate} {$endTime}</td>
                        <td>{$this->numberallowed}</td><td>{$this->venue}</td><td>{$this->venueName}</td>
                        <td><input class='btn btn-primary' type='button' value='Edit' data-eventname=\"{$this->name}\" data-eventid='{$this->idevent}' data-eventvenueid='{$this->venue}' data-bs-toggle='modal' data-bs-target='#editEventModal' /></td>
                        <td><input class='btn btn-danger' type='submit' value='Delete'/><td><input class='btn btn-success' type='button' value='Add Session' data-eventid='{$this->idevent}' data-bs-toggle='modal' data-bs-target='#addSessionModal' /></td></tr>
                    </form>";

        return $bigString;
    }

    function createRowRegister() {
        $startDate = date("m/d/Y", strtotime($this->datestart));
        $startTime = date("g:ia", strtotime($this->datestart));
        $endDate = date("m/d/Y", strtotime($this->dateend));
        $endTime = date("g:ia", strtotime($this->dateend));
        $bigString = "<form action='' method='post'>
                        <tr><td><input type='hidden' name='eventID' value='{$this->idevent}'/>{$this->idevent}</td><td>{$this->name}</td> <td>{$startDate} {$startTime}</td> <td>{$endDate} {$endTime}</td>
                        <td>{$this->numberallowed}</td><td>{$this->venueName}</td><td><input class='btn btn-success' type='submit' value='Register' name='registerEvent' /></td></tr>
                    </form>";

        return $bigString;
    }

    function createRowDelete() {
        $startDate = date("m/d/Y", strtotime($this->datestart));
        $startTime = date("g:ia", strtotime($this->datestart));
        $endDate = date("m/d/Y", strtotime($this->dateend));
        $endTime = date("g:ia", strtotime($this->dateend));
        $bigString = "<form action='' method='post'>
                        <tr><td><input type='hidden' name='deleteEventID' value='{$this->idevent}'/>{$this->idevent}</td><td>{$this->name}</td> <td>{$startDate} {$startTime}</td> <td>{$endDate} {$endTime}</td>
                        <td>{$this->numberallowed}</td><td>{$this->venue}</td>
                        <td><input class='btn btn-danger' type='submit' value='Delete'/></td></tr>
                    </form>";

        return $bigString;
    }

}//Event