<?php

include "includes/admin_header.php";

/*
|--------------------------------------------------------------------------
| CHECK PAYMENT
|--------------------------------------------------------------------------
*/

$payment_stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        amount,
        payment_method,
        status,
        reference_number,
        paid_at
     FROM payments
     WHERE reservation_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $payment_stmt,
    "i",
    $reservation_id
);

mysqli_stmt_execute($payment_stmt);

$payment_result =
    mysqli_stmt_get_result($payment_stmt);

$payment =
    mysqli_fetch_assoc($payment_result);

mysqli_stmt_close($payment_stmt);


/*
|--------------------------------------------------------------------------
| GET RESERVATION ID
|--------------------------------------------------------------------------
*/

$reservation_id = intval(
    $_GET['id'] ?? 0
);


if ($reservation_id <= 0) {

    header("Location: reservations.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET RESERVATION DETAILS
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        r.id,
        r.service_type,
        r.appointment_date,
        r.appointment_time,
        r.remarks,
        r.status,
        r.mechanic_id,
        r.created_at,

        u.fullname AS customer_name,
        u.email AS customer_email,

        v.brand,
        v.model,
        v.year,
        v.plate_number,
        v.color

    FROM reservations r

    INNER JOIN users u
        ON r.user_id = u.id

    INNER JOIN vehicles v
        ON r.vehicle_id = v.id

    WHERE r.id = ?

    LIMIT 1
";


$stmt = mysqli_prepare(
    $conn,
    $query
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


/*
|--------------------------------------------------------------------------
| RESERVATION NOT FOUND
|--------------------------------------------------------------------------
*/

if (!$reservation) {

    header("Location: reservations.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| STATUS BADGE
|--------------------------------------------------------------------------
*/

$status = $reservation['status'];

$status_class = 'secondary';

if ($status === 'Pending') {

    $status_class = 'warning';

} elseif ($status === 'Approved') {

    $status_class = 'primary';

} elseif ($status === 'In Progress') {

    $status_class = 'info';

} elseif ($status === 'Completed') {

    $status_class = 'success';

} elseif ($status === 'Cancelled') {

    $status_class = 'danger';

}

?>


<div class="container-fluid">


    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Reservation #<?= $reservation['id']; ?>
            </h2>

            <p class="text-muted mb-0">
                Reservation details and service information.
            </p>

        </div>


        <a
            href="reservations.php"
            class="btn btn-secondary"
        >

            ← Back to Reservations

        </a>

    </div>



    <div class="row g-4">


        <!-- CUSTOMER -->

        <div class="col-lg-6">

            <div class="dashboard-card">

                <h4 class="mb-4">
                    Customer Information
                </h4>


                <div class="mb-3">

                    <label class="text-muted">
                        Customer
                    </label>

                    <h5>
                        <?= htmlspecialchars(
                            $reservation['customer_name']
                        ); ?>
                    </h5>

                </div>


                <div class="mb-3">

                    <label class="text-muted">
                        Email
                    </label>

                    <div>

                        <?= htmlspecialchars(
                            $reservation['customer_email']
                        ); ?>

                    </div>

                </div>

            </div>

        </div>



        <!-- VEHICLE -->

        <div class="col-lg-6">

            <div class="dashboard-card">

                <h4 class="mb-4">
                    Vehicle Information
                </h4>


                <div class="mb-3">

                    <label class="text-muted">
                        Vehicle
                    </label>

                    <h5>

                        <?= htmlspecialchars(
                            $reservation['brand']
                        ); ?>

                        <?= htmlspecialchars(
                            $reservation['model']
                        ); ?>

                    </h5>

                </div>


                <div class="row">


                    <div class="col-md-6 mb-3">

                        <label class="text-muted">
                            Plate Number
                        </label>

                        <div>

                            <?= htmlspecialchars(
                                $reservation[
                                    'plate_number'
                                ]
                            ); ?>

                        </div>

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="text-muted">
                            Year
                        </label>

                        <div>

                            <?= htmlspecialchars(
                                $reservation['year']
                            ); ?>

                        </div>

                    </div>


                </div>

            </div>

        </div>



        <!-- SERVICE INFORMATION -->

        <div class="col-lg-8">

            <div class="dashboard-card">

                <h4 class="mb-4">
                    Service Information
                </h4>


                <div class="row">


                    <!-- SERVICE -->

                    <div class="col-md-6 mb-4">

                        <label class="text-muted">
                            Service Type
                        </label>

                        <h5>

                            <?= htmlspecialchars(
                                $reservation[
                                    'service_type'
                                ]
                            ); ?>

                        </h5>

                    </div>


                    <!-- STATUS -->

                    <div class="col-md-6 mb-4">

                        <label class="text-muted">
                            Status
                        </label>

                        <div>

                            <span
                                class="badge text-bg-<?= $status_class; ?> fs-6"
                            >

                                <?= htmlspecialchars(
                                    $status
                                ); ?>

                            </span>

                        </div>

                    </div>


                    <!-- DATE -->

                    <div class="col-md-6 mb-4">

                        <label class="text-muted">
                            Appointment Date
                        </label>

                        <div>

                            <?= date(
                                'F d, Y',
                                strtotime(
                                    $reservation[
                                        'appointment_date'
                                    ]
                                )
                            ); ?>

                        </div>

                    </div>


                    <!-- TIME -->

                    <div class="col-md-6 mb-4">

                        <label class="text-muted">
                            Appointment Time
                        </label>

                        <div>

                            <?= date(
                                'h:i A',
                                strtotime(
                                    $reservation[
                                        'appointment_time'
                                    ]
                                )
                            ); ?>

                        </div>

                    </div>


                    <!-- REMARKS -->

                    <div class="col-12">

                        <label class="text-muted">
                            Customer Remarks
                        </label>

                        <div class="border rounded p-3 bg-light">

                            <?php if (
                                !empty(
                                    $reservation['remarks']
                                )
                            ): ?>

                                <?= nl2br(
                                    htmlspecialchars(
                                        $reservation[
                                            'remarks'
                                        ]
                                    )
                                ); ?>

                            <?php else: ?>

                                <span class="text-muted">
                                    No remarks provided.
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>


                </div>

            </div>

        </div>

<!-- MECHANIC -->

<div class="col-lg-4">

    <div class="dashboard-card">

        <h4 class="mb-4">
            Mechanic
        </h4>


        <?php if (!empty($reservation['mechanic_id'])): ?>

            <?php

            $mechanic_stmt = mysqli_prepare(
                $conn,
                "SELECT fullname, specialization, phone, status
                 FROM mechanics
                 WHERE id = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $mechanic_stmt,
                "i",
                $reservation['mechanic_id']
            );

            mysqli_stmt_execute($mechanic_stmt);

            $mechanic_result =
                mysqli_stmt_get_result($mechanic_stmt);

            $mechanic =
                mysqli_fetch_assoc($mechanic_result);

            mysqli_stmt_close($mechanic_stmt);

            ?>


            <?php if ($mechanic): ?>

                <div class="mb-3">

                    <label class="text-muted">
                        Assigned Mechanic
                    </label>

                    <h5>
                        <?= htmlspecialchars(
                            $mechanic['fullname']
                        ); ?>
                    </h5>

                </div>


                <?php if (
                    !empty(
                        $mechanic['specialization']
                    )
                ): ?>

                    <div class="mb-3">

                        <label class="text-muted">
                            Specialization
                        </label>

                        <div>

                            <?= htmlspecialchars(
                                $mechanic['specialization']
                            ); ?>

                        </div>

                    </div>

                <?php endif; ?>


                <?php if (
                    !empty(
                        $mechanic['phone']
                    )
                ): ?>

                    <div class="mb-3">

                        <label class="text-muted">
                            Phone
                        </label>

                        <div>

                            <?= htmlspecialchars(
                                $mechanic['phone']
                            ); ?>

                        </div>

                    </div>

                <?php endif; ?>


                <span class="badge text-bg-success">

                    Assigned

                </span>


            <?php else: ?>

                <div class="alert alert-warning">

                    The assigned mechanic could not be found.

                </div>

            <?php endif; ?>


        <?php else: ?>


            <div class="text-center py-3">

                <div class="fs-1 mb-3">
                    🔧
                </div>

                <p class="text-muted">
                    No mechanic assigned yet.
                </p>

            </div>


            <?php if ($status === 'Approved'): ?>

                <form
                    method="POST"
                    action="assign_mechanic.php"
                >

                    <input
                        type="hidden"
                        name="reservation_id"
                        value="<?= $reservation['id']; ?>"
                    >


                    <div class="mb-3">

                        <label class="form-label">
                            Select Mechanic
                        </label>


                        <select
                            name="mechanic_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                -- Select Mechanic --
                            </option>


                            <?php

                            $mechanics_query = "
                                SELECT
                                    id,
                                    fullname,
                                    specialization
                                FROM mechanics
                                WHERE status = 'Available'
                                ORDER BY fullname ASC
                            ";

                            $mechanics_result =
                                mysqli_query(
                                    $conn,
                                    $mechanics_query
                                );

                            ?>


                            <?php if (
                                $mechanics_result &&
                                mysqli_num_rows(
                                    $mechanics_result
                                ) > 0
                            ): ?>


                                <?php while (
                                    $available_mechanic =
                                        mysqli_fetch_assoc(
                                            $mechanics_result
                                        )
                                ): ?>

                                    <option
                                        value="<?= $available_mechanic['id']; ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $available_mechanic[
                                                'fullname'
                                            ]
                                        ); ?>

                                        <?php if (
                                            !empty(
                                                $available_mechanic[
                                                    'specialization'
                                                ]
                                            )
                                        ): ?>

                                            -
                                            <?= htmlspecialchars(
                                                $available_mechanic[
                                                    'specialization'
                                                ]
                                            ); ?>

                                        <?php endif; ?>

                                    </option>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <option value="" disabled>

                                    No available mechanics

                                </option>

                            <?php endif; ?>


                        </select>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >

                        🔧 Assign Mechanic

                    </button>

                </form>

            <?php else: ?>

                <div class="alert alert-secondary mb-0">

                    Mechanic assignment will be available
                    after the reservation is approved.

                </div>

            <?php endif; ?>


        <?php endif; ?>


    </div>

</div>

<!-- PAYMENT -->

<div class="col-12">

    <div class="dashboard-card">

        <h4 class="mb-4">
            Payment
        </h4>


        <?php if ($payment): ?>

            <div class="row">

                <div class="col-md-3 mb-3">

                    <label class="text-muted">
                        Amount
                    </label>

                    <h5>
                        ₱<?= number_format(
                            $payment['amount'],
                            2
                        ); ?>
                    </h5>

                </div>


                <div class="col-md-3 mb-3">

                    <label class="text-muted">
                        Method
                    </label>

                    <div>

                        <?= htmlspecialchars(
                            $payment[
                                'payment_method'
                            ]
                        ); ?>

                    </div>

                </div>


                <div class="col-md-3 mb-3">

                    <label class="text-muted">
                        Status
                    </label>

                    <div>

                        <?php

                        $payment_badge =
                            $payment['status']
                            === 'Paid'
                            ? 'success'
                            : (
                                $payment['status']
                                === 'Pending'
                                ? 'warning'
                                : 'danger'
                            );

                        ?>

                        <span
                            class="badge text-bg-<?= $payment_badge; ?>"
                        >

                            <?= htmlspecialchars(
                                $payment['status']
                            ); ?>

                        </span>

                    </div>

                </div>


                <div class="col-md-3 mb-3">

                    <label class="text-muted">
                        Reference
                    </label>

                    <div>

                        <?= !empty(
                            $payment[
                                'reference_number'
                            ]
                        )
                            ? htmlspecialchars(
                                $payment[
                                    'reference_number'
                                ]
                            )
                            : '—'; ?>

                    </div>

                </div>

            </div>


            <a
                href="payment_view.php?id=<?= $payment['id']; ?>"
                class="btn btn-outline-primary"
            >

                Manage Payment

            </a>


        <?php elseif (
            in_array(
                $status,
                [
                    'Approved',
                    'In Progress',
                    'Completed'
                ],
                true
            )
        ): ?>

            <p class="text-muted">
                No payment record has been created
                for this reservation.
            </p>

            <a
                href="payment_add.php?reservation_id=<?= $reservation['id']; ?>"
                class="btn btn-primary"
            >

                + Create Payment

            </a>


        <?php else: ?>

            <div class="alert alert-secondary mb-0">

                Payment can be created after the
                reservation is approved.

            </div>

        <?php endif; ?>

    </div>

</div>

        <!-- RESERVATION ACTIONS -->

        <div class="col-12">

            <div class="dashboard-card">

                <h4 class="mb-4">
                    Reservation Actions
                </h4>


                <?php if ($status === 'Pending'): ?>

                    <div class="d-flex gap-2 flex-wrap">


                        <!-- APPROVE -->

                        <form
                            method="POST"
                            action="reservation_action.php"
                        >

                            <input
                                type="hidden"
                                name="reservation_id"
                                value="<?= $reservation['id']; ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="approve"
                            >

                            <button
                                type="submit"
                                class="btn btn-success"
                                onclick="return confirm('Approve this reservation?');"
                            >

                                ✓ Approve Reservation

                            </button>

                        </form>


                        <!-- CANCEL -->

                        <form
                            method="POST"
                            action="reservation_action.php"
                        >

                            <input
                                type="hidden"
                                name="reservation_id"
                                value="<?= $reservation['id']; ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="cancel"
                            >

                            <button
                                type="submit"
                                class="btn btn-danger"
                                onclick="return confirm('Cancel this reservation?');"
                            >

                                ✕ Reject Reservation

                            </button>

                        </form>

                    </div>


                <?php elseif ($status === 'Approved'): ?>

    <?php if (!empty($reservation['mechanic_id'])): ?>

        <div class="alert alert-primary">

            This reservation has been approved and
            a mechanic has been assigned.

        </div>


        <form
            method="POST"
            action="reservation_action.php"
        >

            <input
                type="hidden"
                name="reservation_id"
                value="<?= $reservation['id']; ?>"
            >

            <input
                type="hidden"
                name="action"
                value="start"
            >

            <button
                type="submit"
                class="btn btn-info"
                onclick="return confirm('Start this service?');"
            >

                🔧 Start Service

            </button>

        </form>

    <?php else: ?>

        <div class="alert alert-warning">

            This reservation is approved, but no mechanic
            has been assigned yet.

        </div>

    <?php endif; ?>


                <?php elseif ($status === 'In Progress'): ?>

    <div class="alert alert-info">

        This service is currently in progress.

    </div>


    <form
        method="POST"
        action="reservation_action.php"
    >

        <input
            type="hidden"
            name="reservation_id"
            value="<?= $reservation['id']; ?>"
        >

        <input
            type="hidden"
            name="action"
            value="complete"
        >

        <button
            type="submit"
            class="btn btn-success"
            onclick="return confirm('Mark this service as completed?');"
        >

            ✓ Complete Service

        </button>

    </form>


                <?php elseif ($status === 'Completed'): ?>

                    <div class="alert alert-success">

                        This service has been completed.

                    </div>


                <?php elseif ($status === 'Cancelled'): ?>

                    <div class="alert alert-danger">

                        This reservation has been cancelled.

                    </div>

                <?php endif; ?>

            </div>

        </div>


    </div>


</div>


<?php

include "includes/admin_footer.php";

?>