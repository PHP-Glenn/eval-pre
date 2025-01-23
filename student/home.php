<?php
include('db_connect.php');

function ordinal_suffix($num) {
    $num = $num % 100; // protect against large numbers
    if ($num < 11 || $num > 13) {
        switch($num % 10) {
            case 1: return $num.'st';
            case 2: return $num.'nd';
            case 3: return $num.'rd';
        }
    }
    return $num.'th';
}

$astat = array("Not Yet Started", "Started", "Closed");
$student_id = $_SESSION['login_id']; // Using supervisor's login ID

// Query to fetch evaluation summary for each subject and faculty
$evaluationSummary = $conn->query("
    SELECT 
        el.subject_id, 
        s.subject, 
        f.firstname, 
        f.lastname, 
        q.criteria_id,
        c.criteria, 
        AVG(ea.rate) as average_rating, 
        COUNT(ea.rate) as total_responses 
    FROM evaluation_answers ea
    JOIN evaluation_list el ON ea.evaluation_id = el.evaluation_id
    JOIN subject_list s ON el.subject_id = s.id
    JOIN faculty_list f ON el.faculty_id = f.id
    JOIN question_list q ON ea.question_id = q.id
    JOIN criteria_list c ON q.criteria_id = c.id
    WHERE el.faculty_id IN (
        SELECT id FROM faculty_list WHERE student_id = '$student_id'
    )
    GROUP BY el.subject_id, q.criteria_id
");
?>

<div class="col-12">
    <div class="card">
        <div class="card-body">
            <h3>Welcome <?php echo $_SESSION['login_name']; ?>!</h3>
            <br>
            <div class="col-md-5">
                <div class="callout callout-info">
                    <h5><b>Academic Year: <?php echo $_SESSION['academic']['year'].' '.(ordinal_suffix($_SESSION['academic']['semester'])) ?> Semester</b></h5>
                    <h6><b>Evaluation Status: <?php echo $astat[$_SESSION['academic']['status']] ?></b></h6>
                </div>
            </div>

            <!-- Display evaluation summary -->
            <h5 class="mt-4"><b>Evaluation Summary:</b></h5>
            <div class="row">
                <?php
                if ($evaluationSummary->num_rows > 0) {
                    $currentSubject = null;
                    while ($row = $evaluationSummary->fetch_assoc()) {
                        // If the subject changes, create a new section
                        if ($currentSubject !== $row['subject']) {
                            if ($currentSubject !== null) {
                                echo '</table></div>'; // Close the previous table
                            }
                            $currentSubject = $row['subject'];
                            echo '<div class="col-12 mb-3">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="card-title mb-0">' . $row['subject'] . '</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text">Faculty: <strong>' . $row['firstname'] . ' ' . $row['lastname'] . '</strong></p>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Criteria</th>
                                                        <th>Average Rating</th>
                                                        
                                                    </tr>
                                                </thead>
                                                <tbody>';
                        }
                        // Add criteria rows
                        echo '<tr>
                                <td>' . $row['criteria'] . '</td>
                                <td>' . number_format($row['average_rating'], 2) . '</td>
                                
                              </tr>';
                    }
                    if ($currentSubject !== null) {
                        echo '</tbody></table></div></div>'; // Close the last table
                    }
                } else {
                    echo '<div class="col-12 text-center">
                            <div class="alert alert-warning">No evaluation summaries available.</div>
                          </div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Add necessary scripts for Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
