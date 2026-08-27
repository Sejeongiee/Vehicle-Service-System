<?php

include "includes/admin_auth.php";
include "includes/config.php";


/*
|--------------------------------------------------------------------------
| GET MECHANIC ID
|--------------------------------------------------------------------------
*/

$mechanic_id = intval($_GET['id'] ?? 0);

if ($mechanic_id <= 0) {

    header("Location: mechanics.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET MECHANIC
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        fullname,
        specialization,
        phone,
        email,
        status
     FROM mechanics
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $mechanic_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$mechanic = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$mechanic) {

    header("Location: mechanics.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| CHECK ACTIVE RESERVATIONS
|--------------------------------------------------------------------------
|
| A mechanic is considered actively assigned when they have
| an Approved or In Progress reservation.
|
*/

$active_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM reservations
     WHERE mechanic_id = ?
     AND status IN ('Approved', 'In Progress')"
);

mysqli_stmt_bind_param(
    $active_stmt,
    "i",
    $mechanic_id
);

mysqli_stmt_execute($active_stmt);

$active_result =
    mysqli_stmt_get_result($active_stmt);

$active_data =
    mysqli_fetch_assoc($active_result);

$active_reservations =
    intval($active_data['total']);

mysqli_stmt_close($active_stmt);


/*
|--------------------------------------------------------------------------
| FORM VALUES
|--------------------------------------------------------------------------
*/

$fullname = $mechanic['fullname'];
$specialization = $mechanic['specialization'];
$phone = $mechanic['phone'];
$email = $mechanic['email'];
$status = $mechanic['status'];

$error = '';
$success = '';


/*
|--------------------------------------------------------------------------
| HANDLE UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname =
        trim($_POST['fullname'] ?? '');

    $specialization =
        trim($_POST['specialization'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $requested_status =
        $_POST['status'] ?? $status;


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($fullname === '') {

        $error = "Mechanic name is required.";

    } elseif (
        $email !== '' &&
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error = "Please enter a valid email address.";

    } elseif (
        !in_array(
            $requested_status,
            [
                'Available',
                'Busy',
                'Inactive'
            ],
            true
        )
    ) {

        $error = "Invalid mechanic status.";

    }


    /*
    |--------------------------------------------------------------------------
    | PROTECT BUSY MECHANICS
    |--------------------------------------------------------------------------
    */

    if (
        $error === '' &&
        $active_reservations > 0
    ) {

        /*
         * Active assignments control the mechanic's status.
         * Do not allow manual status changes.
         */

        $requested_status = 'Busy';

    }


    /*
    |--------------------------------------------------------------------------
    | DON'T MANUALLY SET BUSY
    |--------------------------------------------------------------------------
    |
    | Busy should normally only happen when a mechanic
    | is assigned to a reservation.
    |
    */

    if (
        $error === '' &&
        $active_reservations === 0 &&
        $requested_status === 'Busy'
    ) {

        $error =
            "A mechanic can only become Busy when assigned to an active reservation.";

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE MECHANIC
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $update_stmt = mysqli_prepare(
            $conn,
            "UPDATE mechanics
             SET
                fullname = ?,
                specialization = ?,
                phone = ?,
                email = ?,
                status = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $update_stmt,
            "sssssi",
            $fullname,
            $specialization,
            $phone,
            $email,
            $requested_status,
            $mechanic_id
        );


        if (mysqli_stmt_execute($update_stmt)) {

            $success =
                "Mechanic information updated successfully.";

            $status = $requested_status;

        } else {

            $error =
                "Unable to update mechanic information.";

        }

        mysqli_stmt_close($update_stmt);

    }

}


include "includes/admin_header.php";

?>


<div class="container-fluid">


    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Edit Mechanic
            </h2>

            <p class="text-muted mb-0">
                Update mechanic information and availability.
            </p>

        </div>


        <a
            href="mechanics.php"
            class="btn btn-secondary"
        >
            ← Back to Mechanics
        </a>

    </div>



    <!-- ALERTS -->

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <?php if ($success !== ''): ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($success); ?>

        </div>

    <?php endif; ?>



    <div class="row g-4">


        <!-- EDIT FORM -->

        <div class="col-lg-8">

            <div class="dashboard-card">

                <form method="POST">


                    <!-- FULL NAME -->

                    <div class="mb-3">

                        <label class="form-label">

                            Full Name

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <input
                            type="text"
                            name="fullname"
                            class="form-control"
                            value="<?= htmlspecialchars($fullname); ?>"
                            required
                        >

                    </div>



                    <!-- SPECIALIZATION -->

                    <div class="mb-3">

                        <label class="form-label">
                            Specialization
                        </label>

                        <input
                            type="text"
                            name="specialization"
                            class="form-control"
                            value="<?= htmlspecialchars($specialization); ?>"
                            placeholder="e.g. Engine Repair"
                        >

                    </div>



                    <!-- PHONE -->

                    <div class="mb-3">

                        <label class="form-label">
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="<?= htmlspecialchars($phone); ?>"
                        >

                    </div>



                    <!-- EMAIL -->

                    <div class="mb-3">

                        <label class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?= htmlspecialchars($email); ?>"
                        >

                    </div>



                    <!-- STATUS -->

                    <div class="mb-4">

                        <label class="form-label">
                            Status
                        </label>


                        <?php if ($active_reservations > 0): ?>

                            <!-- LOCKED BUSY STATUS -->

                            <input
                                type="text"
                                class="form-control"
                                value="Busy"
                                readonly
                            >

                            <input
                                type="hidden"
                                name="status"
                                value="Busy"
                            >


                            <small class="text-warning">

                                This mechanic has an active
                                reservation. Status cannot be
                                changed manually.

                            </small>


                        <?php else: ?>


                            <select
                                name="status"
                                class="form-select"
                            >

                                <option
                                    value="Available"
                                    <?= $status === 'Available'
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Available
                                </option>


                                <option
                                    value="Inactive"
                                    <?= $status === 'Inactive'
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Inactive
                                </option>

                            </select>


                            <small class="text-muted">

                                Busy status is automatically
                                controlled by reservation
                                assignments.

                            </small>


                        <?php endif; ?>

                    </div>



                    <!-- SAVE -->

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Changes
                    </button>


                </form>

            </div>

        </div>



        <!-- STATUS INFORMATION -->

        <div class="col-lg-4">

            <div class="dashboard-card">

                <h4 class="mb-4">
                    Mechanic Status
                </h4>


                <?php if ($active_reservations > 0): ?>

                    <div class="alert alert-warning">

                        <strong>
                            Busy
                        </strong>

                        <br><br>

                        This mechanic currently has

                        <strong>
                            <?= $active_reservations; ?>
                        </strong>

                        active service
                        <?= $active_reservations === 1
                            ? ''
                            : 's'; ?>.

                    </div>

                <?php elseif ($status === 'Available'): ?>

                    <div class="alert alert-success">

                        <strong>
                            Available
                        </strong>

                        <br><br>

                        This mechanic can be assigned
                        to a new reservation.

                    </div>

                <?php elseif ($status === 'Inactive'): ?>

                    <div class="alert alert-secondary">

                        <strong>
                            Inactive
                        </strong>

                        <br><br>

                        This mechanic will not appear
                        in the reservation assignment list.

                    </div>

                <?php endif; ?>


            </div>

        </div>


    </div>


</div>


<?php

include "includes/admin_footer.php";

?>