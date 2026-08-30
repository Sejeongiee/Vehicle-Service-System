<?php

include "includes/admin_header.php";


/*
|--------------------------------------------------------------------------
| GET ALL RESERVATIONS
|--------------------------------------------------------------------------
*/

$reservations = mysqli_query(
    $conn,
    "SELECT
        r.id,
        r.reference_number,
        r.service_type,
        r.appointment_date,
        r.appointment_time,
        r.status,
        r.created_at,

        c.fullname AS customer_name,
        c.email AS customer_email,
        c.phone AS customer_phone,

        v.brand,
        v.model,
        v.plate_number,

        m.fullname AS mechanic_name

     FROM reservations r

     LEFT JOIN customers c
        ON r.customer_id = c.id

     LEFT JOIN vehicles v
        ON r.vehicle_id = v.id

     LEFT JOIN mechanics m
        ON r.mechanic_id = m.id

     ORDER BY
        r.appointment_date DESC,
        r.appointment_time DESC"
);


$result = mysqli_query($conn, $query);

?>

<div class="container-fluid">

    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Reservations
            </h2>

            <p class="text-muted mb-0">
                Manage customer vehicle service appointments.
            </p>

        </div>

        <div>

            <span class="badge text-bg-warning fs-6">

                <?php

                $pending_query = "
                    SELECT COUNT(*) AS total
                    FROM reservations
                    WHERE status = 'Pending'
                ";

                $pending_result =
                    mysqli_query($conn, $pending_query);

                $pending = mysqli_fetch_assoc(
                    $pending_result
                );

                ?>

                <?= $pending['total']; ?> Pending

            </span>

        </div>

    </div>


    <!-- RESERVATIONS TABLE -->

    <div class="dashboard-card">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Service</th>
                        <th>Schedule</th>
                        <th>Mechanic</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if (
            $reservations &&
            mysqli_num_rows($reservations) > 0
        ): ?>

                    <?php while (
                $reservation =
                    mysqli_fetch_assoc($reservations)
            ): ?>

                    <?php

                $status_class = 'secondary';

                switch ($reservation['status']) {

                    case 'Pending':
                        $status_class = 'warning';
                        break;

                    case 'Approved':
                        $status_class = 'primary';
                        break;

                    case 'In Progress':
                        $status_class = 'info';
                        break;

                    case 'Completed':
                        $status_class = 'success';
                        break;

                    case 'Cancelled':
                    case 'Rejected':
                        $status_class = 'danger';
                        break;

                }

                ?>

                    <tr>

                        <td>

                            <?php if (
                            !empty(
                                $reservation[
                                    'reference_number'
                                ]
                            )
                        ): ?>

                            <strong>
                                <?= htmlspecialchars(
                                    $reservation[
                                        'reference_number'
                                    ]
                                ); ?>
                            </strong>

                            <?php else: ?>

                            <span class="text-muted">
                                Legacy #<?= $reservation['id']; ?>
                            </span>

                            <?php endif; ?>

                        </td>


                        <td>

                            <strong>
                                <?= htmlspecialchars(
                                $reservation[
                                    'customer_name'
                                ] ?? 'Unknown'
                            ); ?>
                            </strong>

                            <br>

                            <small class="text-muted">

                                <?= htmlspecialchars(
                                $reservation[
                                    'customer_email'
                                ] ?? ''
                            ); ?>

                            </small>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                            $reservation['brand'] ?? ''
                        ); ?>

                            <?= htmlspecialchars(
                            $reservation['model'] ?? ''
                        ); ?>

                            <br>

                            <small class="text-muted">

                                <?= htmlspecialchars(
                                $reservation[
                                    'plate_number'
                                ] ?? ''
                            ); ?>

                            </small>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                            $reservation['service_type']
                        ); ?>

                        </td>


                        <td>

                            <?= date(
                            'M d, Y',
                            strtotime(
                                $reservation[
                                    'appointment_date'
                                ]
                            )
                        ); ?>

                            <br>

                            <small class="text-muted">

                                <?= date(
                                'h:i A',
                                strtotime(
                                    $reservation[
                                        'appointment_time'
                                    ]
                                )
                            ); ?>

                            </small>

                        </td>


                        <td>

                            <?= !empty(
                            $reservation['mechanic_name']
                        )
                            ? htmlspecialchars(
                                $reservation[
                                    'mechanic_name'
                                ]
                            )
                            : '<span class="text-muted">Not assigned</span>'; ?>

                        </td>


                        <td>

                            <span class="badge text-bg-<?= $status_class; ?>">
                                <?= htmlspecialchars(
                                $reservation['status']
                            ); ?>
                            </span>

                        </td>


                        <td>

                            <a href="reservation_view.php?id=<?= $reservation['id']; ?>" class="btn btn-sm btn-dark">
                                View
                            </a>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                    <?php else: ?>

                    <tr>

                        <td colspan="8" class="text-center py-5 text-muted">
                            No appointments found.
                        </td>

                    </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<?php

include "includes/admin_footer.php";

?>