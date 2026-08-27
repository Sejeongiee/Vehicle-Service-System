<?php

include "includes/admin_auth.php";
include "includes/config.php";


/*
|--------------------------------------------------------------------------
| GET FORM VALUES
|--------------------------------------------------------------------------
*/

$reservation_id = intval(
    $_POST['reservation_id'] ?? 0
);

$mechanic_id = intval(
    $_POST['mechanic_id'] ?? 0
);


/*
|--------------------------------------------------------------------------
| VALIDATE
|--------------------------------------------------------------------------
*/

if (
    $reservation_id <= 0 ||
    $mechanic_id <= 0
) {

    header("Location: reservations.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET RESERVATION
|--------------------------------------------------------------------------
*/

$reservation_stmt = mysqli_prepare(
    $conn,
    "SELECT id, status, mechanic_id
     FROM reservations
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $reservation_stmt,
    "i",
    $reservation_id
);

mysqli_stmt_execute($reservation_stmt);

$reservation_result =
    mysqli_stmt_get_result(
        $reservation_stmt
    );

$reservation =
    mysqli_fetch_assoc(
        $reservation_result
    );

mysqli_stmt_close(
    $reservation_stmt
);


if (!$reservation) {

    header("Location: reservations.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| ONLY APPROVED RESERVATIONS
|--------------------------------------------------------------------------
*/

if (
    $reservation['status'] !== 'Approved' ||
    !empty($reservation['mechanic_id'])
) {

    header(
        "Location: reservation_view.php?id="
        . $reservation_id
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| GET MECHANIC
|--------------------------------------------------------------------------
*/

$mechanic_stmt = mysqli_prepare(
    $conn,
    "SELECT id, fullname, status
     FROM mechanics
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $mechanic_stmt,
    "i",
    $mechanic_id
);

mysqli_stmt_execute($mechanic_stmt);

$mechanic_result =
    mysqli_stmt_get_result(
        $mechanic_stmt
    );

$mechanic =
    mysqli_fetch_assoc(
        $mechanic_result
    );

mysqli_stmt_close(
    $mechanic_stmt
);


if (!$mechanic) {

    header(
        "Location: reservation_view.php?id="
        . $reservation_id
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| MECHANIC MUST BE AVAILABLE
|--------------------------------------------------------------------------
*/

if ($mechanic['status'] !== 'Available') {

    header(
        "Location: reservation_view.php?id="
        . $reservation_id
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| ASSIGN MECHANIC
|--------------------------------------------------------------------------
*/

mysqli_begin_transaction($conn);

try {

    /*
    |--------------------------------------------------------------------------
    | Update reservation
    |--------------------------------------------------------------------------
    */

    $update_reservation = mysqli_prepare(
        $conn,
        "UPDATE reservations
         SET mechanic_id = ?
         WHERE id = ?
         AND status = 'Approved'
         AND mechanic_id IS NULL"
    );

    mysqli_stmt_bind_param(
        $update_reservation,
        "ii",
        $mechanic_id,
        $reservation_id
    );

    mysqli_stmt_execute(
        $update_reservation
    );


    if (
        mysqli_stmt_affected_rows(
            $update_reservation
        ) !== 1
    ) {

        throw new Exception(
            "Reservation could not be updated."
        );

    }


    mysqli_stmt_close(
        $update_reservation
    );


    /*
    |--------------------------------------------------------------------------
    | Mark mechanic as Busy
    |--------------------------------------------------------------------------
    */

    $update_mechanic = mysqli_prepare(
        $conn,
        "UPDATE mechanics
         SET status = 'Busy'
         WHERE id = ?
         AND status = 'Available'"
    );

    mysqli_stmt_bind_param(
        $update_mechanic,
        "i",
        $mechanic_id
    );

    mysqli_stmt_execute(
        $update_mechanic
    );


    if (
        mysqli_stmt_affected_rows(
            $update_mechanic
        ) !== 1
    ) {

        throw new Exception(
            "Mechanic status could not be updated."
        );

    }


    mysqli_stmt_close(
        $update_mechanic
    );


    mysqli_commit($conn);

} catch (Exception $e) {

    mysqli_rollback($conn);

}


header(
    "Location: reservation_view.php?id="
    . $reservation_id
);

exit;