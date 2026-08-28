<?php

include "includes/config.php";


$reference =
    trim($_GET['ref'] ?? '');


if ($reference === '') {

    header("Location: appointment.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET APPOINTMENT
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        r.reference_number,
        r.service_type,
        r.appointment_date,
        r.appointment_time,
        r.status,

        c.fullname,
        c.email,
        c.phone,

        v.brand,
        v.model,
        v.year,
        v.plate_number

     FROM reservations r

     INNER JOIN customers c
        ON r.customer_id = c.id

     INNER JOIN vehicles v
        ON r.vehicle_id = v.id

     WHERE r.reference_number = ?

     LIMIT 1"
);


mysqli_stmt_bind_param(
    $stmt,
    "s",
    $reference
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);

$appointment =
    mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$appointment) {

    header("Location: appointment.php");
    exit;

}

include "includes/public_header.php";

?>


<section class="appointment-success-page">

    <div class="container">

        <div class="appointment-success-card">


            <div class="success-icon">
                ✓
            </div>


            <span class="section-label">
                APPOINTMENT RECEIVED
            </span>


            <h1>
                Your appointment has been submitted.
            </h1>


            <p class="success-intro">

                Please save your appointment reference number.
                You will use it to check your service status.

            </p>



            <div class="reference-box">

                <span>
                    APPOINTMENT REFERENCE
                </span>

                <strong>
                    <?= htmlspecialchars(
                        $appointment[
                            'reference_number'
                        ]
                    ); ?>
                </strong>

            </div>



            <div class="appointment-summary">


                <div>

                    <span>
                        Customer
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $appointment[
                                'fullname'
                            ]
                        ); ?>

                    </strong>

                </div>


                <div>

                    <span>
                        Vehicle
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $appointment['brand']
                        ); ?>

                        <?= htmlspecialchars(
                            $appointment['model']
                        ); ?>

                    </strong>

                </div>


                <div>

                    <span>
                        Plate Number
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $appointment[
                                'plate_number'
                            ]
                        ); ?>

                    </strong>

                </div>


                <div>

                    <span>
                        Service
                    </span>

                    <strong>

                        <?= htmlspecialchars(
                            $appointment[
                                'service_type'
                            ]
                        ); ?>

                    </strong>

                </div>


                <div>

                    <span>
                        Date
                    </span>

                    <strong>

                        <?= date(
                            'F d, Y',
                            strtotime(
                                $appointment[
                                    'appointment_date'
                                ]
                            )
                        ); ?>

                    </strong>

                </div>


                <div>

                    <span>
                        Time
                    </span>

                    <strong>

                        <?= date(
                            'h:i A',
                            strtotime(
                                $appointment[
                                    'appointment_time'
                                ]
                            )
                        ); ?>

                    </strong>

                </div>


                <div>

                    <span>
                        Status
                    </span>

                    <strong class="status-pending">

                        <?= htmlspecialchars(
                            $appointment[
                                'status'
                            ]
                        ); ?>

                    </strong>

                </div>


            </div>



            <div class="success-actions">


                <a href="track.php?ref=<?= urlencode(
                        $appointment[
                            'reference_number'
                        ]
                    ); ?>" class="success-primary">

                    TRACK APPOINTMENT

                </a>


                <a href="appointment.php" class="success-secondary">

                    BOOK ANOTHER VEHICLE

                </a>


            </div>


        </div>

    </div>

</section>


<?php

include "includes/public_footer.php";

?>