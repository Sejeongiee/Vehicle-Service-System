<?php

include "../includes/config.php";
include "../includes/customer_auth.php";

$user_id = $_SESSION['customer_id'];

$payment_id = intval($_GET['id'] ?? 0);


if ($payment_id <= 0) {

    header("Location: payments.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET RECEIPT
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        p.id AS payment_id,
        p.amount,
        p.payment_method,
        p.status AS payment_status,
        p.reference_number,
        p.paid_at,

        r.id AS reservation_id,
        r.service_type,
        r.appointment_date,
        r.appointment_time,

        u.fullname,
        u.email,

        v.brand,
        v.model,
        v.year,
        v.plate_number,
        v.color

     FROM payments p

     INNER JOIN reservations r
        ON p.reservation_id = r.id

     INNER JOIN users u
        ON r.user_id = u.id

     INNER JOIN vehicles v
        ON r.vehicle_id = v.id

     WHERE p.id = ?
       AND r.user_id = ?
       AND p.status = 'Paid'

     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $payment_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$receipt = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$receipt) {

    header("Location: payments.php");
    exit;

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Payment Receipt #<?= $receipt['payment_id']; ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f5f5;
        }

        .receipt-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
        }

        @media print {

            body {
                background: white;
            }

            .receipt-container {
                margin: 0;
                max-width: none;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }

        }

    </style>

</head>

<body>

<div class="receipt-container shadow">

    <div class="text-center mb-4">

        <h2>
            Vehicle Service Management
        </h2>

        <h5>
            Official Payment Receipt
        </h5>

    </div>


    <hr>


    <div class="row mb-4">

        <div class="col-md-6">

            <strong>
                Receipt No.
            </strong>

            <br>

            #<?= $receipt['payment_id']; ?>

        </div>


        <div class="col-md-6 text-md-end">

            <strong>
                Payment Date
            </strong>

            <br>

            <?= date(
                'F d, Y h:i A',
                strtotime($receipt['paid_at'])
            ); ?>

        </div>

    </div>


    <h5>Customer Information</h5>

    <p>
        <strong>Name:</strong>
        <?= htmlspecialchars($receipt['fullname']); ?>

        <br>

        <strong>Email:</strong>
        <?= htmlspecialchars($receipt['email']); ?>
    </p>


    <hr>


    <h5>Vehicle Information</h5>

    <p>

        <strong>Vehicle:</strong>

        <?= htmlspecialchars($receipt['brand']); ?>

        <?= htmlspecialchars($receipt['model']); ?>

        <br>

        <strong>Year:</strong>

        <?= htmlspecialchars($receipt['year']); ?>

        <br>

        <strong>Plate Number:</strong>

        <?= htmlspecialchars($receipt['plate_number']); ?>

        <br>

        <strong>Color:</strong>

        <?= htmlspecialchars($receipt['color'] ?? 'N/A'); ?>

    </p>


    <hr>


    <h5>Service Information</h5>

    <p>

        <strong>Reservation:</strong>
        #<?= $receipt['reservation_id']; ?>

        <br>

        <strong>Service:</strong>
        <?= htmlspecialchars($receipt['service_type']); ?>

        <br>

        <strong>Appointment:</strong>

        <?= date(
            'F d, Y',
            strtotime($receipt['appointment_date'])
        ); ?>

        <?= date(
            'h:i A',
            strtotime($receipt['appointment_time'])
        ); ?>

    </p>


    <hr>


    <h5>Payment Information</h5>


    <table class="table">

        <tr>

            <th>
                Amount Paid
            </th>

            <td class="text-end">

                <strong>
                    ₱<?= number_format($receipt['amount'], 2); ?>
                </strong>

            </td>

        </tr>


        <tr>

            <th>
                Payment Method
            </th>

            <td class="text-end">

                <?= htmlspecialchars(
                    $receipt['payment_method']
                ); ?>

            </td>

        </tr>


        <tr>

            <th>
                Reference Number
            </th>

            <td class="text-end">

                <?= !empty($receipt['reference_number'])
                    ? htmlspecialchars(
                        $receipt['reference_number']
                    )
                    : 'N/A'; ?>

            </td>

        </tr>


        <tr>

            <th>
                Status
            </th>

            <td class="text-end">

                <span class="badge text-bg-success">
                    Paid
                </span>

            </td>

        </tr>

    </table>


    <div class="text-center mt-5">

        <p class="text-muted">
            Thank you for choosing Vehicle Service Management.
        </p>

    </div>


    <div class="d-flex justify-content-center gap-2 no-print">

        <button
            onclick="window.print();"
            class="btn btn-primary"
        >
            🖨 Print Receipt
        </button>


        <a
            href="payments.php"
            class="btn btn-secondary"
        >
            Back
        </a>

    </div>

</div>

</body>

</html>