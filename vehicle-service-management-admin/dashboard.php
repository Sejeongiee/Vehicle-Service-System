<?php

include "includes/admin_header.php";

$total_customers = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM users
         WHERE role = 'customer'"
    )
)['total'];


$total_vehicles = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM vehicles"
    )
)['total'];


$available_mechanics = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM mechanics
         WHERE status = 'Available'"
    )
)['total'];


$total_revenue = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COALESCE(SUM(amount), 0) AS total
         FROM payments
         WHERE status = 'Paid'"
    )
)['total'];

$total = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM reservations"
    )
)['total'];

$pending = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM reservations
         WHERE status = 'Pending'"
    )
)['total'];

$approved = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM reservations
         WHERE status = 'Approved'"
    )
)['total'];

$completed = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM reservations
         WHERE status = 'Completed'"
    )
)['total'];

?>

<h2 class="mb-4">
    Dashboard
</h2>

<div class="row g-4">

    <div class="col-md-3">

        <div class="stat-card">

            <h6>Total Reservations</h6>

            <h2>
                <?= $total; ?>
            </h2>

        </div>

    </div>

    <div class="col-md-3">

        <div class="stat-card">

            <h6>Pending</h6>

            <h2>
                <?= $pending; ?>
            </h2>

        </div>

    </div>

    <div class="col-md-3">

        <div class="stat-card">

            <h6>Approved</h6>

            <h2>
                <?= $approved; ?>
            </h2>

        </div>

    </div>

    <div class="col-md-3">

        <div class="stat-card">

            <h6>Completed</h6>

            <h2>
                <?= $completed; ?>
            </h2>

        </div>

    </div>

</div>

<div class="row g-4 mt-1">

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
                Vehicles
            </h6>

            <h2>
                <?= $total_vehicles; ?>
            </h2>

        </div>

    </div>


    <div class="col-md-3">

        <div class="stat-card">

            <h6>
                Available Mechanics
            </h6>

            <h2>
                <?= $available_mechanics; ?>
            </h2>

        </div>

    </div>


    <div class="col-md-3">

        <div class="stat-card">

            <h6>
                Revenue
            </h6>

            <h2 class="fs-4">

                ₱<?= number_format(
                    $total_revenue,
                    2
                ); ?>

            </h2>

        </div>

    </div>

</div>

<?php

include "includes/admin_footer.php";

?>