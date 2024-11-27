<?php
include('db_connect.php');

// Function to get ordinal suffix for numbers
function ordinal_suffix1($num) {
    $num = $num % 100;
    if ($num < 11 || $num > 13) {
        switch($num % 10) {
            case 1: return $num . 'st';
            case 2: return $num . 'nd';
            case 3: return $num . 'rd';
        }
    }
    return $num . 'th';
}

$astat = array("Not Yet Started", "Started", "Closed");
$supervisor_id = $_SESSION['login_id'];

// Query to fetch faculties evaluated by the logged-in supervisor
$evaluatedFaculties = $conn->query("
    SELECT f.id, CONCAT(f.firstname, ' ', f.lastname) as faculty_name, se.date_evaluated 
    FROM supervisor_evaluation_list se 
    INNER JOIN faculty_list f ON se.faculty_id = f.id 
    WHERE se.supervisor_id = $supervisor_id
");
?>

<div class="col-12">
    <div class="card">
        <div class="card-body">
            <h3>Welcome <?php echo $_SESSION['login_name']; ?>!</h3>
            <br>
            <div class="col-md-5">
                <div class="callout callout-info">
                    <h5><b>Academic Year: <?php echo $_SESSION['academic']['year'] . ' ' . ordinal_suffix1($_SESSION['academic']['semester']); ?> Semester</b></h5>
                    <h6><b>Evaluation Status: <?php echo $astat[$_SESSION['academic']['status']]; ?></b></h6>
                </div>
            </div>

            <!-- Display evaluated faculties -->
            <h5 class="mt-4"><b>List of Evaluated Faculties:</b></h5>
            <div class="row">
                <?php if ($evaluatedFaculties->num_rows > 0): ?>
                    <?php while ($row = $evaluatedFaculties->fetch_assoc()): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo $row['faculty_name']; ?></h6>
                                    <p class="card-text">Evaluated on: <strong><?php echo date('F j, Y, g:i a', strtotime($row['date_evaluated'])); ?></strong></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-warning">No faculties have been evaluated yet.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add any necessary scripts for Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
