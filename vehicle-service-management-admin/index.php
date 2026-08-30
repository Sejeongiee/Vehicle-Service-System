<?php

include "includes/config.php";


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (
    isset($_SESSION['admin_id']) &&
    ($_SESSION['admin_role'] ?? '') === 'staff'
) {

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/dashboard.php"
    );

} else {

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/login.php"
    );

}


exit;