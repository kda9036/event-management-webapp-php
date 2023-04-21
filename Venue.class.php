<?php

class Venue {
    private $idvenue;
    private $name;
    private $capacity;

    function createRow() {
        $bigString = "<form action='' method='post'>
                        <tr><td><input type='hidden' name='venueID' value='{$this->idvenue}'/>{$this->idvenue}</td><td>{$this->name}</td> <td>{$this->capacity}</td>
                        <td><input class='btn btn-primary' type='button' value='Edit' data-venuename=\"{$this->name}\" data-venueid='{$this->idvenue}' data-bs-toggle='modal' data-bs-target='#editVenueModal'/>
                        </td><td><input class='btn btn-danger' type='submit' value='Delete'/></td>
                        <td><input class='btn btn-success' type='button' value='Add Event' data-venueid='{$this->idvenue}' data-bs-toggle='modal' data-bs-target='#addEventModal'/></td></tr>
                    </form>";

        return $bigString;
    }

    function createRowForManager() {
        $bigString = "<form action='' method='post'>
                        <tr><td><input type='hidden' name='venueID' value='{$this->idvenue}'/>{$this->idvenue}</td><td>{$this->name}</td> <td>{$this->capacity}</td>
                        <td><input class='btn btn-success' type='button' value='Add Event' data-venueid='{$this->idvenue}' data-bs-toggle='modal' data-bs-target='#addEventModal'/></td></tr>
                    </form>";

        return $bigString;
    }
}//Venue