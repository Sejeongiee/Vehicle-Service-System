<?php

include "includes/admin_auth.php";
include "includes/config.php";


$service_id = intval($_GET['id'] ?? 0);


if ($service_id <= 0) {

    header("Location: services.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET SERVICE
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM services
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $service_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$service = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$service) {

    header("Location: services.php");
    exit;

}


$service_name = $service['service_name'];
$description = $service['description'];
$price = $service['price'];
$estimated_duration = $service['estimated_duration'];
$status = $service['status'];

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $service_name =
        trim($_POST['service_name'] ?? '');

    $description =
        trim($_POST['description'] ?? '');

    $price =
        trim($_POST['price'] ?? '');

    $estimated_duration =
        trim($_POST['estimated_duration'] ?? '');

    $status =
        $_POST['status'] ?? 'Active';


    if ($service_name === '') {

        $error = "Service name is required.";

    } elseif (
        $price === '' ||
        !is_numeric($price) ||
        $price < 0
    ) {

        $error = "Please enter a valid service price.";

    } elseif (
        $estimated_duration !== '' &&
        (
            !is_numeric($estimated_duration) ||
            $estimated_duration <= 0
        )
    ) {

        $error =
            "Estimated duration must be a positive number.";

    } elseif (
        !in_array(
            $status,
            ['Active', 'Inactive'],
            true
        )
    ) {

        $error = "Invalid service status.";

    }


    if ($error === '') {

        $price_value =
            floatval($price);

        $duration_value =
            $estimated_duration === ''
            ? null
            : intval($estimated_duration);


        $update_stmt = mysqli_prepare(
            $conn,
            "UPDATE services
             SET
                service_name = ?,
                description = ?,
                price = ?,
                estimated_duration = ?,
                status = ?
             WHERE id = ?"
        );


        mysqli_stmt_bind_param(
            $update_stmt,
            "ssdisi",
            $service_name,
            $description,
            $price_value,
            $duration_value,
            $status,
            $service_id
        );


        if (
            mysqli_stmt_execute(
                $update_stmt
            )
        ) {

            $success =
                "Service updated successfully.";

        } else {

            $error =
                "Unable to update service.";

        }


        mysqli_stmt_close(
            $update_stmt
        );

    }

}


include "includes/admin_header.php";

?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>Edit Service</h2>

            <p class="text-muted mb-0">
                Update service information and availability.
            </p>

        </div>

        <a
            href="services.php"
            class="btn btn-secondary"
        >
            ← Back to Services
        </a>

    </div>


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


    <div class="row">

        <div class="col-lg-8">

            <div class="dashboard-card">

                <form method="POST">


                    <div class="mb-3">

                        <label class="form-label">
                            Service Name
                        </label>

                        <input
                            type="text"
                            name="service_name"
                            class="form-control"
                            value="<?= htmlspecialchars($service_name); ?>"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Description
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="4"
                        ><?= htmlspecialchars($description); ?></textarea>

                    </div>


                    <div class="row">


                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Price
                            </label>

                            <input
                                type="number"
                                name="price"
                                class="form-control"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars($price); ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Estimated Duration
                            </label>

                            <div class="input-group">

                                <input
                                    type="number"
                                    name="estimated_duration"
                                    class="form-control"
                                    min="1"
                                    value="<?= htmlspecialchars(
                                        $estimated_duration ?? ''
                                    ); ?>"
                                >

                                <span class="input-group-text">
                                    minutes
                                </span>

                            </div>

                        </div>


                    </div>


                    <div class="mb-4">

                        <label class="form-label">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option
                                value="Active"
                                <?= $status === 'Active' ? 'selected' : ''; ?>
                            >
                                Active
                            </option>

                            <option
                                value="Inactive"
                                <?= $status === 'Inactive' ? 'selected' : ''; ?>
                            >
                                Inactive
                            </option>

                        </select>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Changes
                    </button>


                </form>

            </div>

        </div>

    </div>

</div>

<?php include "includes/admin_footer.php"; ?>