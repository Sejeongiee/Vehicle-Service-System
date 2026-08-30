<?php

include_once __DIR__ . "/config.php";
include_once __DIR__ . "/admin_auth.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Vehicle Service Management Admin
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="css/admin.css">

</head>

<body>

    <div class="admin-wrapper">

        <aside class="sidebar">

            <div class="sidebar-logo">

                🚗 VSMS Admin

            </div>

            <ul class="sidebar-menu">

                <li>
                    <a href="<?= ADMIN_BASE_URL ?>/dashboard.php">
                        📊 Dashboard
                    </a>
                </li>

                <li>
                    <a href="<?= ADMIN_BASE_URL ?>/reservations.php">
                        📅 Reservations
                    </a>
                </li>

                <li>
                    <a href="<?= ADMIN_BASE_URL ?>/customers.php">
                        👥 Customers
                    </a>
                </li>

                <li>
                    <a href="<?= ADMIN_BASE_URL ?>/mechanics.php">
                        🔧 Mechanics
                    </a>
                </li>

                <li>
                    <a href="<?= ADMIN_BASE_URL ?>/services.php">
                        🛠 Services
                    </a>
                </li>

                <li>
                    <a href="<?= ADMIN_BASE_URL ?>/payments.php">
                        💳 Payments
                    </a>
                </li>

                <li>
                    <a href="<?= ADMIN_BASE_URL ?>/reports.php">
                        📈 Reports
                    </a>
                </li>

                <li>
                    <a href="<?= ADMIN_BASE_URL ?>/logout.php">
                        🚪 Logout
                    </a>
                </li>

            </ul>

        </aside>

        <main class="admin-main">

            <header class="topbar">

                <div>

                    <strong>
                        Vehicle Service Management Admin
                    </strong>

                </div>

                <div>

                    Welcome,
                    <?= htmlspecialchars($_SESSION['admin_name']); ?>

                </div>

            </header>

            <div class="admin-content">