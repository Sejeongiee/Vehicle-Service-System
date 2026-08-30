<?php

include "includes/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


/*
|--------------------------------------------------------------------------
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$fullname = "";
$email = "";
$phone = "";

$brand = "";
$model = "";
$year = "";
$plate_number = "";
$color = "";

$service_id = intval($_GET['service'] ?? 0);

$appointment_date = "";
$appointment_time = "";
$remarks = "";

$error = "";


/*
|--------------------------------------------------------------------------
| GET ACTIVE SERVICES
|--------------------------------------------------------------------------
*/

$services_query = mysqli_query(
    $conn,
    "SELECT
        id,
        service_name,
        description,
        price,
        estimated_duration

     FROM services

     WHERE status = 'Active'

     ORDER BY service_name ASC"
);


/*
|--------------------------------------------------------------------------
| HANDLE APPOINTMENT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | CSRF CHECK
    |--------------------------------------------------------------------------
    */

    $csrf_token = $_POST['csrf_token'] ?? '';

    if (
        empty($csrf_token) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $csrf_token
        )
    ) {
        $error = "Invalid form request. Please refresh the page and try again.";
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER
    |--------------------------------------------------------------------------
    */

    $fullname =
        trim($_POST['fullname'] ?? '');

    $email =
        strtolower(
        trim($_POST['email'] ?? ''));

    $phone =
        trim($_POST['phone'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VEHICLE
    |--------------------------------------------------------------------------
    */

    $brand =
        trim($_POST['brand'] ?? '');

    $model =
        trim($_POST['model'] ?? '');

    $year =
        trim($_POST['year'] ?? '');

    $plate_number =
    strtoupper(
        preg_replace(
            '/\s+/',
            ' ',
            trim($_POST['plate_number'] ?? '')
        )
    );

    $color =
        trim($_POST['color'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | SERVICE + APPOINTMENT
    |--------------------------------------------------------------------------
    */

    $service_id =
        intval($_POST['service_id'] ?? 0);

    $appointment_date =
        $_POST['appointment_date'] ?? '';

    $appointment_time =
        $_POST['appointment_time'] ?? '';

    $remarks =
        trim($_POST['remarks'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | BASIC VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $fullname === '' ||
        $email === '' ||
        $phone === ''
    ) {

        $error =
            "Please complete your contact information.";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            "Please enter a valid email address.";

    } elseif (
        $brand === '' ||
        $model === '' ||
        $year === '' ||
        $plate_number === ''
    ) {

        $error =
            "Please complete your vehicle information.";

    } elseif (
        !ctype_digit($year) ||
        intval($year) < 1900 ||
        intval($year) > intval(date('Y')) + 1
    ) {

        $error =
            "Please enter a valid vehicle year.";

    } elseif (
        $service_id <= 0
    ) {

        $error =
            "Please select a service.";

    } elseif (
        $appointment_date === '' ||
        $appointment_time === ''
    ) {

        $error =
            "Please select your appointment date and time.";

    }


    /*
    |--------------------------------------------------------------------------
    | PREVENT PAST APPOINTMENTS
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $appointment_timestamp =
            strtotime(
                $appointment_date
                . ' '
                . $appointment_time
            );

        if (
            $appointment_timestamp === false ||
            $appointment_timestamp <= time()
        ) {

            $error =
                "Please select a future appointment schedule.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | BUSINESS HOURS
    |--------------------------------------------------------------------------
    |
    | Example:
    | 8:00 AM - 5:00 PM
    |
    */

    if ($error === '') {

    $allowed_times = [
        '08:00',
        '08:30',
        '09:00',
        '09:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
        '12:30',
        '13:00',
        '13:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30',
        '16:00',
        '16:30',
        '17:00'
    ];


    if (!in_array(
        $appointment_time,
        $allowed_times,
        true
    )) {

        $error =
            "Please select a valid appointment time.";

    }

    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY ACTIVE SERVICE
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $service_stmt = mysqli_prepare(
            $conn,
            "SELECT
                id,
                service_name,
                price,
                estimated_duration

             FROM services

             WHERE id = ?
             AND status = 'Active'

             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $service_stmt,
            "i",
            $service_id
        );

        mysqli_stmt_execute(
            $service_stmt
        );

        $service_result =
            mysqli_stmt_get_result(
                $service_stmt
            );

        $selected_service =
            mysqli_fetch_assoc(
                $service_result
            );

        mysqli_stmt_close(
            $service_stmt
        );


        if (!$selected_service) {

            $error =
                "The selected service is no longer available.";

        }

    }



/*
|--------------------------------------------------------------------------
| CHECK APPOINTMENT CAPACITY
|--------------------------------------------------------------------------
*/

if ($error === '') {

    /*
    | Count mechanics that are usable.
    |
    | Inactive mechanics are excluded.
    */

    $mechanic_result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM mechanics
         WHERE status <> 'Inactive'"
    );

    $mechanic_row =
        mysqli_fetch_assoc($mechanic_result);

    $slot_capacity =
        intval($mechanic_row['total'] ?? 0);


    /*
    | Fallback so bookings do not completely stop
    | if there are temporarily no mechanics configured.
    */

    if ($slot_capacity < 1) {
        $slot_capacity = 1;
    }


    /*
    | Count active appointments already occupying
    | the selected schedule.
    */

    $slot_stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total

         FROM reservations

         WHERE appointment_date = ?
         AND appointment_time = ?
         AND status IN (
            'Pending',
            'Approved',
            'In Progress'
         )"
    );


    mysqli_stmt_bind_param(
        $slot_stmt,
        "ss",
        $appointment_date,
        $appointment_time
    );


    mysqli_stmt_execute($slot_stmt);

    $slot_result =
        mysqli_stmt_get_result($slot_stmt);

    $slot_row =
        mysqli_fetch_assoc($slot_result);

    $booked_slots =
        intval($slot_row['total'] ?? 0);

    mysqli_stmt_close($slot_stmt);


    if ($booked_slots >= $slot_capacity) {

        $error =
            "That appointment schedule is already full. Please select another time.";

    }

}


    /*
    |--------------------------------------------------------------------------
    | CREATE APPOINTMENT
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        mysqli_begin_transaction($conn);


        try {

            /*
            |--------------------------------------------------------------------------
            | FIND CUSTOMER
            |--------------------------------------------------------------------------
            |
            | We use email as the primary matching field.
            |
            */

            $customer_stmt = mysqli_prepare(
                $conn,
                "SELECT id

                 FROM customers

                 WHERE email = ?

                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $customer_stmt,
                "s",
                $email
            );

            mysqli_stmt_execute(
                $customer_stmt
            );

            $customer_result =
                mysqli_stmt_get_result(
                    $customer_stmt
                );

            $customer =
                mysqli_fetch_assoc(
                    $customer_result
                );

            mysqli_stmt_close(
                $customer_stmt
            );


            /*
            |--------------------------------------------------------------------------
            | CREATE CUSTOMER IF NEW
            |--------------------------------------------------------------------------
            */

            if ($customer) {

                $customer_id =
                    intval($customer['id']);


                /*
                |--------------------------------------------------------------------------
                | Keep contact details current
                |--------------------------------------------------------------------------
                */

                $update_customer =
                    mysqli_prepare(
                        $conn,
                        "UPDATE customers

                         SET
                            fullname = ?,
                            phone = ?

                         WHERE id = ?"
                    );

                mysqli_stmt_bind_param(
                    $update_customer,
                    "ssi",
                    $fullname,
                    $phone,
                    $customer_id
                );

                mysqli_stmt_execute(
                    $update_customer
                );

                mysqli_stmt_close(
                    $update_customer
                );

            } else {

                $customer_insert =
                    mysqli_prepare(
                        $conn,
                        "INSERT INTO customers
                        (
                            fullname,
                            email,
                            phone
                        )

                        VALUES (?, ?, ?)"
                    );

                mysqli_stmt_bind_param(
                    $customer_insert,
                    "sss",
                    $fullname,
                    $email,
                    $phone
                );

                mysqli_stmt_execute(
                    $customer_insert
                );

                $customer_id =
                    mysqli_insert_id($conn);

                mysqli_stmt_close(
                    $customer_insert
                );

            }


            /*
            |--------------------------------------------------------------------------
            | FIND EXISTING VEHICLE
            |--------------------------------------------------------------------------
            |
            | Plate number + customer prevents duplicates.
            |
            */

            $vehicle_stmt =
                mysqli_prepare(
                    $conn,
                    "SELECT id

                     FROM vehicles

                     WHERE customer_id = ?
                     AND plate_number = ?

                     LIMIT 1"
                );

            mysqli_stmt_bind_param(
                $vehicle_stmt,
                "is",
                $customer_id,
                $plate_number
            );

            mysqli_stmt_execute(
                $vehicle_stmt
            );

            $vehicle_result =
                mysqli_stmt_get_result(
                    $vehicle_stmt
                );

            $vehicle =
                mysqli_fetch_assoc(
                    $vehicle_result
                );

            mysqli_stmt_close(
                $vehicle_stmt
            );


            /*
            |--------------------------------------------------------------------------
            | UPDATE OR CREATE VEHICLE
            |--------------------------------------------------------------------------
            */

            if ($vehicle) {

                $vehicle_id =
                    intval($vehicle['id']);


                $update_vehicle =
                    mysqli_prepare(
                        $conn,
                        "UPDATE vehicles

                         SET
                            brand = ?,
                            model = ?,
                            year = ?,
                            color = ?

                         WHERE id = ?
                         AND customer_id = ?"
                    );

                mysqli_stmt_bind_param(
                    $update_vehicle,
                    "ssisii",
                    $brand,
                    $model,
                    $year,
                    $color,
                    $vehicle_id,
                    $customer_id
                );

                mysqli_stmt_execute(
                    $update_vehicle
                );

                mysqli_stmt_close(
                    $update_vehicle
                );

            } else {

                $vehicle_insert =
                    mysqli_prepare(
                        $conn,
                        "INSERT INTO vehicles
                        (
                            user_id,
                            customer_id,
                            brand,
                            model,
                            year,
                            plate_number,
                            color
                        )

                        VALUES (
                            NULL,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?
                        )"
                    );

                mysqli_stmt_bind_param(
                    $vehicle_insert,
                    "ississ",
                    $customer_id,
                    $brand,
                    $model,
                    $year,
                    $plate_number,
                    $color
                );

                mysqli_stmt_execute(
                    $vehicle_insert
                );

                $vehicle_id =
                    mysqli_insert_id($conn);

                mysqli_stmt_close(
                    $vehicle_insert
                );

            }


            /*
            |--------------------------------------------------------------------------
            | GENERATE REFERENCE NUMBER
            |--------------------------------------------------------------------------
            */

            do {

                $reference_number =
                    'VSMS-'
                    . date('Ymd')
                    . '-'
                    . strtoupper(
                        bin2hex(
                            random_bytes(3)
                        )
                    );


                $reference_check =
                    mysqli_prepare(
                        $conn,
                        "SELECT id
                         FROM reservations
                         WHERE reference_number = ?
                         LIMIT 1"
                    );

                mysqli_stmt_bind_param(
                    $reference_check,
                    "s",
                    $reference_number
                );

                mysqli_stmt_execute(
                    $reference_check
                );

                $reference_result =
                    mysqli_stmt_get_result(
                        $reference_check
                    );

                $reference_exists =
                    mysqli_num_rows(
                        $reference_result
                    ) > 0;

                mysqli_stmt_close(
                    $reference_check
                );

            } while ($reference_exists);


            /*
            |--------------------------------------------------------------------------
            | CREATE RESERVATION
            |--------------------------------------------------------------------------
            */

            $reservation_stmt =
                mysqli_prepare(
                    $conn,
                    "INSERT INTO reservations
                    (
                        reference_number,
                        user_id,
                        customer_id,
                        vehicle_id,
                        service_type,
                        appointment_date,
                        appointment_time,
                        remarks,
                        status,
                        mechanic_id
                    )

                    VALUES
                    (
                        ?,
                        NULL,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        'Pending',
                        NULL
                    )"
                );


            mysqli_stmt_bind_param(
                $reservation_stmt,
                "siissss",
                $reference_number,
                $customer_id,
                $vehicle_id,
                $selected_service[
                    'service_name'
                ],
                $appointment_date,
                $appointment_time,
                $remarks
            );


            mysqli_stmt_execute(
                $reservation_stmt
            );


            $reservation_id =
                mysqli_insert_id($conn);


            mysqli_stmt_close(
                $reservation_stmt
            );


            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

            mysqli_commit($conn);


            /*
            |--------------------------------------------------------------------------
            | REDIRECT TO CONFIRMATION
            |--------------------------------------------------------------------------
            */

            header(
                "Location: "
                . BASE_URL
                . "/appointment_success.php?ref="
                . urlencode($reference_number)
            );

            exit;


        } catch (Throwable $e) {

            mysqli_rollback($conn);

            $error =
                "We were unable to create your appointment. Please try again.";

        }

    }

}

?>

<?php

include "includes/public_header.php";?>


<section class="appointment-page">

    <div class="container">


        <div class="appointment-heading">

            <span class="section-label">
                BOOK ONLINE
            </span>

            <h1>
                Make an Appointment
            </h1>

            <p>
                Tell us about yourself, your vehicle and the
                service you need.
            </p>

        </div>


        <?php if ($error !== ''): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error); ?>

        </div>

        <?php endif; ?>


        <form method="POST" class="appointment-form">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">


            <!-- CUSTOMER -->

            <div class="appointment-section">

                <div class="appointment-section-number">
                    01
                </div>

                <div class="appointment-section-heading">

                    <h2>
                        Your Information
                    </h2>

                    <p>
                        We'll use these details to identify
                        and contact you about your appointment.
                    </p>

                </div>


                <div class="row g-4">


                    <div class="col-md-6">

                        <label class="form-label">
                            Full Name *
                        </label>

                        <input type="text" name="fullname" class="form-control"
                            value="<?= htmlspecialchars($fullname); ?>" required>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Email Address *
                        </label>

                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email); ?>"
                            required>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Phone Number *
                        </label>

                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone); ?>"
                            required>

                    </div>


                </div>

            </div>



            <!-- VEHICLE -->

            <div class="appointment-section">

                <div class="appointment-section-number">
                    02
                </div>


                <div class="appointment-section-heading">

                    <h2>
                        Vehicle Information
                    </h2>

                    <p>
                        Tell us which vehicle will be serviced.
                    </p>

                </div>


                <div class="row g-4">


                    <div class="col-md-4">

                        <label class="form-label">
                            Brand *
                        </label>

                        <input type="text" name="brand" class="form-control" placeholder="Toyota"
                            value="<?= htmlspecialchars($brand); ?>" required>

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Model *
                        </label>

                        <input type="text" name="model" class="form-control" placeholder="Vios"
                            value="<?= htmlspecialchars($model); ?>" required>

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Year *
                        </label>

                        <input type="number" name="year" class="form-control" min="1900" max="<?= date('Y') + 1; ?>"
                            value="<?= htmlspecialchars($year); ?>" required>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Plate Number *
                        </label>

                        <input type="text" name="plate_number" class="form-control" placeholder="ABC-1234"
                            value="<?= htmlspecialchars($plate_number); ?>" required>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Color
                        </label>

                        <input type="text" name="color" class="form-control" placeholder="White"
                            value="<?= htmlspecialchars($color); ?>">

                    </div>


                </div>

            </div>



            <!-- SERVICE -->

            <div class="appointment-section">

                <div class="appointment-section-number">
                    03
                </div>


                <div class="appointment-section-heading">

                    <h2>
                        Choose a Service
                    </h2>

                    <p>
                        Select the service your vehicle needs.
                    </p>

                </div>


                <select name="service_id" class="form-select form-select-lg" required>

                    <option value="">
                        Select a Service
                    </option>


                    <?php while (
                        $service =
                            mysqli_fetch_assoc(
                                $services_query
                            )
                    ): ?>

                    <option value="<?= $service['id']; ?>" <?= $service_id ==
                                $service['id']
                                ? 'selected'
                                : ''; ?>>

                        <?= htmlspecialchars(
                                $service[
                                    'service_name'
                                ]
                            ); ?>

                        —

                        ₱<?= number_format(
                                $service['price'],
                                2
                            ); ?>

                        <?php if (
                                !empty(
                                    $service[
                                        'estimated_duration'
                                    ]
                                )
                            ): ?>

                        (
                        <?= intval(
                                    $service[
                                        'estimated_duration'
                                    ]
                                ); ?>
                        mins)

                        <?php endif; ?>

                    </option>

                    <?php endwhile; ?>


                </select>

            </div>



            <!-- SCHEDULE -->

            <div class="appointment-section">

                <div class="appointment-section-number">
                    04
                </div>


                <div class="appointment-section-heading">

                    <h2>
                        Appointment Schedule
                    </h2>

                    <p>
                        Select your preferred appointment date
                        and time.
                    </p>

                </div>


                <div class="row g-4">


                    <div class="col-md-6">

                        <label class="form-label">
                            Appointment Date *
                        </label>

                        <input type="date" name="appointment_date" class="form-control" min="<?= date('Y-m-d'); ?>"
                            value="<?= htmlspecialchars(
                                $appointment_date
                            ); ?>" required>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Appointment Time *
                        </label>

                        <select name="appointment_time" class="form-select" required>

                            <option value="">
                                Select Time
                            </option>

                            <?php
                                $time_slots = [
                                '08:00' => '8:00 AM',
                                '08:30' => '8:30 AM',
                                '09:00' => '9:00 AM',
                                '09:30' => '9:30 AM',
                                '10:00' => '10:00 AM',
                                '10:30' => '10:30 AM',
                                '11:00' => '11:00 AM',
                                '11:30' => '11:30 AM',
                                '12:00' => '12:00 PM',
                                '12:30' => '12:30 PM',
                                '13:00' => '1:00 PM',
                                '13:30' => '1:30 PM',
                                '14:00' => '2:00 PM',
                                '14:30' => '2:30 PM',
                                '15:00' => '3:00 PM',
                                '15:30' => '3:30 PM',
                                '16:00' => '4:00 PM',
                                '16:30' => '4:30 PM',
                                '17:00' => '5:00 PM'
                            ];


                        foreach (
                            $time_slots as
                            $value => $label
                        ):

                        ?>

                            <option value="<?= $value; ?>" <?= $appointment_time === $value
                            ? 'selected'
                            : ''; 
                            ?>>

                                <?= $label; ?>

                            </option>

                            <?php endforeach; ?>

                        </select>


                        <small class="text-muted">
                            Available from 8:00 AM to 5:00 PM.
                        </small>

                    </div>


                    <div class="col-12">

                        <label class="form-label">
                            Vehicle Concern / Remarks
                        </label>

                        <textarea name="remarks" class="form-control" rows="5"
                            placeholder="Describe any concern, unusual noise, symptoms or additional information..."><?= htmlspecialchars($remarks); ?></textarea>

                    </div>


                </div>

            </div>



            <!-- SUBMIT -->

            <div class="appointment-submit">

                <div>

                    <strong>
                        Ready to book?
                    </strong>

                    <p>
                        Your appointment will initially be
                        submitted as Pending for staff review.
                    </p>

                </div>


                <button type="submit" class="appointment-submit-button">

                    CONFIRM APPOINTMENT →

                </button>

            </div>


        </form>


    </div>

</section>


<?php

include "includes/public_footer.php";

?>