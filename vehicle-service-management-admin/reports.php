<?php

include "includes/admin_header.php";


/*
|--------------------------------------------------------------------------
| RESERVATION COUNTS
|--------------------------------------------------------------------------
*/

$total_reservations = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM reservations"
    )
)['total'];


$pending_reservations = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM reservations
         WHERE status = 'Pending'"
    )
)['total'];


$approved_reservations = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM reservations
         WHERE status = 'Approved'"
    )
)['total'];


$in_progress_reservations = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM reservations
         WHERE status = 'In Progress'"
    )
)['total'];


$completed_reservations = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM reservations
         WHERE status = 'Completed'"
    )
)['total'];


$cancelled_reservations = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM reservations
         WHERE status = 'Cancelled'"
    )
)['total'];


/*
|--------------------------------------------------------------------------
| CUSTOMER COUNT
|--------------------------------------------------------------------------
*/

$total_customers = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM users
         WHERE role = 'customer'"
    )
)['total'];


/*
|--------------------------------------------------------------------------
| VEHICLE COUNT
|--------------------------------------------------------------------------
*/

$total_vehicles = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM vehicles"
    )
)['total'];


/*
|--------------------------------------------------------------------------
| PAYMENT SUMMARY
|--------------------------------------------------------------------------
*/

$total_revenue = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COALESCE(SUM(amount), 0) AS total
         FROM payments
         WHERE status = 'Paid'"
    )
)['total'];


$pending_payments = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM payments
         WHERE status = 'Pending'"
    )
)['total'];


/*
|--------------------------------------------------------------------------
| MOST REQUESTED SERVICES
|--------------------------------------------------------------------------
*/

$services_result = mysqli_query(
    $conn,
    "SELECT
        service_type,
        COUNT(*) AS total
     FROM reservations
     GROUP BY service_type
     ORDER BY total DESC
     LIMIT 10"
);


/*
|--------------------------------------------------------------------------
| MECHANIC WORKLOAD
|--------------------------------------------------------------------------
*/

$mechanic_result = mysqli_query(
    $conn,
    "SELECT
        m.id,
        m.fullname,
        m.specialization,
        m.status,

        COUNT(
            CASE
                WHEN r.status IN (
                    'Approved',
                    'In Progress'
                )
                THEN 1
            END
        ) AS active_jobs,

        COUNT(
            CASE
                WHEN r.status = 'Completed'
                THEN 1
            END
        ) AS completed_jobs

     FROM mechanics m

     LEFT JOIN reservations r
        ON m.id = r.mechanic_id

     GROUP BY
        m.id,
        m.fullname,
        m.specialization,
        m.status

     ORDER BY completed_jobs DESC"
);


/*
|--------------------------------------------------------------------------
| MONTHLY REVENUE
|--------------------------------------------------------------------------
*/

$monthly_revenue_result = mysqli_query(
    $conn,
    "SELECT
        DATE_FORMAT(paid_at, '%Y-%m') AS month,
        SUM(amount) AS total
     FROM payments
     WHERE status = 'Paid'
       AND paid_at IS NOT NULL
     GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
     ORDER BY month DESC
     LIMIT 12"
);

?>


<div class="container-fluid">


    <!-- PAGE HEADER -->

    <div class="mb-4">

        <h2>
            Reports
        </h2>

        <p class="text-muted">
            Overview of reservations, customers,
            mechanics, services, and revenue.
        </p>

    </div>


    <!-- SUMMARY CARDS -->

    <div class="row g-4 mb-4">


        <div class="col-md-3">

            <div class="stat-card">

                <h6>
                    Total Reservations
                </h6>

                <h2>
                    <?= $total_reservations; ?>
                </h2>

            </div>

        </div>


        <div class="col-md-3">

            <div class="stat-card">

                <h6>
                    Completed Services
                </h6>

                <h2>
                    <?= $completed_reservations; ?>
                </h2>

            </div>

        </div>


        <div class="col-md-3">

            <div class="stat-card">

                <h6>
                    Customers
                </h6>

                <h2>
                    <?= $total_customers; ?>
                </h2>

            </div>

        </div>


        <div class="col-md-3">

            <div class="stat-card">

                <h6>
                    Registered Vehicles
                </h6>

                <h2>
                    <?= $total_vehicles; ?>
                </h2>

            </div>

        </div>


    </div>



    <!-- RESERVATION STATUS -->

    <div class="row g-4 mb-4">


        <div class="col-md-2">

            <div class="dashboard-card text-center">

                <h6>
                    Pending
                </h6>

                <h3>
                    <?= $pending_reservations; ?>
                </h3>

            </div>

        </div>


        <div class="col-md-2">

            <div class="dashboard-card text-center">

                <h6>
                    Approved
                </h6>

                <h3>
                    <?= $approved_reservations; ?>
                </h3>

            </div>

        </div>


        <div class="col-md-2">

            <div class="dashboard-card text-center">

                <h6>
                    In Progress
                </h6>

                <h3>
                    <?= $in_progress_reservations; ?>
                </h3>

            </div>

        </div>


        <div class="col-md-2">

            <div class="dashboard-card text-center">

                <h6>
                    Completed
                </h6>

                <h3>
                    <?= $completed_reservations; ?>
                </h3>

            </div>

        </div>


        <div class="col-md-2">

            <div class="dashboard-card text-center">

                <h6>
                    Cancelled
                </h6>

                <h3>
                    <?= $cancelled_reservations; ?>
                </h3>

            </div>

        </div>


        <div class="col-md-2">

            <div class="dashboard-card text-center">

                <h6>
                    Pending Payments
                </h6>

                <h3>
                    <?= $pending_payments; ?>
                </h3>

            </div>

        </div>


    </div>



    <!-- REVENUE -->

    <div class="row g-4 mb-4">


        <div class="col-lg-4">

            <div class="dashboard-card h-100">

                <h4 class="mb-3">
                    Total Revenue
                </h4>

                <h2>

                    ₱<?= number_format(
                        $total_revenue,
                        2
                    ); ?>

                </h2>

                <p class="text-muted mb-0">
                    Based on payments marked as Paid.
                </p>

            </div>

        </div>



        <!-- MONTHLY REVENUE -->

        <div class="col-lg-8">

            <div class="dashboard-card">

                <h4 class="mb-4">
                    Monthly Revenue
                </h4>


                <div class="table-responsive">

                    <table class="table">

                        <thead>

                            <tr>

                                <th>
                                    Month
                                </th>

                                <th>
                                    Revenue
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (
                                $monthly_revenue_result &&
                                mysqli_num_rows(
                                    $monthly_revenue_result
                                ) > 0
                            ): ?>


                                <?php while (
                                    $revenue =
                                        mysqli_fetch_assoc(
                                            $monthly_revenue_result
                                        )
                                ): ?>

                                    <tr>

                                        <td>

                                            <?= date(
                                                'F Y',
                                                strtotime(
                                                    $revenue['month']
                                                    . '-01'
                                                )
                                            ); ?>

                                        </td>

                                        <td>

                                            ₱<?= number_format(
                                                $revenue['total'],
                                                2
                                            ); ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="2"
                                        class="text-center text-muted"
                                    >

                                        No paid transactions yet.

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


    </div>



    <!-- SERVICES + MECHANICS -->

    <div class="row g-4">


        <!-- MOST REQUESTED SERVICES -->

        <div class="col-lg-6">

            <div class="dashboard-card">

                <h4 class="mb-4">
                    Most Requested Services
                </h4>


                <div class="table-responsive">

                    <table class="table table-hover">

                        <thead>

                            <tr>

                                <th>
                                    Service
                                </th>

                                <th>
                                    Requests
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (
                                $services_result &&
                                mysqli_num_rows(
                                    $services_result
                                ) > 0
                            ): ?>


                                <?php while (
                                    $service =
                                        mysqli_fetch_assoc(
                                            $services_result
                                        )
                                ): ?>

                                    <tr>

                                        <td>

                                            <?= htmlspecialchars(
                                                $service[
                                                    'service_type'
                                                ]
                                            ); ?>

                                        </td>

                                        <td>

                                            <span
                                                class="badge text-bg-primary"
                                            >

                                                <?= $service[
                                                    'total'
                                                ]; ?>

                                            </span>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="2"
                                        class="text-center text-muted"
                                    >

                                        No service data available.

                                    </td>

                                </tr>

                            <?php endif; ?>


                        </tbody>

                    </table>

                </div>

            </div>

        </div>



        <!-- MECHANIC WORKLOAD -->

        <div class="col-lg-6">

            <div class="dashboard-card">

                <h4 class="mb-4">
                    Mechanic Workload
                </h4>


                <div class="table-responsive">

                    <table class="table table-hover">

                        <thead>

                            <tr>

                                <th>
                                    Mechanic
                                </th>

                                <th>
                                    Active Jobs
                                </th>

                                <th>
                                    Completed
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if (
                                $mechanic_result &&
                                mysqli_num_rows(
                                    $mechanic_result
                                ) > 0
                            ): ?>


                                <?php while (
                                    $mechanic =
                                        mysqli_fetch_assoc(
                                            $mechanic_result
                                        )
                                ): ?>

                                    <tr>

                                        <td>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $mechanic[
                                                        'fullname'
                                                    ]
                                                ); ?>

                                            </strong>

                                            <?php if (
                                                !empty(
                                                    $mechanic[
                                                        'specialization'
                                                    ]
                                                )
                                            ): ?>

                                                <br>

                                                <small class="text-muted">

                                                    <?= htmlspecialchars(
                                                        $mechanic[
                                                            'specialization'
                                                        ]
                                                    ); ?>

                                                </small>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <?= $mechanic[
                                                'active_jobs'
                                            ]; ?>

                                        </td>

                                        <td>

                                            <?= $mechanic[
                                                'completed_jobs'
                                            ]; ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="3"
                                        class="text-center text-muted"
                                    >

                                        No mechanic data available.

                                    </td>

                                </tr>

                            <?php endif; ?>


                        </tbody>

                    </table>

                </div>

            </div>

        </div>


    </div>


</div>


<?php

include "includes/admin_footer.php";

?>