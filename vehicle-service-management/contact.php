<?php

include "includes/public_header.php";

?>


<section class="inner-hero">

    <div class="container">

        <span class="section-label">
            CONTACT
        </span>

        <h1>
            How Can We Help?
        </h1>

        <p>
            Have questions about your appointment or vehicle
            service? Get in touch with our service team.
        </p>

    </div>

</section>



<section class="contact-page">

    <div class="container">

        <div class="row g-5">


            <div class="col-lg-5">


                <span class="section-label">
                    GET IN TOUCH
                </span>


                <h2>
                    Need help with your vehicle service?
                </h2>


                <p class="contact-description">

                    For appointment concerns, prepare your
                    appointment reference number so our staff
                    can quickly locate your reservation.

                </p>



                <div class="contact-info-card">


                    <div class="contact-info-item">

                        <div class="contact-icon">
                            📅
                        </div>

                        <div>

                            <strong>
                                Existing Appointment
                            </strong>

                            <p>
                                Check your current appointment
                                status online.
                            </p>

                            <a href="<?= BASE_URL ?>/track.php">
                                Track Appointment →
                            </a>

                        </div>

                    </div>



                    <div class="contact-info-item">

                        <div class="contact-icon">
                            🔧
                        </div>

                        <div>

                            <strong>
                                Need Vehicle Service?
                            </strong>

                            <p>
                                Schedule a new service
                                appointment online.
                            </p>

                            <a href="<?= BASE_URL ?>/appointment.php">
                                Make an Appointment →
                            </a>

                        </div>

                    </div>


                </div>


            </div>



            <div class="col-lg-7">

                <div class="contact-message-card">


                    <h3>
                        Service Inquiry
                    </h3>


                    <p>

                        For now, appointment inquiries can be
                        handled through your appointment
                        reference and the Track Appointment
                        page.

                    </p>


                    <div class="contact-reference-box">

                        <span>
                            Already booked?
                        </span>

                        <strong>
                            Have your VSMS reference ready.
                        </strong>

                        <a href="<?= BASE_URL ?>/track.php">
                            TRACK MY APPOINTMENT
                        </a>

                    </div>


                </div>

            </div>


        </div>

    </div>

</section>


<?php

include "includes/public_footer.php";

?>