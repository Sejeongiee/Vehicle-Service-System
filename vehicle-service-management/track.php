<?php

include "includes/config.php";


$reference =
    strtoupper(
        trim($_GET['ref'] ?? '')
    );

$contact =
    trim($_GET['contact'] ?? '');


$error = "";
$appointment = null;


/*
|--------------------------------------------------------------------------
| PROCESS FIRST
|--------------------------------------------------------------------------
*/

$contact = trim($_GET['contact'] ?? '');

$error = "";
$appointment = null;


/*
|--------------------------------------------------------------------------
| HANDLE TRACKING
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reference =
        strtoupper(
            trim($_POST['reference'] ?? '')
        );

    $contact =
    trim($_POST['contact'] ?? '');


    if (filter_var(
        $contact,
        FILTER_VALIDATE_EMAIL
    )) {

        $contact =
            strtolower($contact);

}


    if ($reference === '' || $contact === '') {

        $error =
            "Please enter your appointment reference and email or phone number.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | FIND APPOINTMENT
        |--------------------------------------------------------------------------
        */

        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                r.id,
                r.reference_number,
                r.service_type,
                r.appointment_date,
                r.appointment_time,
                r.remarks,
                r.status,
                r.mechanic_id,

                c.fullname,
                c.email,
                c.phone,

                v.brand,
                v.model,
                v.year,
                v.plate_number,
                v.color,

                m.fullname AS mechanic_name,
                m.specialization AS mechanic_specialization,

                p.id AS payment_id,
                p.amount AS payment_amount,
                p.payment_method,
                p.status AS payment_status,
                p.reference_number AS payment_reference,
                p.paid_at

             FROM reservations r

             INNER JOIN customers c
                ON r.customer_id = c.id

             INNER JOIN vehicles v
                ON r.vehicle_id = v.id

             LEFT JOIN mechanics m
                ON r.mechanic_id = m.id

             LEFT JOIN payments p
                ON r.id = p.reservation_id

             WHERE r.reference_number = ?

             AND (
                c.email = ?
                OR c.phone = ?
             )

             LIMIT 1"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $reference,
            $contact,
            $contact
        );


        mysqli_stmt_execute($stmt);

        $result =
            mysqli_stmt_get_result($stmt);

        $appointment =
            mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$appointment) {

            $error =
                "We could not find an appointment matching those details.";

        }

    }

}


/*
|--------------------------------------------------------------------------
| AUTO-FILL FROM SUCCESS PAGE
|--------------------------------------------------------------------------
|
| If appointment_success.php sends ?ref=...
| we'll show it in the form automatically.
|
*/

?>

<?php

include "includes/public_header.php";

?>

<section class="track-page">

    <div class="container">

        <div class="track-heading">

            <span class="section-label">
                APPOINTMENT STATUS
            </span>

            <h1>
                Track Your Appointment
            </h1>

            <p>
                Enter your appointment reference number and
                the email address or phone number used when booking.
            </p>

        </div>


        <div class="track-search-card">

            <?php if ($error !== ''): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>

            <?php endif; ?>


            <form method="POST">

                <div class="row g-3">


                    <div class="col-lg-5">

                        <label class="form-label">
                            Appointment Reference
                        </label>

                        <input type="text" name="reference" class="form-control" placeholder="VSMS-20260827-A12F7B"
                            value="<?= htmlspecialchars($reference); ?>" required>

                    </div>


                    <div class="col-lg-5">

                        <label class="form-label">
                            Email or Phone Number
                        </label>

                        <input type="text" name="contact" class="form-control"
                            placeholder="juan@example.com or 09123456789" value="<?= htmlspecialchars($contact); ?>"
                            required>

                    </div>


                    <div class="col-lg-2 d-flex align-items-end">

                        <button type="submit" class="track-button w-100">
                            TRACK
                        </button>

                    </div>


                </div>

            </form>

        </div>



        <?php if ($appointment): ?>


        <?php

            /*
            |--------------------------------------------------------------------------
            | RESERVATION STATUS STYLE
            |--------------------------------------------------------------------------
            */

            $status_class = 'secondary';

            if ($appointment['status'] === 'Pending') {

                $status_class = 'warning';

            } elseif ($appointment['status'] === 'Approved') {

                $status_class = 'primary';

            } elseif ($appointment['status'] === 'In Progress') {

                $status_class = 'info';

            } elseif ($appointment['status'] === 'Completed') {

                $status_class = 'success';

            } elseif ($appointment['status'] === 'Cancelled') {

                $status_class = 'danger';

            }


            /*
            |--------------------------------------------------------------------------
            | PAYMENT STATUS STYLE
            |--------------------------------------------------------------------------
            */

            $payment_class = 'secondary';

            if ($appointment['payment_status'] === 'Paid') {

                $payment_class = 'success';

            } elseif ($appointment['payment_status'] === 'Pending') {

                $payment_class = 'warning';

            } elseif ($appointment['payment_status'] === 'Cancelled') {

                $payment_class = 'danger';

            }

            ?>


        <div class="track-result">


            <!-- TOP -->

            <div class="track-result-header">

                <div>

                    <span class="track-small-label">
                        APPOINTMENT REFERENCE
                    </span>

                    <h2>
                        <?= htmlspecialchars(
                                $appointment[
                                    'reference_number'
                                ]
                            ); ?>
                    </h2>

                </div>


                <span class="badge text-bg-<?= $status_class; ?> fs-6">
                    <?= htmlspecialchars(
                            $appointment['status']
                        ); ?>
                </span>

            </div>



            <!-- STATUS FLOW -->

            <div class="appointment-status-flow">

                <?php

                    $statuses = [
                        'Pending',
                        'Approved',
                        'In Progress',
                        'Completed'
                    ];

                    $current_index =
                        array_search(
                            $appointment['status'],
                            $statuses,
                            true
                        );

                    ?>


                <?php if (
                        $appointment['status'] === 'Cancelled'
                    ): ?>

                <div class="cancelled-status">

                    <strong>
                        Appointment Cancelled
                    </strong>

                    <p>
                        This appointment is no longer active.
                    </p>

                </div>


                <?php else: ?>


                <?php foreach (
                            $statuses as
                            $index => $flow_status
                        ): ?>

                <div class="status-step
                                <?= $current_index !== false
                                    && $index <= $current_index
                                    ? 'active'
                                    : ''; ?>">

                    <div class="status-dot">
                        <?= $index + 1; ?>
                    </div>

                    <span>
                        <?= $flow_status; ?>
                    </span>

                </div>

                <?php endforeach; ?>


                <?php endif; ?>

            </div>



            <!-- DETAILS -->

            <div class="row g-4">


                <div class="col-lg-6">

                    <div class="track-detail-card">

                        <h4>
                            Appointment Details
                        </h4>


                        <div class="track-detail-row">

                            <span>
                                Customer
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                        $appointment['fullname']
                                    ); ?>
                            </strong>

                        </div>


                        <div class="track-detail-row">

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


                        <div class="track-detail-row">

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


                        <div class="track-detail-row">

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


                        <div class="track-detail-row">

                            <span>
                                Remarks
                            </span>

                            <strong>

                                <?= !empty(
                                        $appointment['remarks']
                                    )
                                        ? htmlspecialchars(
                                            $appointment[
                                                'remarks'
                                            ]
                                        )
                                        : '—'; ?>

                            </strong>

                        </div>

                    </div>

                </div>



                <div class="col-lg-6">

                    <div class="track-detail-card">

                        <h4>
                            Vehicle
                        </h4>


                        <div class="track-vehicle-name">

                            <?= htmlspecialchars(
                                    $appointment['brand']
                                ); ?>

                            <?= htmlspecialchars(
                                    $appointment['model']
                                ); ?>

                        </div>


                        <div class="track-detail-row">

                            <span>
                                Year
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                        $appointment['year']
                                    ); ?>
                            </strong>

                        </div>


                        <div class="track-detail-row">

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


                        <div class="track-detail-row">

                            <span>
                                Color
                            </span>

                            <strong>

                                <?= !empty(
                                        $appointment['color']
                                    )
                                        ? htmlspecialchars(
                                            $appointment[
                                                'color'
                                            ]
                                        )
                                        : '—'; ?>

                            </strong>

                        </div>

                    </div>

                </div>



                <!-- MECHANIC -->

                <div class="col-lg-6">

                    <div class="track-detail-card">

                        <h4>
                            Assigned Mechanic
                        </h4>


                        <?php if (
                                !empty(
                                    $appointment[
                                        'mechanic_name'
                                    ]
                                )
                            ): ?>

                        <div class="track-mechanic">

                            <div class="mechanic-symbol">
                                🔧
                            </div>

                            <div>

                                <strong>

                                    <?= htmlspecialchars(
                                                $appointment[
                                                    'mechanic_name'
                                                ]
                                            ); ?>

                                </strong>


                                <?php if (
                                            !empty(
                                                $appointment[
                                                    'mechanic_specialization'
                                                ]
                                            )
                                        ): ?>

                                <span>

                                    <?= htmlspecialchars(
                                                    $appointment[
                                                        'mechanic_specialization'
                                                    ]
                                                ); ?>

                                </span>

                                <?php endif; ?>

                            </div>

                        </div>


                        <?php else: ?>

                        <p class="text-muted mb-0">

                            A mechanic has not been assigned yet.

                        </p>

                        <?php endif; ?>

                    </div>

                </div>



                <!-- PAYMENT -->

                <div class="col-lg-6">

                    <div class="track-detail-card">

                        <h4>
                            Payment
                        </h4>


                        <?php if (
                                !empty(
                                    $appointment[
                                        'payment_id'
                                    ]
                                )
                            ): ?>


                        <div class="track-detail-row">

                            <span>
                                Amount
                            </span>

                            <strong>

                                ₱<?= number_format(
                                            $appointment[
                                                'payment_amount'
                                            ],
                                            2
                                        ); ?>

                            </strong>

                        </div>


                        <div class="track-detail-row">

                            <span>
                                Method
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                            $appointment[
                                                'payment_method'
                                            ]
                                        ); ?>

                            </strong>

                        </div>


                        <div class="track-detail-row">

                            <span>
                                Status
                            </span>

                            <span class="badge text-bg-<?= $payment_class; ?>">

                                <?= htmlspecialchars(
                                            $appointment[
                                                'payment_status'
                                            ]
                                        ); ?>

                            </span>

                        </div>


                        <?php else: ?>

                        <span class="badge text-bg-secondary">
                            No Payment Record
                        </span>

                        <p class="text-muted mt-3 mb-0">

                            Payment information will appear
                            once staff creates the payment record.

                        </p>

                        <?php endif; ?>

                    </div>

                </div>


            </div>



            <div class="track-actions">

                <a href="<?= BASE_URL ?>/appointment.php" class="track-secondary-button">
                    BOOK ANOTHER APPOINTMENT
                </a>

            </div>


        </div>


        <?php endif; ?>


    </div>

</section>


<?php

include "includes/public_footer.php";

?>