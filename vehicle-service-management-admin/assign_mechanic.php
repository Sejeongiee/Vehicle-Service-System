<?php

include_once __DIR__ . "/includes/config.php";
include_once __DIR__ . "/includes/admin_auth.php";

/*
|--------------------------------------------------------------------------
| POST ONLY
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/reservations.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| GET VALUES
|--------------------------------------------------------------------------
*/

$reservation_id =
    intval($_POST['reservation_id'] ?? 0);

$mechanic_id =
    intval($_POST['mechanic_id'] ?? 0);


if (
    $reservation_id <= 0 ||
    $mechanic_id <= 0
) {

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/reservations.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| GET RESERVATION
|--------------------------------------------------------------------------
*/

$reservation_stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        status,
        mechanic_id,
        appointment_date,
        appointment_time

     FROM reservations

     WHERE id = ?

     LIMIT 1"
);


mysqli_stmt_bind_param(
    $reservation_stmt,
    "i",
    $reservation_id
);


mysqli_stmt_execute(
    $reservation_stmt
);


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

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/reservations.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| MUST BE APPROVED AND UNASSIGNED
|--------------------------------------------------------------------------
*/

if (
    $reservation['status'] !== 'Approved' ||
    !empty($reservation['mechanic_id'])
) {

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/reservation_view.php?id="
        . $reservation_id
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK MECHANIC
|--------------------------------------------------------------------------
*/

$mechanic_stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        fullname,
        status

     FROM mechanics

     WHERE id = ?

     LIMIT 1"
);


mysqli_stmt_bind_param(
    $mechanic_stmt,
    "i",
    $mechanic_id
);


mysqli_stmt_execute(
    $mechanic_stmt
);


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


if (
    !$mechanic ||
    $mechanic['status'] !== 'Available'
) {

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/reservation_view.php?id="
        . $reservation_id
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK SAME-SCHEDULE CONFLICT
|--------------------------------------------------------------------------
|
| A mechanic must not be assigned to another Approved or
| In Progress reservation at the same appointment date/time.
|
*/

$conflict_stmt = mysqli_prepare(
    $conn,
    "SELECT id

     FROM reservations

     WHERE mechanic_id = ?
     AND appointment_date = ?
     AND appointment_time = ?
     AND status IN (
        'Approved',
        'In Progress'
     )
     AND id <> ?

     LIMIT 1"
);


mysqli_stmt_bind_param(
    $conflict_stmt,
    "issi",
    $mechanic_id,
    $reservation['appointment_date'],
    $reservation['appointment_time'],
    $reservation_id
);


mysqli_stmt_execute(
    $conflict_stmt
);


$conflict_result =
    mysqli_stmt_get_result(
        $conflict_stmt
    );


$conflict =
    mysqli_fetch_assoc(
        $conflict_result
    );


mysqli_stmt_close(
    $conflict_stmt
);


if ($conflict) {

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/reservation_view.php?id="
        . $reservation_id
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ASSIGN MECHANIC
|--------------------------------------------------------------------------
|
| Do NOT change mechanic status to Busy here.
| Busy means the mechanic is actively working on a vehicle.
|
*/

$update_stmt = mysqli_prepare(
    $conn,
    "UPDATE reservations

     SET mechanic_id = ?

     WHERE id = ?
     AND status = 'Approved'
     AND mechanic_id IS NULL"
);


mysqli_stmt_bind_param(
    $update_stmt,
    "ii",
    $mechanic_id,
    $reservation_id
);


mysqli_stmt_execute(
    $update_stmt
);


mysqli_stmt_close(
    $update_stmt
);


header(
    "Location: "
    . ADMIN_BASE_URL
    . "/reservation_view.php?id="
    . $reservation_id
);


exit;