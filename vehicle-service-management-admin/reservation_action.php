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
| GET REQUEST
|--------------------------------------------------------------------------
*/

$reservation_id =
    intval($_POST['reservation_id'] ?? 0);

$action =
    trim($_POST['action'] ?? '');


$allowed_actions = [
    'approve',
    'cancel',
    'start',
    'complete'
];


if (
    $reservation_id <= 0 ||
    !in_array(
        $action,
        $allowed_actions,
        true
    )
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

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        status,
        mechanic_id

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


$result =
    mysqli_stmt_get_result($stmt);


$reservation =
    mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


if (!$reservation) {

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/reservations.php"
    );

    exit;
}


$current_status =
    $reservation['status'];

$mechanic_id =
    intval(
        $reservation['mechanic_id'] ?? 0
    );


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
        "Location: "
        . ADMIN_BASE_URL
        . "/reservation_view.php?id="
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

    /*
    | Only Pending and Approved reservations
    | may be cancelled.
    |
    | The mechanic is NOT changed here because
    | assignment no longer marks them Busy.
    */

    if (
        $current_status === 'Pending' ||
        $current_status === 'Approved'
    ) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE reservations

             SET status = 'Cancelled'

             WHERE id = ?
             AND status IN (
                'Pending',
                'Approved'
             )"
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
        "Location: "
        . ADMIN_BASE_URL
        . "/reservation_view.php?id="
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

    if (
        $current_status === 'Approved' &&
        $mechanic_id > 0
    ) {

        mysqli_begin_transaction($conn);


        try {

            /*
            |--------------------------------------------------------------------------
            | MARK MECHANIC BUSY
            |--------------------------------------------------------------------------
            */

            $mechanic_stmt =
                mysqli_prepare(
                    $conn,
                    "UPDATE mechanics

                     SET status = 'Busy'

                     WHERE id = ?
                     AND status = 'Available'"
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
                    "Mechanic is not available."
                );
            }


            mysqli_stmt_close(
                $mechanic_stmt
            );


            /*
            |--------------------------------------------------------------------------
            | START RESERVATION
            |--------------------------------------------------------------------------
            */

            $reservation_stmt =
                mysqli_prepare(
                    $conn,
                    "UPDATE reservations

                     SET status = 'In Progress'

                     WHERE id = ?
                     AND status = 'Approved'
                     AND mechanic_id = ?"
                );


            mysqli_stmt_bind_param(
                $reservation_stmt,
                "ii",
                $reservation_id,
                $mechanic_id
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
                    "Reservation could not be started."
                );
            }


            mysqli_stmt_close(
                $reservation_stmt
            );


            mysqli_commit($conn);

        } catch (Throwable $e) {

            mysqli_rollback($conn);

        }
    }


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
| COMPLETE SERVICE
|--------------------------------------------------------------------------
*/

if ($action === 'complete') {

    if (
        $current_status === 'In Progress' &&
        $mechanic_id > 0
    ) {

        mysqli_begin_transaction($conn);


        try {

            /*
            |--------------------------------------------------------------------------
            | COMPLETE RESERVATION
            |--------------------------------------------------------------------------
            */

            $reservation_stmt =
                mysqli_prepare(
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
            | RELEASE MECHANIC
            |--------------------------------------------------------------------------
            */

            $mechanic_stmt =
                mysqli_prepare(
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
                    "Mechanic could not be released."
                );
            }


            mysqli_stmt_close(
                $mechanic_stmt
            );


            mysqli_commit($conn);

        } catch (Throwable $e) {

            mysqli_rollback($conn);

        }
    }


    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/reservation_view.php?id="
        . $reservation_id
    );

    exit;
}