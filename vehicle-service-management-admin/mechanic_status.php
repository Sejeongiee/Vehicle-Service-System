<?php

include "includes/admin_auth.php";
include "includes/config.php";


$mechanic_id = intval(
    $_POST['mechanic_id'] ?? 0
);

$action = $_POST['action'] ?? '';


if ($mechanic_id <= 0) {

    header("Location: mechanics.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET MECHANIC
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, status
     FROM mechanics
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $mechanic_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$mechanic = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$mechanic) {

    header("Location: mechanics.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| CHECK ACTIVE RESERVATIONS
|--------------------------------------------------------------------------
*/

$active_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM reservations
     WHERE mechanic_id = ?
     AND status IN ('Approved', 'In Progress')"
);

mysqli_stmt_bind_param(
    $active_stmt,
    "i",
    $mechanic_id
);

mysqli_stmt_execute($active_stmt);

$active_result =
    mysqli_stmt_get_result($active_stmt);

$active =
    mysqli_fetch_assoc($active_result);

$active_count =
    intval($active['total']);

mysqli_stmt_close($active_stmt);


/*
|--------------------------------------------------------------------------
| DEACTIVATE
|--------------------------------------------------------------------------
*/

if ($action === 'deactivate') {

    if (
        $active_count === 0 &&
        $mechanic['status'] === 'Available'
    ) {

        $update_stmt = mysqli_prepare(
            $conn,
            "UPDATE mechanics
             SET status = 'Inactive'
             WHERE id = ?
             AND status = 'Available'"
        );

        mysqli_stmt_bind_param(
            $update_stmt,
            "i",
            $mechanic_id
        );

        mysqli_stmt_execute($update_stmt);

        mysqli_stmt_close($update_stmt);

    }

}


/*
|--------------------------------------------------------------------------
| ACTIVATE
|--------------------------------------------------------------------------
*/

if ($action === 'activate') {

    if (
        $active_count === 0 &&
        $mechanic['status'] === 'Inactive'
    ) {

        $update_stmt = mysqli_prepare(
            $conn,
            "UPDATE mechanics
             SET status = 'Available'
             WHERE id = ?
             AND status = 'Inactive'"
        );

        mysqli_stmt_bind_param(
            $update_stmt,
            "i",
            $mechanic_id
        );

        mysqli_stmt_execute($update_stmt);

        mysqli_stmt_close($update_stmt);

    }

}


header("Location: mechanics.php");
exit;