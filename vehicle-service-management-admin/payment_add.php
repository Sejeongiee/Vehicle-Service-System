<?php

include "includes/admin_auth.php";
include "includes/config.php";


$reservation_id = intval(
    $_GET['reservation_id'] ?? 0
);


if ($reservation_id <= 0) {

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
    "SELECT
        r.id,
        r.status,
        r.service_type,

        u.fullname AS customer_name,

        v.brand,
        v.model,
        v.plate_number

     FROM reservations r

     INNER JOIN users u
        ON r.user_id = u.id

     INNER JOIN vehicles v
        ON r.vehicle_id = v.id

     WHERE r.id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $reservation_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$reservation =
    mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$reservation) {

    header("Location: reservations.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| STATUS CHECK
|--------------------------------------------------------------------------
*/

if (
    !in_array(
        $reservation['status'],
        [
            'Approved',
            'In Progress',
            'Completed'
        ],
        true
    )
) {

    header(
        "Location: reservation_view.php?id="
        . $reservation_id
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| CHECK EXISTING PAYMENT
|--------------------------------------------------------------------------
*/

$check_stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM payments
     WHERE reservation_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $check_stmt,
    "i",
    $reservation_id
);

mysqli_stmt_execute($check_stmt);

$check_result =
    mysqli_stmt_get_result($check_stmt);

$existing =
    mysqli_fetch_assoc($check_result);

mysqli_stmt_close($check_stmt);


if ($existing) {

    header(
        "Location: payment_view.php?id="
        . $existing['id']
    );

    exit;

}


$amount = "";
$payment_method = "Cash";
$reference_number = "";

$error = "";


/*
|--------------------------------------------------------------------------
| CREATE PAYMENT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $amount =
        trim($_POST['amount'] ?? '');

    $payment_method =
        $_POST['payment_method']
        ?? 'Cash';

    $reference_number =
        trim(
            $_POST['reference_number']
            ?? ''
        );


    if (
        $amount === '' ||
        !is_numeric($amount) ||
        $amount <= 0
    ) {

        $error =
            "Please enter a valid amount.";

    } elseif (
        !in_array(
            $payment_method,
            [
                'Cash',
                'GCash',
                'Bank Transfer',
                'Card'
            ],
            true
        )
    ) {

        $error =
            "Invalid payment method.";

    }


    if ($error === '') {

        $amount_value =
            floatval($amount);


        $insert_stmt = mysqli_prepare(
            $conn,
            "INSERT INTO payments
            (
                reservation_id,
                amount,
                payment_method,
                status,
                reference_number
            )
            VALUES (?, ?, ?, 'Pending', ?)"
        );


        mysqli_stmt_bind_param(
            $insert_stmt,
            "idss",
            $reservation_id,
            $amount_value,
            $payment_method,
            $reference_number
        );


        if (
            mysqli_stmt_execute(
                $insert_stmt
            )
        ) {

            $payment_id =
                mysqli_insert_id($conn);

            mysqli_stmt_close(
                $insert_stmt
            );

            header(
                "Location: payment_view.php?id="
                . $payment_id
            );

            exit;

        }


        $error =
            "Unable to create payment.";

        mysqli_stmt_close(
            $insert_stmt
        );

    }

}


include "includes/admin_header.php";

?>

<div class="container-fluid">

    <div class="mb-4">

        <h2>
            Create Payment
        </h2>

        <p class="text-muted">
            Create a payment record for this reservation.
        </p>

    </div>


    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <div class="row g-4">


        <div class="col-lg-4">

            <div class="dashboard-card">

                <h5 class="mb-3">
                    Reservation
                </h5>

                <p>
                    <strong>Customer:</strong><br>
                    <?= htmlspecialchars(
                        $reservation[
                            'customer_name'
                        ]
                    ); ?>
                </p>

                <p>
                    <strong>Vehicle:</strong><br>

                    <?= htmlspecialchars(
                        $reservation['brand']
                    ); ?>

                    <?= htmlspecialchars(
                        $reservation['model']
                    ); ?>

                    <br>

                    <small class="text-muted">
                        <?= htmlspecialchars(
                            $reservation[
                                'plate_number'
                            ]
                        ); ?>
                    </small>
                </p>

                <p class="mb-0">
                    <strong>Service:</strong><br>

                    <?= htmlspecialchars(
                        $reservation[
                            'service_type'
                        ]
                    ); ?>
                </p>

            </div>

        </div>


        <div class="col-lg-8">

            <div class="dashboard-card">

                <form method="POST">


                    <div class="mb-3">

                        <label class="form-label">
                            Amount
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                ₱
                            </span>

                            <input
                                type="number"
                                name="amount"
                                class="form-control"
                                min="0.01"
                                step="0.01"
                                value="<?= htmlspecialchars(
                                    $amount
                                ); ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Payment Method
                        </label>

                        <select
                            name="payment_method"
                            class="form-select"
                        >

                            <?php

                            $methods = [
                                'Cash',
                                'GCash',
                                'Bank Transfer',
                                'Card'
                            ];

                            ?>

                            <?php foreach (
                                $methods
                                as $method
                            ): ?>

                                <option
                                    value="<?= $method; ?>"
                                    <?= $payment_method
                                        === $method
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    <?= $method; ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="mb-4">

                        <label class="form-label">
                            Reference Number
                        </label>

                        <input
                            type="text"
                            name="reference_number"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $reference_number
                            ); ?>"
                            placeholder="Optional for cash payments"
                        >

                    </div>


                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Create Payment
                        </button>

                        <a
                            href="reservation_view.php?id=<?= $reservation_id; ?>"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>

                    </div>


                </form>

            </div>

        </div>


    </div>

</div>

<?php include "includes/admin_footer.php"; ?>