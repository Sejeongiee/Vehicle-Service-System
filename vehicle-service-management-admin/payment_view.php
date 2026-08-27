<?php

include "includes/admin_auth.php";
include "includes/config.php";


$payment_id = intval(
    $_GET['id'] ?? 0
);


if ($payment_id <= 0) {

    header("Location: payments.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| HANDLE ACTION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action =
        $_POST['action'] ?? '';


    if ($action === 'paid') {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE payments
             SET
                status = 'Paid',
                paid_at = NOW()
             WHERE id = ?
             AND status = 'Pending'"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $payment_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

    }


    if ($action === 'cancel') {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE payments
             SET
                status = 'Cancelled',
                paid_at = NULL
             WHERE id = ?
             AND status = 'Pending'"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $payment_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

    }

}


/*
|--------------------------------------------------------------------------
| GET PAYMENT
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        p.*,

        r.service_type,
        r.status AS reservation_status,

        u.fullname AS customer_name,

        v.brand,
        v.model,
        v.plate_number

     FROM payments p

     INNER JOIN reservations r
        ON p.reservation_id = r.id

     INNER JOIN users u
        ON r.user_id = u.id

     INNER JOIN vehicles v
        ON r.vehicle_id = v.id

     WHERE p.id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $payment_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$payment =
    mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$payment) {

    header("Location: payments.php");
    exit;

}


include "includes/admin_header.php";

?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Payment #<?= $payment['id']; ?>
            </h2>

            <p class="text-muted mb-0">
                Payment information and status.
            </p>

        </div>

        <a
            href="payments.php"
            class="btn btn-secondary"
        >
            ← Payments
        </a>

    </div>


    <div class="row g-4">


        <div class="col-lg-6">

            <div class="dashboard-card">

                <h4 class="mb-4">
                    Payment Information
                </h4>

                <p>
                    <strong>Amount</strong><br>

                    ₱<?= number_format(
                        $payment['amount'],
                        2
                    ); ?>
                </p>

                <p>
                    <strong>Payment Method</strong><br>

                    <?= htmlspecialchars(
                        $payment[
                            'payment_method'
                        ]
                    ); ?>
                </p>

                <p>
                    <strong>Reference Number</strong><br>

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
                </p>

                <p>
                    <strong>Status</strong><br>

                    <?= htmlspecialchars(
                        $payment['status']
                    ); ?>
                </p>


                <?php if (
                    !empty(
                        $payment['paid_at']
                    )
                ): ?>

                    <p class="mb-0">

                        <strong>Paid At</strong><br>

                        <?= date(
                            'M d, Y h:i A',
                            strtotime(
                                $payment[
                                    'paid_at'
                                ]
                            )
                        ); ?>

                    </p>

                <?php endif; ?>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="dashboard-card">

                <h4 class="mb-4">
                    Reservation
                </h4>

                <p>
                    <strong>Customer</strong><br>

                    <?= htmlspecialchars(
                        $payment[
                            'customer_name'
                        ]
                    ); ?>
                </p>

                <p>
                    <strong>Vehicle</strong><br>

                    <?= htmlspecialchars(
                        $payment['brand']
                    ); ?>

                    <?= htmlspecialchars(
                        $payment['model']
                    ); ?>

                    <br>

                    <small class="text-muted">

                        <?= htmlspecialchars(
                            $payment[
                                'plate_number'
                            ]
                        ); ?>

                    </small>
                </p>

                <p class="mb-0">

                    <strong>Service</strong><br>

                    <?= htmlspecialchars(
                        $payment[
                            'service_type'
                        ]
                    ); ?>

                </p>

            </div>

        </div>


        <?php if (
            $payment['status']
            === 'Pending'
        ): ?>

            <div class="col-12">

                <div class="dashboard-card">

                    <h4 class="mb-4">
                        Payment Actions
                    </h4>


                    <div class="d-flex gap-2">


                        <form method="POST">

                            <input
                                type="hidden"
                                name="action"
                                value="paid"
                            >

                            <button
                                type="submit"
                                class="btn btn-success"
                                onclick="return confirm('Mark this payment as paid?');"
                            >
                                ✓ Mark as Paid
                            </button>

                        </form>


                        <form method="POST">

                            <input
                                type="hidden"
                                name="action"
                                value="cancel"
                            >

                            <button
                                type="submit"
                                class="btn btn-danger"
                                onclick="return confirm('Cancel this payment?');"
                            >
                                Cancel Payment
                            </button>

                        </form>


                    </div>

                </div>

            </div>

        <?php endif; ?>


    </div>

</div>

<?php include "includes/admin_footer.php"; ?>