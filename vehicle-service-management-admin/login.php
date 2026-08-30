<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


include "includes/config.php";


/*
|--------------------------------------------------------------------------
| ALREADY LOGGED IN
|--------------------------------------------------------------------------
*/

if (
    isset($_SESSION['admin_id']) &&
    isset($_SESSION['admin_role']) &&
    $_SESSION['admin_role'] === 'staff'
) {

    header(
        "Location: "
        . ADMIN_BASE_URL
        . "/dashboard.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['admin_csrf_token'])) {

    $_SESSION['admin_csrf_token'] =
        bin2hex(random_bytes(32));

}


$error = "";


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $csrf_token =
        $_POST['csrf_token'] ?? '';


    if (
        empty($csrf_token) ||
        !hash_equals(
            $_SESSION['admin_csrf_token'],
            $csrf_token
        )
    ) {

        $error =
            "Invalid login request. Please refresh the page.";

    }


    $email =
        strtolower(
            trim($_POST['email'] ?? '')
        );


    $password =
        $_POST['password'] ?? '';


    if (
        $error === '' &&
        (
            $email === '' ||
            $password === ''
        )
    ) {

        $error =
            "Please enter your email and password.";

    }


    if ($error === '') {


        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                id,
                fullname,
                email,
                password,
                role

             FROM users

             WHERE email = ?
             AND role = 'staff'

             LIMIT 1"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );


        mysqli_stmt_execute($stmt);


        $result =
            mysqli_stmt_get_result($stmt);


        $user =
            mysqli_fetch_assoc($result);


        if (
            $user &&
            password_verify(
                $password,
                $user['password']
            )
        ) {


            session_regenerate_id(true);


            $_SESSION['admin_id'] =
                intval($user['id']);


            $_SESSION['admin_name'] =
                $user['fullname'];


            $_SESSION['admin_role'] =
                $user['role'];


            /*
            | Regenerate CSRF token
            */

            $_SESSION['admin_csrf_token'] =
                bin2hex(random_bytes(32));


            mysqli_stmt_close($stmt);


            header(
                "Location: "
                . ADMIN_BASE_URL
                . "/dashboard.php"
            );


            exit;

        }


        mysqli_stmt_close($stmt);


        $error =
            "Invalid staff email or password.";

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Vehicle Service Management Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="css/admin.css">

</head>

<body class="admin-login-page">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-5">

                <div class="admin-login-card">

                    <div class="text-center mb-4">

                        <h2>
                            Vehicle Service
                        </h2>

                        <h4>
                            Management Admin
                        </h4>

                        <p class="text-muted">
                            Staff Portal
                        </p>

                    </div>

                    <?php if (!empty($error)): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error); ?>
                    </div>

                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label">
                                Staff Email
                            </label>

                            <input type="email" name="email" class="form-control" required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input type="password" name="password" class="form-control" required>

                        </div>

                        <button type="submit" name="login" class="btn btn-primary w-100">

                            Staff Login

                        </button>

                    </form>

                    <div class="text-center mt-4">

                        <a href="http://localhost/vehicle-service-management/">

                            ← Customer Website

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>