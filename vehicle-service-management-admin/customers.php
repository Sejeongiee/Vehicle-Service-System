<?php

include "includes/admin_header.php";


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');


/*
|--------------------------------------------------------------------------
| CUSTOMER QUERY
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        u.id,
        u.fullname,
        u.email,
        u.created_at,

        COUNT(DISTINCT v.id) AS vehicle_count,

        COUNT(DISTINCT r.id) AS reservation_count

    FROM users u

    LEFT JOIN vehicles v
        ON u.id = v.user_id

    LEFT JOIN reservations r
        ON u.id = r.user_id

    WHERE u.role = 'customer'
";


/*
|--------------------------------------------------------------------------
| SEARCH FILTER
|--------------------------------------------------------------------------
*/

if (!empty($search)) {

    $search_safe = mysqli_real_escape_string(
        $conn,
        $search
    );

    $query .= "
        AND (
            u.fullname LIKE '%$search_safe%'
            OR u.email LIKE '%$search_safe%'
        )
    ";
}


$query .= "

    GROUP BY
        u.id,
        u.fullname,
        u.email,
        u.created_at

    ORDER BY
        u.created_at DESC
";


$result = mysqli_query(
    $conn,
    $query
);

?>


<div class="container-fluid">


    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Customers
            </h2>

            <p class="text-muted mb-0">
                View and manage registered customers.
            </p>

        </div>

    </div>



    <!-- SEARCH -->

    <div class="dashboard-card mb-4">

        <form
            method="GET"
            class="row g-3"
        >

            <div class="col-md-9">

                <label class="form-label">
                    Search Customer
                </label>

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by name or email..."
                    value="<?= htmlspecialchars($search); ?>"
                >

            </div>


            <div class="col-md-3 d-flex align-items-end">

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                >

                    🔍 Search

                </button>

            </div>

        </form>

    </div>



    <!-- CUSTOMER TABLE -->

    <div class="dashboard-card">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h4 class="mb-0">
                Registered Customers
            </h4>

            <?php if (!empty($search)): ?>

                <a
                    href="customers.php"
                    class="btn btn-sm btn-outline-secondary"
                >

                    Clear Search

                </a>

            <?php endif; ?>

        </div>


        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Customer</th>

                        <th>Email</th>

                        <th>Vehicles</th>

                        <th>Reservations</th>

                        <th>Registered</th>

                    </tr>

                </thead>


                <tbody>


                    <?php if (
                        $result &&
                        mysqli_num_rows($result) > 0
                    ): ?>


                        <?php while (
                            $customer =
                            mysqli_fetch_assoc($result)
                        ): ?>

                            <tr>


                                <!-- ID -->

                                <td>

                                    <strong>

                                        #<?= $customer['id']; ?>

                                    </strong>

                                </td>



                                <!-- CUSTOMER -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $customer[
                                                'fullname'
                                            ]
                                        ); ?>

                                    </strong>

                                </td>



                                <!-- EMAIL -->

                                <td>

                                    <?= htmlspecialchars(
                                        $customer['email']
                                    ); ?>

                                </td>



                                <!-- VEHICLES -->

                                <td>

                                    <span class="badge text-bg-primary">

                                        <?= $customer[
                                            'vehicle_count'
                                        ]; ?>

                                    </span>

                                </td>



                                <!-- RESERVATIONS -->

                                <td>

                                    <span class="badge text-bg-secondary">

                                        <?= $customer[
                                            'reservation_count'
                                        ]; ?>

                                    </span>

                                </td>



                                <!-- REGISTERED -->

                                <td>

                                    <?= date(
                                        'M d, Y',
                                        strtotime(
                                            $customer[
                                                'created_at'
                                            ]
                                        )
                                    ); ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="6"
                                class="text-center py-5"
                            >

                                <div class="fs-1 mb-3">
                                    👤
                                </div>


                                <h5>
                                    No Customers Found
                                </h5>


                                <?php if (
                                    !empty($search)
                                ): ?>

                                    <p class="text-muted mb-0">

                                        No customer matches
                                        your search.

                                    </p>

                                <?php else: ?>

                                    <p class="text-muted mb-0">

                                        There are currently
                                        no registered customers.

                                    </p>

                                <?php endif; ?>


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