<?php

include "includes/admin_header.php";

$query = "
    SELECT *
    FROM services
    ORDER BY
        CASE
            WHEN status = 'Active' THEN 1
            ELSE 2
        END,
        service_name ASC
";


$result = mysqli_query(
    $conn,
    $query
);

?>

<?php if (($_GET['added'] ?? '') === '1'): ?>

    <div class="alert alert-success">
        Service added successfully.
    </div>

<?php endif; ?>


<div class="container-fluid">


    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Services
            </h2>

            <p class="text-muted mb-0">
                Manage vehicle services offered to customers.
            </p>

        </div>


        <a
            href="service_add.php"
            class="btn btn-primary"
        >
            + Add Service
        </a>

    </div>



    <div class="dashboard-card">


        <div class="table-responsive">

            <table class="table table-hover align-middle">


                <thead>

                    <tr>

                        <th>#</th>

                        <th>Service</th>

                        <th>Description</th>

                        <th>Price</th>

                        <th>Duration</th>

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
                            $service =
                            mysqli_fetch_assoc($result)
                        ): ?>


                            <tr>


                                <td>

                                    #<?= $service['id']; ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $service[
                                                'service_name'
                                            ]
                                        ); ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php if (
                                        !empty(
                                            $service[
                                                'description'
                                            ]
                                        )
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $service[
                                                'description'
                                            ]
                                        ); ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    ₱<?= number_format(
                                        $service['price'],
                                        2
                                    ); ?>

                                </td>


                                <td>

                                    <?php if (
                                        !empty(
                                            $service[
                                                'estimated_duration'
                                            ]
                                        )
                                    ): ?>

                                        <?= intval(
                                            $service[
                                                'estimated_duration'
                                            ]
                                        ); ?>

                                        minutes

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php

                                    $badge =
                                        $service['status']
                                        === 'Active'
                                        ? 'success'
                                        : 'secondary';

                                    ?>

                                    <span
                                        class="badge text-bg-<?= $badge; ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $service['status']
                                        ); ?>

                                    </span>

                                </td>


                                <td>

                                    <a
                                        href="service_edit.php?id=<?= $service['id']; ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >

                                        Edit

                                    </a>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5"
                            >

                                <div class="fs-1 mb-3">
                                    🛠
                                </div>

                                <h5>
                                    No Services Found
                                </h5>

                                <p class="text-muted mb-0">

                                    Add your first vehicle service.

                                </p>

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