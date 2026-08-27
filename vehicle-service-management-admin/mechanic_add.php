<?php

include "includes/admin_auth.php";
include "includes/config.php";


/*
|--------------------------------------------------------------------------
| FORM VARIABLES
|--------------------------------------------------------------------------
*/

$fullname = '';
$specialization = '';
$phone = '';
$email = '';
$status = 'Available';

$error = '';


/*
|--------------------------------------------------------------------------
| HANDLE FORM
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

    $status =
        $_POST['status'] ?? 'Available';


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($fullname === '') {

        $error =
            'Mechanic name is required.';

    } elseif (
        !in_array(
            $status,
            ['Available', 'Busy', 'Inactive'],
            true
        )
    ) {

        $error =
            'Invalid mechanic status.';

    } elseif (
        $email !== '' &&
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Please enter a valid email address.';

    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO mechanics
            (
                fullname,
                specialization,
                phone,
                email,
                status
            )
            VALUES
            (?, ?, ?, ?, ?)"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "sssss",
            $fullname,
            $specialization,
            $phone,
            $email,
            $status
        );


        if (
            mysqli_stmt_execute($stmt)
        ) {

            mysqli_stmt_close($stmt);

            header(
                "Location: mechanics.php"
            );

            exit;

        }


        $error =
            'Unable to add mechanic.';

        mysqli_stmt_close($stmt);

    }

}


include "includes/admin_header.php";

?>


<div class="container-fluid">


    <div class="mb-4">

        <h2>
            Add Mechanic
        </h2>

        <p class="text-muted">
            Add a new mechanic to the system.
        </p>

    </div>



    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>



    <div class="row">

        <div class="col-lg-8">

            <div class="dashboard-card">


                <form
                    method="POST"
                >


                    <!-- NAME -->

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
                            placeholder="e.g. Engine Repair"
                            value="<?= htmlspecialchars($specialization); ?>"
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
                                value="Busy"
                                <?= $status === 'Busy'
                                    ? 'selected'
                                    : ''; ?>
                            >

                                Busy

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

                    </div>



                    <!-- BUTTONS -->

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            + Add Mechanic

                        </button>


                        <a
                            href="mechanics.php"
                            class="btn btn-secondary"
                        >

                            Cancel

                        </a>

                    </div>


                </form>


            </div>

        </div>

    </div>


</div>


<?php

include "includes/admin_footer.php";

?>