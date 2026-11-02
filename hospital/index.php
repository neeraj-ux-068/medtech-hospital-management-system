<?php
include_once('hms/include/config.php');
hms_start_session();
if (isset($_POST['submit'])) {
    hms_verify_csrf();
    $name = hms_post_string('fullname', 150);
    $email = filter_var(hms_post_string('emailid', 254), FILTER_VALIDATE_EMAIL);
    $mobileno = preg_replace('/[^0-9+() -]/', '', hms_post_string('mobileno', 24));
    $description = hms_post_string('description', 4000);
    if ($name !== '' && $email && $mobileno !== '' && $description !== '') {
        hms_db_query($con, 'INSERT INTO tblcontactus (fullname, email, contactno, message) VALUES (?, ?, ?, ?)', [$name, $email, preg_replace('/\D/', '', $mobileno), $description]);
        header('Location: index.php?contact=sent#contact'); exit;
    }
}
/** Optional landing-page data must not make the public site unavailable. */
$publicQuery = static function (string $sql) use ($con) {
    try {
        return hms_db_query($con, $sql);
    } catch (Throwable $exception) {
        error_log('HMS landing-page optional query failed: ' . $exception->getMessage());
        return null;
    }
};

$publicStats = [];
foreach ([['patients','users'], ['doctors','doctors'], ['appointments','appointment']] as [$label, $table]) {
    $stat = $publicQuery('SELECT COUNT(*) AS total FROM ' . $table);
    $publicStats[$label] = $stat ? (int)($stat->fetch_assoc()['total'] ?? 0) : 0;
}
$featuredSpecialties = $publicQuery("SELECT DISTINCT specilization FROM doctors WHERE specilization IS NOT NULL AND specilization <> '' ORDER BY specilization LIMIT 6");
$aboutPage = hms_db_query($con, "SELECT PageDescription FROM tblpage WHERE PageType = 'aboutus' LIMIT 1");
$aboutContent = $aboutPage ? $aboutPage->fetch_assoc() : [];
$contactPage = hms_db_query($con, "SELECT PageDescription, MobileNumber, Email, OpenningTime FROM tblpage WHERE PageType = 'contactus' LIMIT 1");
$contactContent = $contactPage ? $contactPage->fetch_assoc() : [];
?>
<!doctype html>
<html lang="en"><head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MedTech Hospital | Care, coordinated</title>
  <link rel="shortcut icon" href="assets/images/fav.jpg">
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/fontawsom-all.min.css">
  <link rel="stylesheet" href="assets/css/medtech-ui.css">
</head><body class="mt-home">
<header class="mt-header"><nav class="mt-nav container" aria-label="Main navigation">
  <a class="mt-brand" href="#home"><span class="mt-brand-mark"><i class="fas fa-plus"></i></span><span>MedTech<small>HOSPITAL</small></span></a>
  <button class="mt-nav-toggle" type="button" aria-expanded="false" aria-controls="mt-menu" aria-label="Open navigation"><i class="fas fa-bars"></i></button>
  <div class="mt-menu" id="mt-menu"><a class="is-active" href="#home">Home</a><a href="#services">Services</a><a href="#about">About</a><a href="#contact">Contact</a><a class="mt-login" href="hms/user-login.php">Sign in</a><a class="mt-button mt-button-primary" href="hms/user-login.php">Book appointment <i class="fas fa-arrow-right"></i></a></div>
</nav></header>
<main id="home">
  <section class="mt-hero"><div class="container mt-hero-grid"><div class="mt-hero-copy"><span class="mt-kicker"><i class="fas fa-heartbeat"></i> A BETTER WAY TO CARE</span><h1>Healthcare management,<br><em>made human.</em></h1><p>Connect patients, care teams and hospital operations in one simple, secure place.</p><div class="mt-hero-actions"><a class="mt-button mt-button-primary" href="hms/user-login.php">Book an appointment <i class="fas fa-arrow-right"></i></a><a class="mt-button mt-button-quiet" href="#services">Explore services</a></div><div class="mt-proof"><span class="mt-proof-icon"><i class="fas fa-shield"></i></span><span><strong>Care with confidence</strong><small>One connected experience for every role</small></span></div></div>
  <div class="mt-hero-art"><div class="mt-visual-frame"><img src="assets/images/doctor.jpg" alt="Doctor ready to support your care"><div class="mt-visual-overlay"><span class="mt-live-dot"></span><span><strong>Connected care</strong><small>Patients · Doctors · Hospital</small></span><i class="fas fa-external-link"></i></div></div><div class="mt-floating-note"><i class="fas fa-calendar-check-o"></i><span><strong>Care, on schedule</strong><small>Appointments made simple</small></span></div><div class="mt-hero-orbit"></div></div></div></section>
  <section class="mt-stats"><div class="container mt-stats-grid"><div><strong><?= number_format($publicStats['patients']) ?></strong><span>Patient accounts</span></div><div><strong><?= number_format($publicStats['doctors']) ?></strong><span>Care professionals</span></div><div><strong><?= number_format($publicStats['appointments']) ?></strong><span>Appointments managed</span></div><div class="mt-stats-note"><i class="fas fa-lock"></i><span>Privacy-minded tools<br>for modern care teams</span></div></div></section>
  <section class="mt-section" id="services"><div class="container"><div class="mt-section-head"><div><span class="mt-kicker">ONE CONNECTED PLATFORM</span><h2>Everything your care journey needs</h2></div><p>Purpose-built tools keep daily hospital workflows organized and easy to access.</p></div><div class="mt-service-grid">
  <?php $services = [['calendar-check-o','Appointment management','Find a time, request a visit and keep track of what comes next.'],['file-text-o','Patient records','Keep essential patient information and clinical history together.'],['user-md','Doctor management','Support doctor profiles, schedules and patient care.'],['medkit','Prescriptions','Give care teams a clear place to manage treatment details.'],['heartbeat','Medical history','Review relevant health information across visits.'],['bar-chart','Hospital administration','Coordinate users, appointments and everyday operations.']]; foreach ($services as $i=>$service) { ?><article class="mt-service-card"><span class="mt-service-icon mt-icon-<?= $i+1 ?>"><i class="fas fa-<?= hms_h($service[0]) ?>"></i></span><h3><?= hms_h($service[1]) ?></h3><p><?= hms_h($service[2]) ?></p><a href="hms/user-login.php" aria-label="Open <?= hms_h($service[1]) ?>">Explore <i class="fas fa-arrow-right"></i></a></article><?php } ?>
  </div><?php if ($featuredSpecialties && $featuredSpecialties->num_rows > 0) { ?><div class="mt-specialty-strip"><strong>Care specialties</strong><div><?php while ($specialty = $featuredSpecialties->fetch_assoc()) { ?><span><?= hms_h($specialty['specilization']) ?></span><?php } ?></div></div><?php } ?></div></section>
  <section class="mt-how"><div class="container"><div class="mt-section-head"><div><span class="mt-kicker">GETTING STARTED</span><h2>Your next step is simple</h2></div><p>From first sign-in to a scheduled visit, the essentials are easy to find.</p></div><div class="mt-steps"><article><span>01</span><h3>Create your account</h3><p>Register securely as a patient to access your care portal.</p></article><article><span>02</span><h3>Choose your care</h3><p>Explore available specialties and select a doctor.</p></article><article><span>03</span><h3>Request a visit</h3><p>Choose an available date and time that works for you.</p></article><article><span>04</span><h3>Stay connected</h3><p>Review appointments and the information linked to your care.</p></article></div></div></section>
  <section class="mt-about" id="about"><div class="container mt-about-grid"><div class="mt-about-visual"><img src="assets/images/gallery/gallery_04.jpg" alt="Doctor ready to welcome a patient"><span><i class="fas fa-heart"></i> People first, always</span></div><div><span class="mt-kicker">WHY MEDTECH</span><h2>Better coordination.<br><em>More time for care.</em></h2><p>MedTech brings the everyday work of a hospital into one thoughtful digital experience—so patients, doctors and administrators can focus on the next right step.</p><?php if (!empty($aboutContent['PageDescription'])) { ?><div class="mt-about-existing"><?= hms_rich_text($aboutContent['PageDescription']) ?></div><?php } ?><ul class="mt-benefits"><li><i class="fas fa-check-circle"></i> Secure patient access and role-specific portals</li><li><i class="fas fa-check-circle"></i> Straightforward appointment scheduling</li><li><i class="fas fa-check-circle"></i> Centralized clinical and hospital workflows</li></ul><a class="mt-text-link" href="#portals">Explore the portals <i class="fas fa-arrow-right"></i></a></div></div></section>
  <section class="mt-portals" id="portals"><div class="container mt-portals-inner"><div><span class="mt-kicker">ONE HOSPITAL, CONNECTED</span><h2>Tools for every care role</h2><p>Sign in to the workspace made for you.</p></div><div class="mt-portal-links"><a href="hms/user-login.php"><span><i class="fas fa-user"></i></span><strong>Patient portal</strong><small>Appointments and personal care</small><i class="fas fa-arrow-right"></i></a><a href="hms/doctor/index.php"><span><i class="fas fa-user-md"></i></span><strong>Doctor portal</strong><small>Patients and appointment schedule</small><i class="fas fa-arrow-right"></i></a><a href="hms/admin/index.php"><span><i class="fas fa-hospital-o"></i></span><strong>Admin portal</strong><small>Hospital operations and oversight</small><i class="fas fa-arrow-right"></i></a></div></div></section>
  <section class="mt-cta"><div class="container mt-cta-inner"><div><span class="mt-kicker">YOUR CARE, IN ONE PLACE</span><h2>Ready to take the next step?</h2><p>Sign in to request an appointment and stay connected with your care team.</p></div><a class="mt-button mt-button-light" href="hms/user-login.php">Book an appointment <i class="fas fa-arrow-right"></i></a></div></section>
  <section class="mt-contact" id="contact"><div class="container mt-contact-grid"><div><span class="mt-kicker">GET IN TOUCH</span><h2>How can we help?</h2><p>Send our team a message and we’ll help you find the right next step.</p></div><div><?php if (isset($_GET['contact']) && $_GET['contact']==='sent') { ?><div class="mt-notice" role="status">Thank you. Your message has been sent to our team.</div><?php } ?><?php if (isset($_POST['submit']) && (empty(filter_var($_POST['emailid'] ?? '', FILTER_VALIDATE_EMAIL)) || trim((string)($_POST['fullname'] ?? ''))==='' || trim((string)($_POST['mobileno'] ?? ''))==='' || trim((string)($_POST['description'] ?? ''))==='')) { ?><div class="mt-error" role="alert">Please check your details and try again.</div><?php } ?><form class="mt-form" method="post"><?= hms_csrf_field() ?><div class="mt-form-row"><label>Your name<input name="fullname" autocomplete="name" required></label><label>Email address<input type="email" name="emailid" autocomplete="email" required></label></div><label>Phone number<input type="tel" name="mobileno" autocomplete="tel" required></label><label>Message<textarea name="description" rows="4" required></textarea></label><button class="mt-button mt-button-primary" type="submit" name="submit">Send message <i class="fas fa-arrow-right"></i></button></form></div></div></section>
</main>
<footer class="mt-footer"><div class="container mt-footer-grid"><a class="mt-brand" href="#home"><span class="mt-brand-mark"><i class="fas fa-plus"></i></span><span>MedTech<small>HOSPITAL</small></span></a><p>Thoughtful tools for connected hospital care.</p><div><a href="#services">Services</a><a href="#about">About</a><a href="#portals">Portals</a><a href="#contact">Contact</a></div><?php if (!empty($contactContent)) { ?><p class="mt-footer-contact"><?= hms_h($contactContent['MobileNumber'] ?? '') ?> · <a href="mailto:<?= rawurlencode((string)($contactContent['Email'] ?? '')) ?>"><?= hms_h($contactContent['Email'] ?? '') ?></a><?php if (!empty($contactContent['OpenningTime'])) { ?> · <?= hms_h($contactContent['OpenningTime']) ?><?php } ?></p><?php } ?><small>© <?= date('Y') ?> MedTech Hospital Management System</small></div></footer>
<script src="assets/js/jquery-3.2.1.min.js"></script><script src="assets/js/bootstrap.min.js"></script><script src="assets/js/medtech.js"></script>
</body></html>
