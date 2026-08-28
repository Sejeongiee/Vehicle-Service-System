<?php

include "includes/admin_header.php";


/*
|--------------------------------------------------------------------------
| GET PAYMENTS
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        p.id,
        p.amount,
        p.payment_method,
        p.status AS payment_status,
        p.reference_number,
        p.paid_at,
        p.created_at,

        r.id AS reservation_id,
        r.service_type,
        r.status AS reservation_status,
        r.appointment_date,

        c.fullname AS customer_name,

        v.brand,
        v.model,
        v.plate_number

    FROM payments p

    INNER JOIN reservations r
        ON p.reservation_id = r.id

    LEFT JOIN customers c
        ON r.customer_id = c.id

    INNER JOIN vehicles v
        ON r.vehicle_id = v.id

    ORDER BY p.created_at DESC
";


$result = mysqli_query($conn, $query);


/*
|--------------------------------------------------------------------------
| TOTAL PAID
|--------------------------------------------------------------------------
*/

$paid_query = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM payments
     WHERE status = 'Paid'"
);

$paid_data = mysqli_fetch_assoc($paid_query);

$total_paid = $paid_data['total'];

?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Payments
            </h2>

            <p class="text-muted mb-0">
                Manage customer service payments.
            </p>

        </div>

        <div>

            <span class="badge text-bg-success fs-6">

                Total Paid:
                ₱<?= number_format($total_paid, 2); ?>

            </span>

        </div>

    </div>


    <div class="dashboard-card">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Customer</th>

                        <th>Vehicle</th>

                        <th>Service</th>

                        <th>Amount</th>

                        <th>Method</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (
                        $result &&
                        mysqli_num_rows($result) > 0
                    ): ?>

                    <?php while (
                            $payment =
                                mysqli_fetch_assoc($result)
                        ): ?>

                    <?php

                            $badge = 'secondary';

                            if (
                                $payment['payment_status']
                                === 'Paid'
                            ) {

                                $badge = 'success';

                            } elseif (
                                $payment['payment_status']
                                === 'Pending'
                            ) {

                                $badge = 'warning';

                            } elseif (
                                $payment['payment_status']
                                === 'Cancelled'
                            ) {

                                $badge = 'danger';

                            }

                            ?>

                    <tr>

                        <td>
                            #<?= $payment['id']; ?>
                        </td>

                        <td>

                            <?= htmlspecialchars(
                                        $payment['customer_name']
                                    ); ?>

                        </td>

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                            $payment['brand']
                                        ); ?>

                                <?= htmlspecialchars(
                                            $payment['model']
                                        ); ?>

                            </strong>

                            <br>

                            <small class="text-muted">

                                <?= htmlspecialchars(
                                            $payment['plate_number']
                                        ); ?>

                            </small>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                        $payment['service_type']
                                    ); ?>

                        </td>

                        <td>

                            ₱<?= number_format(
                                        $payment['amount'],
                                        2
                                    ); ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                        $payment['payment_method']
                                    ); ?>

                        </td>

                        <td>

                            <span class="badge text-bg-<?= $badge; ?>">

                                <?= htmlspecialchars(
                                            $payment['payment_status']
                                        ); ?>

                            </span>

                        </td>

                        <td>

                            <a href="payment_view.php?id=<?= $payment['id']; ?>" class="btn btn-sm btn-outline-primary">
                                View
                            </a>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                    <?php else: ?>

                    <tr>

                        <td colspan="8" class="text-center py-5">

                            <div class="fs-1 mb-3">
                                💳
                            </div>

                            <h5>
                                No Payments Yet
                            </h5>

                            <p class="text-muted mb-0">
                                Payment records will appear here.
                            </p>

                        </td>

                    </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include "includes/admin_footer.php"; ?>