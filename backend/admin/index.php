<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$totalJobs = 0;
$publishedJobs = 0;
$totalApplications = 0;
$newApplications = 0;
$totalLeads = 0;


/*
|--------------------------------------------------------------------------
| TOTAL JOBS
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM jobs"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalJobs = (int)($row["total"] ?? 0);
}


/*
|--------------------------------------------------------------------------
| PUBLISHED JOBS
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM jobs
     WHERE status = 'published'"
);

if ($result) {
    $row = $result->fetch_assoc();
    $publishedJobs = (int)($row["total"] ?? 0);
}


/*
|--------------------------------------------------------------------------
| TOTAL APPLICATIONS
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM applications"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalApplications = (int)($row["total"] ?? 0);
}


/*
|--------------------------------------------------------------------------
| NEW APPLICATIONS
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM applications
     WHERE status = 'new'"
);

if ($result) {
    $row = $result->fetch_assoc();
    $newApplications = (int)($row["total"] ?? 0);
}


/*
|--------------------------------------------------------------------------
| TOTAL LEADS
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM leads"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalLeads = (int)($row["total"] ?? 0);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Aerol Colt Admin Dashboard</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #05050A;
    color: #FFFFFF;
    font-family: Arial, Helvetica, sans-serif;
}

.container {
    max-width: 1200px;
    margin: auto;
    padding: 30px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
}

.brand h1 {
    margin: 0;
    font-size: 28px;
}

.brand p {
    margin: 6px 0 0;
    color: #9CA3AF;
}

.logout {
    display: inline-block;
    padding: 10px 16px;
    border-radius: 8px;
    background: #1E2235;
    color: white;
    text-decoration: none;
    font-size: 14px;
}

.logout:hover {
    background: #2A3048;
}

.welcome {
    margin-bottom: 25px;
    color: #9CA3AF;
}

.cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 35px;
}

.card {
    background: #0F111A;
    border: 1px solid #1E2235;
    border-radius: 14px;
    padding: 25px;
}

.card-title {
    color: #9CA3AF;
    font-size: 14px;
    margin-bottom: 12px;
}

.card-number {
    font-size: 32px;
    font-weight: bold;
}

.actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.action {
    display: block;
    text-decoration: none;
    color: white;
    background: #0F111A;
    border: 1px solid #1E2235;
    border-radius: 14px;
    padding: 25px;
    transition: 0.2s;
}

.action:hover {
    border-color: #4B5563;
    transform: translateY(-2px);
}

.action h2 {
    margin: 0 0 8px;
    font-size: 20px;
}

.action p {
    margin: 0;
    color: #9CA3AF;
    line-height: 1.5;
}

.badge {
    display: inline-block;
    margin-top: 15px;
    padding: 6px 10px;
    background: #1E2235;
    border-radius: 6px;
    font-size: 12px;
}

@media (max-width: 900px) {

    .cards {
        grid-template-columns: repeat(2, 1fr);
    }

    .actions {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 600px) {

    .container {
        padding: 20px;
    }

    .topbar {
        align-items: flex-start;
        gap: 20px;
    }

    .cards {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<div class="container">


<!-- =========================================================
     TOP BAR
========================================================= -->

<div class="topbar">

<div class="brand">

<h1>
Aerol Colt
</h1>

<p>
Admin Dashboard
</p>

</div>

<a
    href="logout.php"
    class="logout"
>
    Logout
</a>

</div>


<!-- =========================================================
     WELCOME
========================================================= -->

<div class="welcome">

Welcome,
<?= htmlspecialchars(
    $_SESSION["admin_username"] ?? "Admin"
) ?>

</div>


<!-- =========================================================
     SUMMARY CARDS
========================================================= -->

<div class="cards">


<div class="card">

<div class="card-title">
Total Jobs
</div>

<div class="card-number">
<?= $totalJobs ?>
</div>

</div>


<div class="card">

<div class="card-title">
Published Jobs
</div>

<div class="card-number">
<?= $publishedJobs ?>
</div>

</div>


<div class="card">

<div class="card-title">
Total Applications
</div>

<div class="card-number">
<?= $totalApplications ?>
</div>

</div>


<div class="card">

<div class="card-title">
New Applications
</div>

<div class="card-number">
<?= $newApplications ?>
</div>

</div>


</div>


<!-- =========================================================
     ADMIN ACTIONS
========================================================= -->

<div class="actions">


<!-- JOBS -->

<a
    href="jobs.php"
    class="action"
>

<h2>
Jobs
</h2>

<p>
Create, edit, publish and manage
Aerol Colt job vacancies.
</p>

<span class="badge">
<?= $totalJobs ?> jobs
</span>

</a>


<!-- APPLICATIONS -->

<a
    href="applications.php"
    class="action"
>

<h2>
Applications
</h2>

<p>
Review candidates, CVs, match scores
and application statuses.
</p>

<span class="badge">
<?= $newApplications ?> new
</span>

</a>


<!-- LEADS -->

<a
    href="leads.php"
    class="action"
>

<h2>
Leads
</h2>

<p>
View and manage lead submissions
received through the website.
</p>

<span class="badge">
<?= $totalLeads ?> leads
</span>

</a>


</div>

</div>

</body>

</html>

<?php

$conn->close();

?>