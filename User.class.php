<?php

class User {
    private $idattendee; // match database columns in terms of case sensitivity
    private $name;
    private $password;
    private $role;

    function createRow() {
        $bigString = "<form action='' method='post'>
                        <tr><td><input type='hidden' name='userID' value='{$this->idattendee}'/>{$this->idattendee}</td><td>{$this->name}</td> <td>{$this->password}</td> <td>{$this->role}</td>";
        // Kelly Appleton is master admin - do not allow edit or delete of master admin
        if($this->name == "Kelly Appleton") {
            $bigString .= "<td></td><td></td></tr></form>";
        } else {             
            $bigString .= "<td><input class='btn btn-primary' type='button' value='Edit' data-userid='{$this->idattendee}' data-username='{$this->name}' data-bs-toggle='modal' data-bs-target='#editUserModal' /></td>
                    <td><input class='btn btn-danger' type='submit' value='Delete'/></td></tr>
                    </form>";
        }

        return $bigString;
    }


}//User