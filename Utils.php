<?php

class Utils {

    public static function pageName() {
        return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
    }

    public static function navigation() {
        $current_page = Utils::pageName();
        echo '<nav class="navbar navbar-expand-lg navbar-dark bg-dark p-3">
            <div class="container-fluid">
            <a class="navbar-brand" href="events.php">Event Registration</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="collapsibleNavbar">
                    <ul class="navbar-nav me-auto">'; // align items left
                    $activeClass = ($current_page=='events.php') ? 'active' : 'NULL';
                    echo '<li class="nav-item">
                        <a class="nav-link mx-2 '.$activeClass.'" aria-current="page" href="events.php"><i class="bi bi-calendar-event"></i> Events</a>
                    </li>';
                    $activeClass = ($current_page=='registrations.php') ? 'active' : 'NULL';
                    echo '<li class="nav-item">
                        <a class="nav-link mx-2 '.$activeClass.'" href="registrations.php"><i class="bi bi-pencil"></i> Registration</a>
                    </li>';
                    // only list admin page if user has admin or event manager role
                    if($_SESSION['role'] == '1' || $_SESSION['role'] == '2') {
                        $activeClass = ($current_page=='admin.php') ? 'active' : 'NULL';
                        echo '<li class="nav-item">
                            <a class="nav-link mx-2 '.$activeClass.'" href="admin.php"><i class="bi bi-person-workspace"></i> Admin</a>
                        </li>';
                        }
                    echo '</ul>';
                    // align logout right
                    echo '<ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                        <a class="nav-link mx-2" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>';
    } // navigation

    public static function htmlHeader($pageTitle) {
        echo "<!DOCTYPE html>";
        echo "<html lang='en-US'>";
        echo "<head>";
	    echo "<title>$pageTitle</title>";
	    echo "<meta charset='utf-8'>";
	    echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
        // bootstrap
	    echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css'>";
	    echo "<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js'></script>";
	    echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>";
        // icons
        echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css'>";
        // css stylesheet
        echo "<link rel='stylesheet' type='text/css' href='./styles.css'>";
        if(isset($_SESSION['userlogin'])) {
            Utils::navigation();
        }

    }

    public static function isUserLoggedIn() {
        if (!isset($_SESSION['userlogin'])) {
            // re-direct user login.php
            header("Location: login.php");
            exit;
        }
    }

    public static function linkJS() {
        echo "<script src='./script.js'></script>";
    }

} // Utils