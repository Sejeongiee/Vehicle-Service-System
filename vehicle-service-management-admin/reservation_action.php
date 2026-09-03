<?php

include_once __DIR__ . "/includes/config.php";
include_once __DIR__ . "/includes/admin_auth.php";


/*
|--------------------------------------------------------------------------
| GET REQUEST VALUES
|--------------------------------------------------------------------------
*/

$reservation_id = intval(
    $_POST['reservation_id'] ?? 0
);

$action = $_POST['action'] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDATE REQUEST
|--------------------------------------------------------------------------
*/

if ($reservation_id <= 0 || empty($action)) {

    header("Location: reservations.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET RESERVATION
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, status, mechanic_id
     FROM reservations
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $reservation_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$reservation = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$reservation) {

    header("Location: reservations.php");
    exit;

}


$current_status = $reservation['status'];
$mechanic_id = $reservation['mechanic_id'];


/*
|--------------------------------------------------------------------------
| APPROVE
|--------------------------------------------------------------------------
*/

if ($action === 'approve') {

    if ($current_status === 'Pending') {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE reservations
             SET status = 'Approved'
             WHERE id = ?
             AND status = 'Pending'"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $reservation_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }


    header(
        "Location: reservation_view.php?id="
        . $reservation_id
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CANCEL
|--------------------------------------------------------------------------
*/

if ($action === 'cancel') {

    if (
        $current_status === 'Pending' ||
        $current_status === 'Approved'
    ) {

        /*
        |--------------------------------------------------------------------------
        | If a mechanic was already assigned,
        | make that mechanic available again.
        |--------------------------------------------------------------------------
        */

        if (!empty($mechanic_id)) {

            $mechanic_stmt = mysqli_prepare(
                $conn,
                "UPDATE mechanics
                 SET status = 'Available'
                 WHERE id = ?
                 AND status = 'Busy'"
            );

            mysqli_stmt_bind_param(
                $mechanic_stmt,
                "i",
                $mechanic_id
            );

            mysqli_stmt_execute(
                $mechanic_stmt
            );

            mysqli_stmt_close(
                $mechanic_stmt
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Cancel reservation
        |--------------------------------------------------------------------------
        */

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE reservations
             SET status = 'Cancelled'
             WHERE id = ?
             AND status IN ('Pending', 'Approved')"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $reservation_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }


    header(
        "Location: reservation_view.php?id="
        . $reservation_id
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| START SERVICE
|--------------------------------------------------------------------------
*/

if ($action === 'start') {

    /*
    |--------------------------------------------------------------------------
    | A mechanic must be assigned first.
    |--------------------------------------------------------------------------
    */

    if (
        $current_status === 'Approved' &&
        !empty($mechanic_id)
    ) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE reservations
             SET status = 'In Progress'
             WHERE id = ?
             AND status = 'Approved'
             AND mechanic_id IS NOT NULL"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $reservation_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }


    header(
        "Location: reservation_view.php?id="
        . $reservation_id
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| COMPLETE SERVICE
|--------------------------------------------------------------------------
*/

if ($action === 'complete') {

    /*
    |--------------------------------------------------------------------------
    | Service must currently be In Progress.
    |--------------------------------------------------------------------------
    */

    if (
        $current_status === 'In Progress' &&
        !empty($mechanic_id)
    ) {

        mysqli_begin_transaction($conn);

        try {

            /*
            |--------------------------------------------------------------------------
            | Complete reservation
            |--------------------------------------------------------------------------
            */

            $reservation_stmt = mysqli_prepare(
                $conn,
                "UPDATE reservations
                 SET status = 'Completed'
                 WHERE id = ?
                 AND status = 'In Progress'"
            );

            mysqli_stmt_bind_param(
                $reservation_stmt,
                "i",
                $reservation_id
            );

            mysqli_stmt_execute(
                $reservation_stmt
            );


            if (
                mysqli_stmt_affected_rows(
                    $reservation_stmt
                ) !== 1
            ) {

                throw new Exception(
                    "Reservation could not be completed."
                );

            }


            mysqli_stmt_close(
                $reservation_stmt
            );


            /*
            |--------------------------------------------------------------------------
            | Make mechanic available again
            |--------------------------------------------------------------------------
            */

            $mechanic_stmt = mysqli_prepare(
                $conn,
                "UPDATE mechanics
                 SET status = 'Available'
                 WHERE id = ?
                 AND status = 'Busy'"
            );

            mysqli_stmt_bind_param(
                $mechanic_stmt,
                "i",
                $mechanic_id
            );

            mysqli_stmt_execute(
                $mechanic_stmt
            );


            if (
                mysqli_stmt_affected_rows(
                    $mechanic_stmt
                ) !== 1
            ) {

                throw new Exception(
                    "Mechanic status could not be updated."
                );

            }


            mysqli_stmt_close(
                $mechanic_stmt
            );


            /*
            |--------------------------------------------------------------------------
            | Everything succeeded
            |--------------------------------------------------------------------------
            */

            mysqli_commit($conn);

        } catch (Exception $e) {

            mysqli_rollback($conn);

        }
    }


    header(
        "Location: reservation_view.php?id="
        . $reservation_id
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| UNKNOWN ACTION
|--------------------------------------------------------------------------
*/

header(
    "Location: reservation_view.php?id="
    . $reservation_id
);

exit;