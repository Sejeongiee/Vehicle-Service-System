<?php

include "includes/admin_auth.php";
include "includes/config.php";

$service_name = "";
$description = "";
$price = "";
$estimated_duration = "";
$status = "Active";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $service_name = trim($_POST['service_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $estimated_duration = trim($_POST['estimated_duration'] ?? '');
    $status = $_POST['status'] ?? 'Active';


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

        $error = "Estimated duration must be a positive number.";

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

        $duration_value =
            $estimated_duration === ''
            ? null
            : intval($estimated_duration);

        $price_value = floatval($price);


        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO services
            (
                service_name,
                description,
                price,
                estimated_duration,
                status
            )
            VALUES (?, ?, ?, ?, ?)"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "ssdis",
            $service_name,
            $description,
            $price_value,
            $duration_value,
            $status
        );


        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            header("Location: services.php?added=1");
            exit;

        }

        $error = "Unable to add service.";

        mysqli_stmt_close($stmt);

    }

}


include "includes/admin_header.php";

?>

<div class="container-fluid">

    <div class="mb-4">

        <h2>Add Service</h2>

        <p class="text-muted">
            Add a new vehicle service offered to customers.
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

                <form method="POST">


                    <div class="mb-3">

                        <label class="form-label">
                            Service Name
                            <span class="text-danger">*</span>
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
                                <span class="text-danger">*</span>
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
                                    value="<?= htmlspecialchars($estimated_duration); ?>"
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


                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            + Add Service
                        </button>

                        <a
                            href="services.php"
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

<?php include "includes/admin_footer.php"; ?>