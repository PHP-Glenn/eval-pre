<?php include('db_connect.php'); ?>
<?php 
function ordinal_suffix1($num){
    $num = $num % 100; // protect against large numbers
    if($num < 11 || $num > 13){
        switch($num % 10){
            case 1: return $num.'st';
            case 2: return $num.'nd';
            case 3: return $num.'rd';
        }
    }
    return $num.'th';
}

$astat = array("Not Yet Started", "On-going", "Closed");

// Check if 'academic' session is set
$academic_year = isset($_SESSION['academic']['year']) ? $_SESSION['academic']['year'] : 'Unknown Year';
$academic_semester = isset($_SESSION['academic']['semester']) ? ordinal_suffix1($_SESSION['academic']['semester']) : 'Unknown Semester';
$academic_status = isset($_SESSION['academic']['status']) ? $astat[$_SESSION['academic']['status']] : 'Unknown Status';
?>
<div class="col-12">
    <div class="card">
        <div class="card-body">
            Welcome <?php echo $_SESSION['login_name']; ?>!
            <br>
            <div class="col-md-5">
                <div class="callout callout-info">
                    <h5><b>Academic Year: <?php echo $academic_year . ' ' . $academic_semester; ?> Semester</b></h5>
                    <h6><b>Evaluation Status: <?php echo $academic_status; ?></b></h6>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-sm-6 col-md-4">
        <a href="./index.php?page=faculty_list" class="small-box bg-light shadow-sm border">
            <div class="inner">
                <h3><?php echo $conn->query("SELECT * FROM faculty_list")->num_rows; ?></h3>
                <p>Total Faculty Member</p>
            </div>
            <div class="icon">
                <i class="fa fa-user-friends"></i>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <a href="./index.php?page=student_list" class="small-box bg-light shadow-sm border">
            <div class="inner">
                <h3><?php echo $conn->query("SELECT * FROM student_list")->num_rows; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="icon">
                <i class="fa ion-ios-people-outline"></i>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <a href="./index.php?page=user_list" class="small-box bg-light shadow-sm border">
            <div class="inner">
                <h3><?php echo $conn->query("SELECT * FROM users")->num_rows; ?></h3>
                <p>Admin User</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <a href="./index.php?page=supervisor_list" class="small-box bg-light shadow-sm border">
            <div class="inner">
                <h3><?php echo $conn->query("SELECT * FROM supervisor_list")->num_rows; ?></h3>
                <p>Total Supervisors</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <a href="./index.php?page=class_list" class="small-box bg-light shadow-sm border">
            <div class="inner">
                <h3><?php echo $conn->query("SELECT * FROM class_list")->num_rows; ?></h3>
                <p>Total Classes</p>
            </div>
            <div class="icon">
                <i class="fa fa-list-alt"></i>
            </div>
        </a>
    </div>
</div>
