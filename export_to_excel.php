<?php
// Include necessary files for database connection and session
include 'db_connect.php';
session_start();

// Get the faculty ID from the query string
$faculty_id = isset($_GET['fid']) ? intval($_GET['fid']) : 0;

if ($faculty_id == 0) {
    die('Invalid faculty ID.');
}

// Helper function to calculate ordinal suffix
function ordinal_suffix($num) {
    $num = $num % 100;
    if ($num < 11 || $num > 13) {
        switch ($num % 10) {
            case 1: return $num . 'st';
            case 2: return $num . 'nd';
            case 3: return $num . 'rd';
        }
    }
    return $num . 'th';
}

// Fetch academic data
$academic_year = $_SESSION['academic']['year'] ?? 'Unknown Year';
$semester = $_SESSION['academic']['semester'] ?? 1;
$semester_with_suffix = ordinal_suffix($semester);

// Fetch faculty data
$faculty_query = $conn->query("SELECT concat(firstname, ' ', lastname) as name, acad_rank FROM faculty_list WHERE id = $faculty_id");
$faculty_data = $faculty_query->fetch_assoc();
$faculty_name = $faculty_data['name'] ?? 'Unknown Faculty';
$academic_rank = $faculty_data['acad_rank'] ?? 'N/A';

// Fetch evaluation criteria
$criteria_query = $conn->query("
    SELECT q.id AS question_id, c.id AS criteria_id, c.criteria
    FROM question_list q
    INNER JOIN criteria_list c ON c.id = q.criteria_id
    WHERE q.academic_id = {$_SESSION['academic']['id']}
");
$criteria_map = [];
while ($row = $criteria_query->fetch_assoc()) {
    $criteria_map[$row['criteria_id']]['criteria'] = $row['criteria'];
    $criteria_map[$row['criteria_id']]['questions'][] = $row['question_id'];
}

// Fetch student evaluation scores
$evaluation_query = $conn->query("
    SELECT 
        sl.school_id,
        ea.question_id,
        SUM(ea.rate) AS score
    FROM evaluation_answers ea
    INNER JOIN evaluation_list el ON el.evaluation_id = ea.evaluation_id
    INNER JOIN student_list sl ON sl.id = el.student_id
    WHERE el.academic_id = {$_SESSION['academic']['id']}
      AND el.faculty_id = $faculty_id
    GROUP BY sl.school_id, ea.question_id
");
$student_scores = [];
while ($row = $evaluation_query->fetch_assoc()) {
    $school_id = $row['school_id'];
    $question_id = $row['question_id'];
    $score = $row['score'];
    foreach ($criteria_map as $criteria_id => $criteria_data) {
        if (in_array($question_id, $criteria_data['questions'])) {
            $student_scores[$school_id][$criteria_id]['criteria'] = $criteria_data['criteria'];
            $student_scores[$school_id][$criteria_id]['total_score'] =
                ($student_scores[$school_id][$criteria_id]['total_score'] ?? 0) + $score;
            break;
        }
    }
}

// Calculate totals and averages for students
$overall_total = 0;
$row_count = 0; // Count the total number of rows (students)
foreach ($student_scores as $school_id => $criteria_scores) {
    $total = array_sum(array_column($criteria_scores, 'total_score'));
    $student_scores[$school_id]['total'] = $total;
    $student_scores[$school_id]['average'] = round($total / count($criteria_map), 2);
    $overall_total += $total;
    $row_count++; // Increment the row count for each student
}

// Calculate the overall average
$overall_average = ($row_count > 0) ? round($overall_total / $row_count, 2) : 0;

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
$export_filename = "Student_Evaluation_Report_" . str_replace(' ', '_', $faculty_name) . "_" . date('Y-m-d') . ".xls";
header("Content-Disposition: attachment; filename=$export_filename");
header("Pragma: no-cache");
header("Expires: 0");

// Generate the Excel content
?>
<table border="1" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th colspan="7" style="background-color: #f2f2f2; font-weight: bold; text-align: center;">Student Evaluation Report</th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">Zamboanga State Polytechnic State University</th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">R.T. Lim Boulevard, Zamboanga City</th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">National Budget Circular No. 461</th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">Qualitative Contribution Evaluation (QCE)</th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">Ninth Cycle</th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">July 1, 2019 - July 30, 2023</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Name of Faculty:</strong></td>
            <td colspan="6"><?php echo $faculty_name; ?></td>
        </tr>
        <tr>
            <td><strong>Academic Year:</strong></td>
            <td colspan="6"><?php echo $academic_year . ' ' . $semester_with_suffix . ' Semester'; ?></td>
        </tr>
        <tr>
            <td><strong>Academic Rank:</strong></td>
            <td colspan="6"><?php echo $academic_rank; ?></td>
        </tr>
    </tbody>
</table>

<table border="1" style="border-collapse: collapse; width: 100%; margin-top: 20px;">
    <thead>
        <tr style="background-color: #d9d9d9; font-weight: bold;">
            <th>School ID</th>
            <?php foreach ($criteria_map as $criteria): ?>
                <th><?php echo $criteria['criteria']; ?></th>
            <?php endforeach; ?>
            <th>Total</th>
            <th>Average</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($student_scores as $school_id => $scores): ?>
            <tr>
                <td><?php echo $school_id; ?></td>
                <?php foreach ($criteria_map as $criteria_id => $criteria): ?>
                    <td><?php echo $scores[$criteria_id]['total_score'] ?? 0; ?></td>
                <?php endforeach; ?>
                <td><?php echo $scores['total']; ?></td>
                <td><?php echo $scores['average']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="<?php echo count($criteria_map) + 1; ?>" style="text-align: right; font-weight: bold;">Overall Total:</td>
            <td><?php echo $overall_total; ?></td>
        </tr>
        <tr>
            <td colspan="<?php echo count($criteria_map) + 1; ?>" style="text-align: right; font-weight: bold;">Total Students Evaluated:</td>
            <td><?php echo $row_count; ?></td>
        </tr>
        <tr>
            <td colspan="<?php echo count($criteria_map) + 1; ?>" style="text-align: right; font-weight: bold;">Overall Average:</td>
            <td><?php echo $overall_average; ?></td>
        </tr>
    </tfoot>
</table>
