<?php

include_once __DIR__ . "/config.php";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Vehicle Service Management
    </title>


    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- Google Font -->

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">


    <!-- Public CSS -->

    <link rel="stylesheet" href="<?= BASE_URL ?>/css/public.css">

</head>


<body>


    <nav class="public-navbar">

    <div class="container">

        <div class="navbar-content">

            <!-- LOGO + TITLE -->
            <a href="<?= BASE_URL ?>/index.php" class="public-logo">

                <img src="<?= BASE_URL ?>/images/logo.png"
                     alt="Lucky Yuna Logo"
                     style="width: 45px; height: 45px; object-fit: contain;">

                <span>
                    Lucky Yuna Car Care Center
                </span>

            </a>


                <!-- MOBILE BUTTON -->

                <button class="mobile-menu-button" id="mobileMenuButton" type="button" aria-label="Open navigation">

                    ☰

                </button>


                <!-- NAVIGATION -->

                <div class="public-nav-links" id="publicNavLinks">

                    <a href="<?= BASE_URL ?>/index.php">
                        Home
                    </a>


                    <a href="<?= BASE_URL ?>/services.php">
                        Services
                    </a>


                    <a href="<?= BASE_URL ?>/about.php">
                        About Us
                    </a>


                    <a href="<?= BASE_URL ?>/contact.php">
                        Contact
                    </a>


                    <a href="<?= BASE_URL ?>/track.php">
                        Track Appointment
                    </a>


                    <a href="<?= BASE_URL ?>/appointment.php" class="book-nav-button">
                        BOOK NOW
                    </a>

                </div>

            </div>

        </div>

    </nav>