<?php
// Include necessary files for database connection
include 'db_connect.php';
session_start();

// Get the faculty ID from the query string
$faculty_id = isset($_GET['fid']) ? intval($_GET['fid']) : 0;

if ($faculty_id == 0) {
    die('Invalid faculty ID.');
}

// Fetch faculty data
$faculty_query = $conn->query("SELECT CONCAT(firstname, ' ', lastname) AS name FROM faculty_list WHERE id = $faculty_id");
if (!$faculty_query) {
    die('Query Error (Faculty Data): ' . $conn->error);
}
$faculty_data = $faculty_query->fetch_assoc();
$faculty_name = $faculty_data['name'] ?? 'Unknown Faculty';

// Fetch evaluation summary for the selected faculty
$summary_query = $conn->query("
    SELECT ev.evaluator_name, AVG(ev.rating) AS average_rating, SUM(ev.weight) AS total_weight, 
           SUM(ev.rating * ev.weight) / 100 AS equivalent_point
    FROM evaluation_summary ev
    WHERE ev.faculty_id = $faculty_id
    GROUP BY ev.evaluator_name
");

// Debugging: Check if query executed successfully
if (!$summary_query) {
    die('Query Error (Evaluation Summary): ' . $conn->error);
}

// Debugging: Check if query returned rows
if ($summary_query->num_rows == 0) {
    die('No data available for the selected faculty. Debug Faculty ID: ' . $faculty_id);
}

// Process query results
$evaluators = [];
while ($row = $summary_query->fetch_assoc()) {
    $evaluators[] = $row;
}

// Calculate total weighted average
$total_weighted_average = 0;
foreach ($evaluators as $evaluator) {
    $total_weighted_average += $evaluator['equivalent_point'];
}

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
$export_filename = "Evaluation_Summary_Report_" . str_replace(' ', '_', $faculty_name) . "_" . date('Y-m-d') . ".xls";
header("Content-Disposition: attachment; filename=$export_filename");
header("Pragma: no-cache");
header("Expires: 0");

// Generate the Excel content
?>
<table border="1" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th colspan="4" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">Faculty Evaluation Summary</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center;">Zamboanga City State Polytechnic College</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center;">Region IX, Zamboanga Peninsula</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center;">Faculty: <?php echo htmlspecialchars($faculty_name, ENT_QUOTES, 'UTF-8'); ?></th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center;">Evaluation Period: <u>July 1, 2019 to July 31, 2023</u></th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th style="width: 40%;">Evaluator</th>
            <th style="width: 20%;">Average Rating</th>
            <th style="width: 20%;">Weight</th>
            <th style="width: 20%;">Equivalent Point</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($evaluators as $evaluator): ?>
            <tr>
                <td><?php echo htmlspecialchars($evaluator['evaluator_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo number_format($evaluator['average_rating'], 2); ?></td>
                <td><?php echo number_format($evaluator['total_weight'], 2); ?>%</td>
                <td><?php echo number_format($evaluator['equivalent_point'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align: right; font-weight: bold;">Total/Weighted Average:</td>
            <td><?php echo number_format($total_weighted_average, 2); ?></td>
        </tr>
    </tfoot>
</table>
<?php
exit;
?>
