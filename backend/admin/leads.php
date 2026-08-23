<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| DELETE LEAD
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_lead"])) {

    $leadId = (int) $_POST["delete_lead"];

    if ($leadId > 0) {

        $stmt = $conn->prepare("DELETE FROM leads WHERE id = ?");

        if ($stmt) {

            $stmt->bind_param("i", $leadId);
            $stmt->execute();
            $stmt->close();

            header("Location: leads.php?deleted=1");
            exit;

        } else {

            header("Location: leads.php?error=delete");
            exit;
        }
    }
}

/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");

$source = trim($_GET["source"] ?? "");

/*
|--------------------------------------------------------------------------
| GET AVAILABLE SOURCES
|--------------------------------------------------------------------------
*/

$sources = [];

$sourceResult = $conn->query(
    "SELECT DISTINCT source
     FROM leads
     WHERE source IS NOT NULL
       AND source != ''
     ORDER BY source ASC"
);

if ($sourceResult) {

    while ($row = $sourceResult->fetch_assoc()) {

        $sources[] = $row["source"];

    }
}

/*
|--------------------------------------------------------------------------
| BUILD LEAD QUERY
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        name,
        phone,
        email,
        message,
        source,
        created_at
    FROM leads
    WHERE 1=1
";

$params = [];
$types = "";

/*
|--------------------------------------------------------------------------
| SEARCH FILTER
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $sql .= "
        AND (
            name LIKE ?
            OR phone LIKE ?
            OR email LIKE ?
            OR message LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ssss";
}

/*
|--------------------------------------------------------------------------
| SOURCE FILTER
|--------------------------------------------------------------------------
*/

if ($source !== "") {

    $sql .= " AND source = ? ";

    $params[] = $source;

    $types .= "s";
}

/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

$sql .= " ORDER BY id DESC";

/*
|--------------------------------------------------------------------------
| EXECUTE QUERY
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

$leads = [];

if ($stmt) {

    if (!empty($params)) {

        $stmt->bind_param($types, ...$params);

    }

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $leads[] = $row;

        }

    }

    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| TOTAL LEADS
|--------------------------------------------------------------------------
*/

$totalLeads = 0;

$countResult = $conn->query(
    "SELECT COUNT(*) AS total FROM leads"
);

if ($countResult) {

    $countRow = $countResult->fetch_assoc();

    $totalLeads = (int) $countRow["total"];
}

/*
|--------------------------------------------------------------------------
| CURRENT RESULT COUNT
|--------------------------------------------------------------------------
*/

$visibleLeads = count($leads);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Leads - Aerol Colt Admin</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    background: #05050A;

    color: #FFFFFF;

    font-family:
        Arial,
        Helvetica,
        sans-serif;
}

.container {

    max-width: 1450px;

    margin: 0 auto;

    padding: 30px;
}

/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.header {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 20px;

    margin-bottom: 25px;
}

.title h1 {

    margin: 0;

    font-size: 32px;
}

.title p {

    margin: 8px 0 0;

    color: #9CA3AF;

    font-size: 15px;
}

.back {

    display: inline-block;

    padding: 11px 18px;

    border-radius: 8px;

    border: 1px solid #2A3048;

    background: #0F111A;

    color: #FFFFFF;

    text-decoration: none;

    font-size: 14px;

    transition: 0.2s;
}

.back:hover {

    background: #171B29;

    border-color: #4B5563;
}

/*
|--------------------------------------------------------------------------
| TOP STATS
|--------------------------------------------------------------------------
*/

.stats {

    display: flex;

    gap: 15px;

    margin-bottom: 25px;

    flex-wrap: wrap;
}

.stat {

    background: #0F111A;

    border: 1px solid #1E2235;

    border-radius: 10px;

    padding: 14px 18px;

    min-width: 150px;
}

.stat-label {

    color: #9CA3AF;

    font-size: 12px;

    margin-bottom: 5px;
}

.stat-number {

    font-size: 22px;

    font-weight: bold;
}

/*
|--------------------------------------------------------------------------
| ALERTS
|--------------------------------------------------------------------------
*/

.alert {

    padding: 14px 18px;

    border-radius: 9px;

    margin-bottom: 20px;

    font-size: 14px;
}

.alert-success {

    background: #0B2A1A;

    border: 1px solid #14532D;

    color: #86EFAC;
}

.alert-error {

    background: #2A0B0B;

    border: 1px solid #7F1D1D;

    color: #FCA5A5;
}

/*
|--------------------------------------------------------------------------
| FILTER BAR
|--------------------------------------------------------------------------
*/

.filters {

    background: #0F111A;

    border: 1px solid #1E2235;

    border-radius: 14px;

    padding: 18px;

    margin-bottom: 20px;

    display: flex;

    align-items: center;

    gap: 12px;

    flex-wrap: wrap;
}

.search {

    flex: 1;

    min-width: 250px;
}

.search input,
.filters select {

    width: 100%;

    padding: 12px 14px;

    border-radius: 8px;

    border: 1px solid #2A3048;

    background: #080A11;

    color: #FFFFFF;

    outline: none;

    font-size: 14px;
}

.search input:focus,
.filters select:focus {

    border-color: #667085;
}

.filters select {

    width: 210px;
}

.filter-button {

    padding: 12px 18px;

    border: none;

    border-radius: 8px;

    background: #FFFFFF;

    color: #05050A;

    font-weight: bold;

    cursor: pointer;

    font-size: 14px;
}

.filter-button:hover {

    background: #E5E7EB;
}

.clear-button {

    padding: 11px 16px;

    border-radius: 8px;

    border: 1px solid #2A3048;

    background: transparent;

    color: #9CA3AF;

    text-decoration: none;

    font-size: 14px;
}

.clear-button:hover {

    color: #FFFFFF;

    background: #171B29;
}

/*
|--------------------------------------------------------------------------
| TABLE WRAPPER
|--------------------------------------------------------------------------
*/

.table-wrapper {

    background: #0F111A;

    border: 1px solid #1E2235;

    border-radius: 14px;

    overflow: hidden;
}

.table-header {

    padding: 18px 20px;

    border-bottom: 1px solid #1E2235;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;
}

.table-header h2 {

    margin: 0;

    font-size: 18px;
}

.table-header span {

    color: #9CA3AF;

    font-size: 13px;
}

/*
|--------------------------------------------------------------------------
| TABLE
|--------------------------------------------------------------------------
*/

.table-scroll {

    width: 100%;

    overflow-x: auto;
}

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1100px;
}

thead {

    background: #090B12;
}

th {

    text-align: left;

    padding: 15px 16px;

    font-size: 12px;

    text-transform: uppercase;

    letter-spacing: 0.5px;

    color: #9CA3AF;

    border-bottom: 1px solid #1E2235;

    white-space: nowrap;
}

td {

    padding: 17px 16px;

    border-bottom: 1px solid #1A1D29;

    vertical-align: top;

    font-size: 14px;
}

tbody tr:hover {

    background: #121520;
}

tbody tr:last-child td {

    border-bottom: none;
}

/*
|--------------------------------------------------------------------------
| ID
|--------------------------------------------------------------------------
*/

.id {

    color: #7DD3FC;

    font-weight: bold;
}

/*
|--------------------------------------------------------------------------
| NAME
|--------------------------------------------------------------------------
*/

.name {

    font-weight: bold;

    color: #FFFFFF;
}

/*
|--------------------------------------------------------------------------
| CONTACT
|--------------------------------------------------------------------------
*/

.contact a {

    color: #D1D5DB;

    text-decoration: none;
}

.contact a:hover {

    color: #FFFFFF;

    text-decoration: underline;
}

.phone {

    margin-bottom: 7px;
}

/*
|--------------------------------------------------------------------------
| MESSAGE
|--------------------------------------------------------------------------
*/

.message {

    max-width: 300px;

    color: #D1D5DB;

    line-height: 1.5;

    white-space: pre-wrap;

    word-break: break-word;
}

.no-message {

    color: #6B7280;

    font-style: italic;
}

/*
|--------------------------------------------------------------------------
| SOURCE
|--------------------------------------------------------------------------
*/

.source-badge {

    display: inline-block;

    padding: 6px 9px;

    border-radius: 6px;

    background: #1E2235;

    color: #D1D5DB;

    font-size: 11px;

    word-break: break-word;
}

/*
|--------------------------------------------------------------------------
| DATE
|--------------------------------------------------------------------------
*/

.date {

    color: #9CA3AF;

    white-space: nowrap;

    font-size: 13px;
}

/*
|--------------------------------------------------------------------------
| ACTIONS
|--------------------------------------------------------------------------
*/

.actions {

    display: flex;

    gap: 7px;

    flex-wrap: wrap;
}

.action-button {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 8px 10px;

    border-radius: 7px;

    border: 1px solid #2A3048;

    background: #171B29;

    color: #FFFFFF;

    text-decoration: none;

    font-size: 12px;

    cursor: pointer;

    transition: 0.2s;
}

.action-button:hover {

    background: #23293B;
}

.whatsapp {

    border-color: #14532D;

    color: #86EFAC;
}

.whatsapp:hover {

    background: #0B2A1A;
}

.delete {

    border-color: #7F1D1D;

    background: #2A0B0B;

    color: #FCA5A5;
}

.delete:hover {

    background: #450A0A;
}

/*
|--------------------------------------------------------------------------
| EMPTY
|--------------------------------------------------------------------------
*/

.empty {

    text-align: center;

    padding: 70px 20px;

    color: #9CA3AF;
}

.empty strong {

    display: block;

    color: #FFFFFF;

    font-size: 18px;

    margin-bottom: 8px;
}

/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 700px) {

    .container {

        padding: 20px;
    }

    .header {

        flex-direction: column;
    }

    .back {

        width: 100%;

        text-align: center;
    }

    .filters {

        align-items: stretch;
    }

    .search {

        min-width: 100%;
    }

    .filters select {

        width: 100%;
    }

    .filter-button,
    .clear-button {

        width: 100%;

        text-align: center;
    }

}

</style>

</head>

<body>

<div class="container">

<!-- HEADER -->

<div class="header">

<div class="title">

<h1>Leads</h1>

<p>
Website lead submissions
</p>

</div>

<a
    href="index.php"
    class="back"
>
    ← Back to Dashboard
</a>

</div>


<!-- STATS -->

<div class="stats">

<div class="stat">

<div class="stat-label">
Total Leads
</div>

<div class="stat-number">
<?= $totalLeads ?>
</div>

</div>

<div class="stat">

<div class="stat-label">
Showing
</div>

<div class="stat-number">
<?= $visibleLeads ?>
</div>

</div>

</div>


<!-- ALERTS -->

<?php if (isset($_GET["deleted"])): ?>

<div class="alert alert-success">

Lead deleted successfully.

</div>

<?php endif; ?>


<?php if (isset($_GET["error"])): ?>

<div class="alert alert-error">

Unable to delete the lead.

</div>

<?php endif; ?>


<!-- FILTERS -->

<form
    method="GET"
    class="filters"
>

<div class="search">

<input
    type="text"
    name="search"
    placeholder="Search name, phone, email or message..."
    value="<?= htmlspecialchars($search) ?>"
>

</div>


<select name="source">

<option value="">
All Sources
</option>

<?php foreach ($sources as $itemSource): ?>

<option
    value="<?= htmlspecialchars($itemSource) ?>"
    <?= $source === $itemSource ? "selected" : "" ?>
>

<?= htmlspecialchars($itemSource) ?>

</option>

<?php endforeach; ?>

</select>


<button
    type="submit"
    class="filter-button"
>
    Search
</button>


<a
    href="leads.php"
    class="clear-button"
>
    Clear
</a>

</form>


<!-- LEADS TABLE -->

<div class="table-wrapper">

<div class="table-header">

<h2>
Lead Submissions
</h2>

<span>
<?= $visibleLeads ?> result<?= $visibleLeads === 1 ? "" : "s" ?>
</span>

</div>


<?php if (empty($leads)): ?>

<div class="empty">

<strong>
No leads found
</strong>

Try changing your search or filter.

</div>

<?php else: ?>


<div class="table-scroll">

<table>

<thead>

<tr>

<th>ID</th>

<th>Name</th>

<th>Contact</th>

<th>Message</th>

<th>Source</th>

<th>Created</th>

<th>Actions</th>

</tr>

</thead>

<tbody>


<?php foreach ($leads as $lead): ?>

<?php

$leadId = (int) $lead["id"];

$name = $lead["name"] ?? "";

$phone = $lead["phone"] ?? "";

$email = $lead["email"] ?? "";

$message = $lead["message"] ?? "";

$leadSource = $lead["source"] ?? "";

$createdAt = $lead["created_at"] ?? "";

$phoneForLink = preg_replace(
    "/[^0-9+]/",
    "",
    $phone
);

$whatsappNumber = preg_replace(
    "/[^0-9]/",
    "",
    $phone
);

?>

<tr>


<!-- ID -->

<td>

<div class="id">

#<?= $leadId ?>

</div>

</td>


<!-- NAME -->

<td>

<div class="name">

<?= htmlspecialchars($name ?: "—") ?>

</div>

</td>


<!-- CONTACT -->

<td class="contact">

<?php if ($phone): ?>

<div class="phone">

<a
    href="tel:<?= htmlspecialchars($phoneForLink) ?>"
>
    <?= htmlspecialchars($phone) ?>
</a>

</div>

<?php endif; ?>


<?php if ($email): ?>

<div>

<a
    href="mailto:<?= htmlspecialchars($email) ?>"
>
    <?= htmlspecialchars($email) ?>
</a>

</div>

<?php endif; ?>


<?php if (!$phone && !$email): ?>

<span class="no-message">
No contact details
</span>

<?php endif; ?>

</td>


<!-- MESSAGE -->

<td>

<?php if (trim($message) !== ""): ?>

<div class="message">

<?= htmlspecialchars($message) ?>

</div>

<?php else: ?>

<span class="no-message">
No message
</span>

<?php endif; ?>

</td>


<!-- SOURCE -->

<td>

<?php if ($leadSource): ?>

<span class="source-badge">

<?= htmlspecialchars($leadSource) ?>

</span>

<?php else: ?>

<span class="no-message">
Unknown
</span>

<?php endif; ?>

</td>


<!-- CREATED -->

<td>

<div class="date">

<?= htmlspecialchars($createdAt ?: "—") ?>

</div>

</td>


<!-- ACTIONS -->

<td>

<div class="actions">


<?php if ($phoneForLink): ?>

<a
    href="tel:<?= htmlspecialchars($phoneForLink) ?>"
    class="action-button"
>
    Call
</a>

<?php endif; ?>


<?php if ($email): ?>

<a
    href="mailto:<?= htmlspecialchars($email) ?>"
    class="action-button"
>
    Email
</a>

<?php endif; ?>


<?php if ($whatsappNumber): ?>

<a
    href="https://wa.me/<?= htmlspecialchars($whatsappNumber) ?>"
    target="_blank"
    rel="noopener noreferrer"
    class="action-button whatsapp"
>
    WhatsApp
</a>

<?php endif; ?>


<form
    method="POST"
    style="display:inline;"
    onsubmit="return confirm('Are you sure you want to permanently delete this lead?');"
>

<input
    type="hidden"
    name="delete_lead"
    value="<?= $leadId ?>"
>

<button
    type="submit"
    class="action-button delete"
>
    Delete
</button>

</form>


</div>

</td>


</tr>

<?php endforeach; ?>


</tbody>

</table>

</div>

<?php endif; ?>

</div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| Prevent accidental double-submit on delete
|--------------------------------------------------------------------------
*/

document.querySelectorAll("form[method='POST']").forEach(function(form) {

    form.addEventListener("submit", function() {

        const button = form.querySelector("button[type='submit']");

        if (button) {

            button.disabled = true;

            button.innerText = "Deleting...";

        }

    });

});

</script>

</body>

</html>

<?php

$conn->close();

?>