<?php

include "includes/admin_header.php";


/*
|--------------------------------------------------------------------------
| GET ALL MECHANICS
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        m.id,
        m.fullname,
        m.specialization,
        m.phone,
        m.email,
        m.status,
        m.created_at,

        COUNT(r.id) AS reservation_count

    FROM mechanics m

    LEFT JOIN reservations r
        ON m.id = r.mechanic_id

    GROUP BY
        m.id,
        m.fullname,
        m.specialization,
        m.phone,
        m.email,
        m.status,
        m.created_at

    ORDER BY
        CASE
            WHEN m.status = 'Available' THEN 1
            WHEN m.status = 'Busy' THEN 2
            WHEN m.status = 'Inactive' THEN 3
            ELSE 4
        END,
        m.fullname ASC
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
                Mechanics
            </h2>

            <p class="text-muted mb-0">
                Manage mechanics and their availability.
            </p>

        </div>


        <a
            href="mechanic_add.php"
            class="btn btn-primary"
        >

            + Add Mechanic

        </a>

    </div>



    <!-- MECHANIC TABLE -->

    <div class="dashboard-card">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Mechanic</th>

                        <th>Specialization</th>

                        <th>Contact</th>

                        <th>Status</th>

                        <th>Reservations</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>


                    <?php if (
                        $result &&
                        mysqli_num_rows($result) > 0
                    ): ?>


                        <?php while (
                            $mechanic =
                            mysqli_fetch_assoc($result)
                        ): ?>


                            <?php

                            $status =
                                $mechanic['status'];

                            $status_class =
                                'secondary';


                            if (
                                $status === 'Available'
                            ) {

                                $status_class =
                                    'success';

                            } elseif (
                                $status === 'Busy'
                            ) {

                                $status_class =
                                    'warning';

                            } elseif (
                                $status === 'Inactive'
                            ) {

                                $status_class =
                                    'danger';

                            }

                            ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <strong>

                                        #<?= $mechanic['id']; ?>

                                    </strong>

                                </td>



                                <!-- NAME -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $mechanic[
                                                'fullname'
                                            ]
                                        ); ?>

                                    </strong>

                                </td>



                                <!-- SPECIALIZATION -->

                                <td>

                                    <?php if (
                                        !empty(
                                            $mechanic[
                                                'specialization'
                                            ]
                                        )
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $mechanic[
                                                'specialization'
                                            ]
                                        ); ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Not specified
                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- CONTACT -->

                                <td>

                                    <?php if (
                                        !empty(
                                            $mechanic['phone']
                                        )
                                    ): ?>

                                        <div>

                                            <?= htmlspecialchars(
                                                $mechanic[
                                                    'phone'
                                                ]
                                            ); ?>

                                        </div>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty(
                                            $mechanic['email']
                                        )
                                    ): ?>

                                        <small class="text-muted">

                                            <?= htmlspecialchars(
                                                $mechanic[
                                                    'email'
                                                ]
                                            ); ?>

                                        </small>

                                    <?php endif; ?>


                                    <?php if (
                                        empty(
                                            $mechanic['phone']
                                        ) &&
                                        empty(
                                            $mechanic['email']
                                        )
                                    ): ?>

                                        <span class="text-muted">

                                            No contact information

                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="badge text-bg-<?= $status_class; ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $status
                                        ); ?>

                                    </span>

                                </td>



                                <!-- RESERVATIONS -->

                                <td>

                                    <span class="badge text-bg-secondary">

                                        <?= $mechanic[
                                            'reservation_count'
                                        ]; ?>

                                    </span>

                                </td>



                                <!-- ACTION -->

                                <td>

                                    <div class="d-flex gap-2">

    <a
        href="mechanic_edit.php?id=<?= $mechanic['id']; ?>"
        class="btn btn-sm btn-outline-primary"
    >
        Edit
    </a>

    <?php if ($mechanic['status'] === 'Available'): ?>

        <form
            method="POST"
            action="mechanic_status.php"
            onsubmit="return confirm('Deactivate this mechanic?');"
        >

            <input
                type="hidden"
                name="mechanic_id"
                value="<?= $mechanic['id']; ?>"
            >

            <input
                type="hidden"
                name="action"
                value="deactivate"
            >

            <button
                type="submit"
                class="btn btn-sm btn-outline-danger"
            >
                Deactivate
            </button>

        </form>

    <?php elseif ($mechanic['status'] === 'Inactive'): ?>

        <form
            method="POST"
            action="mechanic_status.php"
        >

            <input
                type="hidden"
                name="mechanic_id"
                value="<?= $mechanic['id']; ?>"
            >

            <input
                type="hidden"
                name="action"
                value="activate"
            >

            <button
                type="submit"
                class="btn btn-sm btn-outline-success"
            >
                Activate
            </button>

        </form>

    <?php endif; ?>

</div>

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
                                    🔧
                                </div>


                                <h5>
                                    No Mechanics Found
                                </h5>


                                <p class="text-muted mb-0">

                                    Add your first mechanic
                                    to get started.

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