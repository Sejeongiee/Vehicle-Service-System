<?php

include "includes/public_header.php";

?>


<section class="inner-hero">

    <div class="container">

        <span class="section-label">
            ABOUT US
        </span>

        <h1>
            Better Vehicle Service Management
        </h1>

        <p>
            Making vehicle maintenance scheduling and service
            monitoring more convenient.
        </p>

    </div>

</section>



<section class="about-page">

    <div class="container">

        <div class="row align-items-center g-5">


            <div class="col-lg-6">

                <span class="section-label">
                    WHO WE ARE
                </span>


                <h2>
                    Convenient vehicle care for every customer.
                </h2>


                <p>

                    The Vehicle Service Management System
                    provides customers with a convenient way
                    to schedule vehicle maintenance and
                    monitor service progress.

                </p>


                <p>

                    Customers can select available services,
                    provide their vehicle information,
                    choose an appointment schedule and track
                    their appointment using a unique reference
                    number.

                </p>


                <p>

                    On the administrative side, staff can
                    manage appointments, mechanics, services,
                    payments and reports from a separate
                    management system.

                </p>


                <a href="<?= BASE_URL ?>/appointment.php" class="about-book-button">

                    MAKE AN APPOINTMENT

                </a>

            </div>



            <div class="col-lg-6">

                <img src="<?= BASE_URL ?>/images/hero.jpg" alt="Vehicle maintenance" class="about-image">

            </div>


        </div>

    </div>

</section>



<section class="about-features">

    <div class="container">

        <div class="row g-4">


            <div class="col-md-4">

                <div class="about-feature-card">

                    <div class="about-feature-number">
                        01
                    </div>

                    <h3>
                        Easy Booking
                    </h3>

                    <p>

                        Customers can schedule an appointment
                        without creating an account.

                    </p>

                </div>

            </div>



            <div class="col-md-4">

                <div class="about-feature-card">

                    <div class="about-feature-number">
                        02
                    </div>

                    <h3>
                        Appointment Tracking
                    </h3>

                    <p>

                        Customers can monitor the status of
                        their appointment using their booking
                        reference.

                    </p>

                </div>

            </div>



            <div class="col-md-4">

                <div class="about-feature-card">

                    <div class="about-feature-number">
                        03
                    </div>

                    <h3>
                        Organized Management
                    </h3>

                    <p>

                        Staff can manage reservations,
                        mechanics, payments and service
                        information in one system.

                    </p>

                </div>

            </div>


        </div>

    </div>

</section>


<?php

include "includes/public_footer.php";

?>