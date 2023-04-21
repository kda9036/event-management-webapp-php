<?php

class Session {
    private $idsession;
    private $name;
    private $numberallowed;
    private $event; // id
    private $startdate;
    private $enddate;
    private $eventName;

    function createRow() {
        $startDate = date("m/d/Y", strtotime($this->startdate));
        $startTime = date("g:ia", strtotime($this->startdate));
        $endDate = date("m/d/Y", strtotime($this->enddate));
        $endTime = date("g:ia", strtotime($this->enddate));
        $bigString = "<form action='' method='post'>
                        <tr><td><input type='hidden' name='sessionID' value='{$this->idsession}'/>{$this->idsession}</td><td><a href='attendees.php?sessionID={$this->idsession}'>{$this->name}</a></td>
                        <td>{$this->numberallowed}</td><td>{$this->event}</td><td>{$this->eventName}</td>
                        <td>{$startDate} {$startTime}</td>
                        <td>{$endDate} {$endTime}</td><td><input class='btn btn-primary' type='button' value='Edit' data-sessionname=\"{$this->name}\" data-sessionid='{$this->idsession}' data-sessioneventid='{$this->event}' data-bs-toggle='modal' data-bs-target='#editSessionModal' /></td>
                        <td><input class='btn btn-danger' type='submit' value='Delete'/></td></tr>
                        </form>";
        return $bigString;
    }

    function createRowRegister() {
        $startDate = date("m/d/Y", strtotime($this->startdate));
        $startTime = date("g:ia", strtotime($this->startdate));
        $endDate = date("m/d/Y", strtotime($this->enddate));
        $endTime = date("g:ia", strtotime($this->enddate));
        $bigString = "<form action='' method='post'>
                        <tr><td><input type='hidden' name='sessionID' value='{$this->idsession}'/>{$this->idsession}</td><td>{$this->name}</td><td>{$this->numberallowed}</td><td>{$this->event}</td><td>{$this->eventName}</td>
                        <td>{$startDate} {$startTime}</td><td>{$endDate} {$endTime}</td><td><input class='btn btn-success' type='submit' value='Register' name='registerSession' /></td></tr>
                        </form>";
        return $bigString;
    }

    function createRowDelete() {
        $startDate = date("m/d/Y", strtotime($this->startdate));
        $startTime = date("g:ia", strtotime($this->startdate));
        $endDate = date("m/d/Y", strtotime($this->enddate));
        $endTime = date("g:ia", strtotime($this->enddate));
        $bigString = "<form action='' method='post'>
                        <tr><td><input type='hidden' name='deleteSessionID' value='{$this->idsession}'/>{$this->idsession}</td><td>{$this->name}</td><td>{$this->numberallowed}</td><td>{$this->event}</td><td>{$this->eventName}</td>
                        <td>{$startDate} {$startTime}</td><td>{$endDate} {$endTime}</td>
                        <td><input class='btn btn-danger' type='submit' value='Delete'/></td></tr>
                        </form>";
        return $bigString;
    }

}//Session