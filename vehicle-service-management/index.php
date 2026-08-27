<?php

include "includes/public_header.php";


/*
|--------------------------------------------------------------------------
| ACTIVE SERVICES
|--------------------------------------------------------------------------
*/

$services = mysqli_query(
    $conn,
    "SELECT
        id,
        service_name,
        description,
        price,
        estimated_duration

     FROM services

     WHERE status = 'Active'

     ORDER BY service_name ASC

     LIMIT 6"
);

?>


<!-- HERO -->

<section class="home-hero">

    <div class="hero-overlay"></div>

    <div class="container hero-content">

        <div class="hero-copy">

            <span class="hero-eyebrow">
                COMPLETE VEHICLE CARE
                Vehicle Managemnet System
            </span>

            <h1>

                Professional Vehicle Care
                <span>You Can Trust.</span>

            </h1>


            <p>

                Reliable maintenance, professional
                mechanics and convenient online
                appointment scheduling.

            </p>


            <div class="hero-actions">

                <a href="appointment.php" class="hero-primary-button">
                    MAKE AN APPOINTMENT
                </a>


                <a href="services.php" class="hero-secondary-button">
                    VIEW SERVICES
                </a>

            </div>

        </div>

    </div>

</section>



<!-- QUICK APPOINTMENT -->

<section class="quick-book-section">

    <div class="container">

        <div class="quick-book-card">

            <div>

                <span class="section-label">
                    NEED A SERVICE?
                </span>

                <h2>
                    Book your vehicle appointment today.
                </h2>

            </div>


            <a href="appointment.php" class="quick-book-button">
                BOOK NOW →
            </a>

        </div>

    </div>

</section>



<!-- SERVICES -->

<section class="home-services">

    <div class="container">

        <div class="section-heading">

            <span class="section-label">
                WHAT WE DO
            </span>

            <h2>
                Our Vehicle Services
            </h2>

            <p>
                Professional automotive maintenance
                for your everyday driving needs.
            </p>

        </div>


        <div class="row g-4">


            <?php while (
                $service = mysqli_fetch_assoc($services)
            ): ?>


            <div class="col-md-6 col-lg-4">

                <div class="service-card">

                    <div class="service-icon">
                        🔧
                    </div>


                    <h3>

                        <?= htmlspecialchars(
                                $service['service_name']
                            ); ?>

                    </h3>


                    <p>

                        <?= htmlspecialchars(
                                $service['description']
                                ?: 'Professional vehicle service and maintenance.'
                            ); ?>

                    </p>


                    <div class="service-meta">

                        <strong>

                            Starts at
                            ₱<?= number_format(
                                    $service['price'],
                                    2
                                ); ?>

                        </strong>


                        <?php if (
                                !empty(
                                    $service['estimated_duration']
                                )
                            ): ?>

                        <span>

                            <?= intval(
                                        $service['estimated_duration']
                                    ); ?>
                            mins

                        </span>

                        <?php endif; ?>

                    </div>


                    <a href="appointment.php?service=<?= $service['id']; ?>" class="service-book-link">
                        BOOK THIS SERVICE →
                    </a>

                </div>

            </div>


            <?php endwhile; ?>


        </div>


        <div class="text-center mt-5">

            <a href="services.php" class="view-all-services">
                View All Services
            </a>

        </div>

    </div>

</section>



<!-- WHY US -->

<section class="why-us-section">

    <div class="container">

        <div class="row align-items-center g-5">


            <div class="col-lg-6">

                <span class="section-label">
                    WHY CHOOSE US
                </span>

                <h2>
                    Vehicle care made simple.
                </h2>


                <p class="why-description">

                    From preventive maintenance to
                    diagnostics and repairs, our goal is
                    to provide convenient and dependable
                    vehicle service.

                </p>


                <div class="why-grid">


                    <div class="why-item">

                        <span>✓</span>

                        <div>

                            <strong>
                                Professional Service
                            </strong>

                            <p>
                                Skilled vehicle maintenance
                                and repair.
                            </p>

                        </div>

                    </div>


                    <div class="why-item">

                        <span>✓</span>

                        <div>

                            <strong>
                                Transparent Pricing
                            </strong>

                            <p>
                                Review available services
                                and estimated prices.
                            </p>

                        </div>

                    </div>


                    <div class="why-item">

                        <span>✓</span>

                        <div>

                            <strong>
                                Easy Scheduling
                            </strong>

                            <p>
                                Schedule your appointment
                                online in minutes.
                            </p>

                        </div>

                    </div>


                    <div class="why-item">

                        <span>✓</span>

                        <div>

                            <strong>
                                Track Your Service
                            </strong>

                            <p>
                                Check your appointment
                                status using your reference
                                number.
                            </p>

                        </div>

                    </div>


                </div>

            </div>


            <div class="col-lg-6">

                <img src="images/cover.jpg" alt="Vehicle Service" class="why-image">

            </div>


        </div>

    </div>

</section>



<!-- CTA -->

<section class="booking-cta">

    <div class="container text-center">

        <span class="section-label">
            READY TO GET STARTED?
        </span>

        <h2>
            Your vehicle deserves proper care.
        </h2>

        <p>
            Schedule your service appointment today.
        </p>

        <a href="appointment.php" class="cta-button">
            MAKE AN APPOINTMENT
        </a>

    </div>

</section>


<?php

include "includes/public_footer.php";

?>