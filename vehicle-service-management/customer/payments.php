<?php

include "../includes/config.php";
include "../includes/customer_auth.php";
include "../includes/customer_header.php";

$user_id = $_SESSION['customer_id'];


/*
|--------------------------------------------------------------------------
| GET CUSTOMER PAYMENTS
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        p.id AS payment_id,
        p.amount,
        p.payment_method,
        p.status AS payment_status,
        p.reference_number,
        p.paid_at,

        r.id AS reservation_id,
        r.service_type,
        r.appointment_date,

        v.brand,
        v.model,
        v.plate_number

    FROM payments p

    INNER JOIN reservations r
        ON p.reservation_id = r.id

    INNER JOIN vehicles v
        ON r.vehicle_id = v.id

    WHERE r.user_id = ?

    ORDER BY p.created_at DESC
";


$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<div class="container-fluid">

    <div class="mb-4">

        <h2>My Payments</h2>

        <p class="text-muted">
            View payment records for your vehicle services.
        </p>

    </div>


    <div class="dashboard-card">

        <?php if (mysqli_num_rows($result) > 0): ?>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>
                            <th>Reservation</th>
                            <th>Vehicle</th>
                            <th>Service</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($payment = mysqli_fetch_assoc($result)): ?>

                            <?php

                            $badge = 'secondary';

                            if ($payment['payment_status'] === 'Paid') {
                                $badge = 'success';
                            } elseif ($payment['payment_status'] === 'Pending') {
                                $badge = 'warning';
                            } elseif ($payment['payment_status'] === 'Cancelled') {
                                $badge = 'danger';
                            }

                            ?>

                            <tr>

                                <td>
                                    #<?= $payment['reservation_id']; ?>
                                </td>

                                <td>

                                    <strong>
                                        <?= htmlspecialchars($payment['brand']); ?>
                                        <?= htmlspecialchars($payment['model']); ?>
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        <?= htmlspecialchars($payment['plate_number']); ?>
                                    </small>

                                </td>

                                <td>
                                    <?= htmlspecialchars($payment['service_type']); ?>
                                </td>

                                <td>
                                    ₱<?= number_format($payment['amount'], 2); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($payment['payment_method']); ?>
                                </td>

                                <td>

                                    <span class="badge text-bg-<?= $badge; ?>">
                                        <?= htmlspecialchars($payment['payment_status']); ?>
                                    </span>

                                </td>

                                <td>

                                    <?php if ($payment['payment_status'] === 'Paid'): ?>

                                        <a
                                            href="receipt.php?id=<?= $payment['payment_id']; ?>"
                                            class="btn btn-sm btn-outline-success"
                                        >
                                            View Receipt
                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="text-center py-5">

                <div class="fs-1 mb-3">
                    💳
                </div>

                <h4>No Payment Records</h4>

                <p class="text-muted mb-0">
                    Your payment records will appear here once created by staff.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php

mysqli_stmt_close($stmt);

include "../includes/customer_footer.php";

?>