<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['admin_role'])
) {

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/login.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| STAFF ACCESS ONLY
|--------------------------------------------------------------------------
*/

if ($_SESSION['admin_role'] !== 'staff') {

    session_unset();
    session_destroy();

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/login.php"
    );

    exit;
}