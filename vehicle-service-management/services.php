<?php
include "includes/public_header.php";

$services = mysqli_query(
    $conn,
    "SELECT id, service_name, description, price, estimated_duration
     FROM services
     WHERE status = 'Active'
     ORDER BY service_name ASC"
);
?>

<section class="home-services" style="padding-top:90px; min-height:70vh;">
    <div class="container">
        <div class="section-heading">
            <span class="section-label">OUR SERVICES</span>
            <h1>Vehicle Care & Maintenance</h1>
            <p>Choose the service your vehicle needs and schedule an appointment online.</p>
        </div>

        <div class="row g-4">
            <?php if ($services && mysqli_num_rows($services) > 0): ?>
            <?php while ($service = mysqli_fetch_assoc($services)): ?>
            <div class="col-md-6 col-lg-4">
                <div class="service-card h-100">
                    <div class="service-icon">🔧</div>
                    <h3><?= htmlspecialchars($service['service_name']); ?></h3>
                    <p><?= htmlspecialchars($service['description'] ?: 'Professional vehicle service and maintenance.'); ?>
                    </p>
                    <div class="service-meta">
                        <strong>Starts at ₱<?= number_format((float)$service['price'], 2); ?></strong>
                        <?php if (!empty($service['estimated_duration'])): ?>
                        <span><?= intval($service['estimated_duration']); ?> mins</span>
                        <?php endif; ?>
                    </div>
                    <a href="appointment.php?service=<?= intval($service['id']); ?>" class="service-book-link">BOOK THIS
                        SERVICE →</a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div class="col-12">
                <div class="alert alert-secondary">No active services are available right now.</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include "includes/public_footer.php"; ?>