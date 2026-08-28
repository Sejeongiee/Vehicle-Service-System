<?php

include "includes/public_header.php";


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

     ORDER BY service_name ASC"
);

?>


<section class="inner-hero">

    <div class="container">

        <span class="section-label">
            OUR SERVICES
        </span>

        <h1>
            Professional Vehicle Services
        </h1>

        <p>
            Choose the service your vehicle needs and schedule
            your appointment online.
        </p>

    </div>

</section>


<section class="services-page">

    <div class="container">


        <?php if (
            $services &&
            mysqli_num_rows($services) > 0
        ): ?>


        <div class="row g-4">


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
                                        : 'Professional vehicle maintenance and service.'
                                ); ?>

                    </p>


                    <div class="service-meta">


                        <strong>

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

                            Approx.

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


        </div>


        <?php else: ?>


        <div class="alert alert-light text-center">

            No active services are currently available.

        </div>


        <?php endif; ?>


    </div>

</section>


<?php

include "includes/public_footer.php";

?>