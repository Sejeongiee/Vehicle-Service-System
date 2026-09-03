<?php

include "includes/public_header.php";


/*
|--------------------------------------------------------------------------
| LOAD ACTIVE SERVICES
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


<!-- ============================================
     HERO
============================================= -->

<section class="home-hero">

    <div class="hero-overlay"></div>


    <div class="container hero-content">

        <div class="hero-copy">


            <span class="hero-eyebrow">
                COMPLETE VEHICLE CARE
            </span>


            <h1>

                Professional Vehicle Care

                <span>
                    You Can Trust.
                </span>

            </h1>


            <p>

                We are your one stop shop for your car's maintenance and repairs. 
                Our accommodating head mechanic, Lucky, specializes on all car makes and models.

            </p>


            <div class="hero-actions">


                <a href="<?= BASE_URL ?>/appointment.php" class="hero-primary-button">
                    MAKE AN APPOINTMENT
                </a>


                <a href="<?= BASE_URL ?>/services.php" class="hero-secondary-button">
                    VIEW SERVICES
                </a>


            </div>

        </div>

    </div>

</section>



<!-- ============================================
     QUICK BOOKING
============================================= -->

<section class="quick-book-section">

    <div class="container">

        <div class="quick-book-card">


            <div>

                <span class="section-label">
                    NEED VEHICLE SERVICE?
                </span>

                <h2>
                    Book your vehicle appointment today.
                </h2>

            </div>


            <a href="<?= BASE_URL ?>/appointment.php" class="quick-book-button">
                BOOK NOW →
            </a>


        </div>

    </div>

</section>



<!-- ============================================
     SERVICES
============================================= -->

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
                and service for your everyday
                driving needs.

            </p>

        </div>



        <div class="row g-4">


            <?php if (
                $services &&
                mysqli_num_rows($services) > 0
            ): ?>


            <?php while (
                    $service =
                        mysqli_fetch_assoc($services)
                ): ?>


            <div class="col-md-6 col-lg-4">

                <div class="service-card">


                    <div class="service-icon">
                        🔧
                    </div>


                    <h3>

                        <?= htmlspecialchars(
                                    $service[
                                        'service_name'
                                    ]
                                ); ?>

                    </h3>


                    <p>

                        <?= htmlspecialchars(
                                    !empty(
                                        $service[
                                            'description'
                                        ]
                                    )
                                        ? $service[
                                            'description'
                                        ]
                                        : 'Professional vehicle service and maintenance.'
                                ); ?>

                    </p>


                    <div class="service-meta">


                        <strong>

                            Starts at

                            ₱<?= number_format(
                                        $service[
                                            'price'
                                        ],
                                        2
                                    ); ?>

                        </strong>


                        <?php if (
                                    !empty(
                                        $service[
                                            'estimated_duration'
                                        ]
                                    )
                                ): ?>

                        <span>

                            <?= intval(
                                            $service[
                                                'estimated_duration'
                                            ]
                                        ); ?>

                            mins

                        </span>

                        <?php endif; ?>


                    </div>


                    <a href="<?= BASE_URL ?>/appointment.php?service=<?= intval($service['id']); ?>"
                        class="service-book-link">
                        BOOK THIS SERVICE →
                    </a>


                </div>

            </div>


            <?php endwhile; ?>


            <?php else: ?>


            <div class="col-12">

                <div class="alert alert-light text-center">

                    No active services are currently
                    available.

                </div>

            </div>


            <?php endif; ?>


        </div>



        <div class="text-center mt-5">

            <a href="<?= BASE_URL ?>/services.php" class="view-all-services">
                View All Services
            </a>

        </div>


    </div>

</section>



<!-- ============================================
     WHY CHOOSE US
============================================= -->

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
                    diagnostics and repairs, our system
                    helps make scheduling and monitoring
                    vehicle service convenient.

                </p>



                <div class="why-grid">


                    <div class="why-item">

                        <span>
                            ✓
                        </span>

                        <div>

                            <strong>
                                Professional Service
                            </strong>

                            <p>
                                Reliable vehicle maintenance
                                and repair services.
                            </p>

                        </div>

                    </div>



                    <div class="why-item">

                        <span>
                            ✓
                        </span>

                        <div>

                            <strong>
                                Transparent Pricing
                            </strong>

                            <p>

                                View available services and
                                their estimated prices before
                                booking.

                            </p>

                        </div>

                    </div>



                    <div class="why-item">

                        <span>
                            ✓
                        </span>

                        <div>

                            <strong>
                                Easy Scheduling
                            </strong>

                            <p>

                                Schedule a service appointment
                                online in just a few steps.

                            </p>

                        </div>

                    </div>



                    <div class="why-item">

                        <span>
                            ✓
                        </span>

                        <div>

                            <strong>
                                Track Your Service
                            </strong>

                            <p>

                                Track appointment progress
                                using your unique reference
                                number.

                            </p>

                        </div>

                    </div>


                </div>


            </div>



            <div class="col-lg-6">


                <!--
                    cover.jpg did not exist in your upload.
                    hero.jpg DOES exist, so we'll use it
                    temporarily.
                -->

                <img src="<?= BASE_URL ?>/images/cover.png" alt="Vehicle Service" class="why-image" style="width: 100%; max-width: 700px; height: auto;">


            </div>


        </div>

    </div>

</section>

</section>


<!-- ============================================
     MAKES AND MODELS
============================================= -->

<section class="makes-models-section">

    <div class="container">

        <h2 style="color: #f80303;">YOUR VEHICLE, OUR EXPERTISE</h2>

        <div class="makes-grid">

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/toyota.png" alt="Toyota">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/honda.png" alt="Honda">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/mitsubishi.png" alt="Mitsubishi">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/hyundai.png" alt="Hyundai">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/nissan.png" alt="Nissan">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/kia.png" alt="Kia">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/suzuki.png" alt="Suzuki">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/ford.png" alt="Ford">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/mazda.png" alt="Mazda">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/isuzu.png" alt="Isuzu">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/foton.png" alt="Foton">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/hino.png" alt="Hino">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/volkswagen.png" alt="Volkswagen">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/chevrolet.png" alt="Chevrolet">
            </div>

            <div class="make-logo">
                <img src="<?= BASE_URL ?>/images/subaru.png" alt="Subaru">
            </div>

        </div>
<!--
        <a href="<?= BASE_URL ?>/makes-models.php" class="see-all-makes">
            See All Makes And Models
        </a>
            -->
    </div>

</section>


<!-- ============================================
     FINAL CALL TO ACTION
============================================= -->

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


        <a href="<?= BASE_URL ?>/appointment.php" class="cta-button">
            MAKE AN APPOINTMENT
        </a>


    </div>

</section>


<?php

include "includes/public_footer.php";

?>