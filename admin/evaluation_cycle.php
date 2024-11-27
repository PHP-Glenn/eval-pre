<?php 
$faculty_id = isset($_GET['fid']) ? $_GET['fid'] : ''; 
$academic_year = isset($_SESSION['academic']['year']) ? $_SESSION['academic']['year'] : '';
$semester = isset($_SESSION['academic']['semester']) ? $_SESSION['academic']['semester'] : '';

function ordinal_suffix($num){
    $num = $num % 100; // Protect against large numbers
    if ($num < 11 || $num > 13) {
        switch ($num % 10) {
            case 1: return $num.'st';
            case 2: return $num.'nd';
            case 3: return $num.'rd';
        }
    }
    return $num.'th';
}
?>
<div class="container">
    <div class="callout callout-info">
        <div class="d-flex w-100 justify-content-center align-items-center">
            <label for="faculty">Select Faculty</label>
            <div class="mx-2 col-md-4">
                <select name="faculty_id" id="faculty_id" class="form-control form-control-sm select2">
                    <option value=""></option>
                    <?php 
                    $faculty = $conn->query("SELECT *,concat(firstname,' ',lastname) as name FROM faculty_list ORDER BY concat(firstname,' ',lastname) ASC");
                    $f_arr = array();
                    $fname = array();
                    while($row = $faculty->fetch_assoc()):
                        $f_arr[$row['id']] = $row;
                        $fname[$row['id']] = ucwords($row['name']);
                    ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($faculty_id) && $faculty_id == $row['id'] ? "selected" : "" ?>><?php echo ucwords($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Buttons to Select Semester -->
    <div class="row my-3">
        <div class="col-md-6">
            <button id="load-first-semester" class="btn btn-primary w-100">Load First Semester</button>
        </div>
        <div class="col-md-6">
            <button id="load-second-semester" class="btn btn-secondary w-100">Load Second Semester</button>
        </div>
    </div>

    <!-- Display Evaluation Report Information -->
    <div class="callout callout-info" id="printable">
        <h3 class="text-center">Evaluation Report</h3>
        <hr>
        <table width="100%">
            <tr>
                <td width="50%"><p><b>Faculty: <span id="fname"></span></b></p></td>
                <td width="50%"><p><b>Academic Year: <span id="ay"><?php echo $academic_year . ' ' . (ordinal_suffix($semester)) ?> Semester</span></b></p></td>
            </tr>
        </table>
        <p class=""><b>Total Student Evaluated: <span id="tse"></span></b></p>
        <p class=""><b>Total Rates: <span id="total_rates"></span></b></p>
        
        <!-- First Semester Table -->
        <div id="first-semester-container" class="table-container" style="display: none;">
            <h2>First Semester (<?php echo $academic_year; ?>)</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Commitment</th>
                        <th>Knowledge of Subject Matter</th>
                        <th>Teaching for Independent Learning</th>
                        <th>Management of Learning</th>
                        <th>Total</th>
                        <th>Average</th>
                    </tr>
                </thead>
                <tbody id="first-semester-rows"></tbody>
            </table>
            <p><b>First Semester Average: <span id="first-semester-average">0</span></b></p>
        </div>

        <!-- Second Semester Table -->
        <div id="second-semester-container" class="table-container" style="display: none;">
            <h2>Second Semester (<?php echo $academic_year; ?>)</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Commitment</th>
                        <th>Knowledge of Subject Matter</th>
                        <th>Teaching for Independent Learning</th>
                        <th>Management of Learning</th>
                        <th>Total</th>
                        <th>Average</th>
                    </tr>
                </thead>
                <tbody id="second-semester-rows"></tbody>
            </table>
            <p><b>Second Semester Average: <span id="second-semester-average">0</span></b></p>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#faculty_id').change(function() {
        if ($(this).val() > 0) {
            window.history.pushState({}, null, './index.php?page=report&fid=' + $(this).val());
            load_class();
        }
    });

    // Load the first semester report when button clicked
    $('#load-first-semester').click(function() {
        load_report($('#faculty_id').val(), '<?php echo $academic_year; ?>', '1st');
        $('#first-semester-container').show();
        $('#second-semester-container').hide();
    });

    // Load the second semester report when button clicked
    $('#load-second-semester').click(function() {
        load_report($('#faculty_id').val(), '<?php echo $academic_year; ?>', '2nd');
        $('#second-semester-container').show();
        $('#first-semester-container').hide();
    });

    if ($('#faculty_id').val() > 0) {
        load_class();
    }

    function load_class() {
        var fname = <?php echo json_encode($fname); ?>;
        $('#fname').text(fname[$('#faculty_id').val()]);
    }

    function load_report(faculty_id, academic_year, semester) {
        $.ajax({
            url: 'ajax.php?action=get_report',
            method: 'POST',
            data: { 
                faculty_id: faculty_id, 
                academic_year: academic_year, 
                semester: semester 
            },
            success: function(resp) {
                if (resp) {
                    resp = JSON.parse(resp);

                    // Clear previous data
                    $('#first-semester-rows, #second-semester-rows').empty();

                    if (semester == '1st') {
                        let firstTotal = 0;
                        let studentCount = 0;
                        let firstSemesterData = resp.first_semester;

                        // Add the student data with averages
                        firstSemesterData.forEach(row => {
                            let total = row.commitment + row.knowledge + row.teaching + row.management;
                            let average = (total / 4).toFixed(2);

                            $('#first-semester-rows').append(
                                `<tr>
                                    <td>${row.student_id}</td>
                                    <td>${row.commitment}</td>
                                    <td>${row.knowledge}</td>
                                    <td>${row.teaching}</td>
                                    <td>${row.management}</td>
                                    <td>${total}</td>
                                    <td>${average}</td>
                                </tr>`
                            );
                            firstTotal += total;
                            studentCount++;
                        });

                        let firstAverage = (firstTotal / studentCount).toFixed(2);
                        $('#first-semester-average').text(firstAverage);
                    } else if (semester == '2nd') {
                        let secondTotal = 0;
                        let studentCount = 0;
                        let secondSemesterData = resp.second_semester;

                        secondSemesterData.forEach(row => {
                            let total = row.commitment + row.knowledge + row.teaching + row.management;
                            let average = (total / 4).toFixed(2);

                            $('#second-semester-rows').append(
                                `<tr>
                                    <td>${row.student_id}</td>
                                    <td>${row.commitment}</td>
                                    <td>${row.knowledge}</td>
                                    <td>${row.teaching}</td>
                                    <td>${row.management}</td>
                                    <td>${total}</td>
                                    <td>${average}</td>
                                </tr>`
                            );
                            secondTotal += total;
                            studentCount++;
                        });

                        let secondAverage = (secondTotal / studentCount).toFixed(2);
                        $('#second-semester-average').text(secondAverage);
                    }

                    $('#tse').text(resp.total_students_evaluated);
                    $('#total_rates').text(resp.total_rates + '%');
                }
            }
        });
    }
});
</script>
