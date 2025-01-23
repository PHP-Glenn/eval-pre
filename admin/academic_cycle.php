<?php 
$faculty_id = isset($_GET['fid']) ? $_GET['fid'] : ''; 
$academic_year = isset($_SESSION['academic']['year']) ? $_SESSION['academic']['year'] : '';
$semester = isset($_SESSION['academic']['semester']) ? $_SESSION['academic']['semester'] : '';

// Define the academic years
$academic_years = ["2019 - 2020", "2020 - 2021", "2021 - 2022", "2022 - 2023"];

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
<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
}
table {
    border-collapse: collapse;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}
th, td {
    border: 3px solid #ddd;
    padding: 8px;
    text-align: center;
}
th {
    background-color: #f2f2f2;
    font-weight: bold;
}
.evaluator {
    background-color: #f8f8f8;
    font-weight: bold;
}
.average-column {
    background-color: #f5f5f5;
}
thead th {
    border-top: 2px solid #000;
    border-bottom: 2px solid #000;
}
tfoot td {
    border-top: 2px solid #000;
    border-bottom: 2px solid #000;
}
tbody tr {
    border-bottom: 1px solid #ddd;
}
</style>

<!-- Add dropdown to filter Academic Year -->
<div class="container">
    <div class="callout callout-info">
        <div class="d-flex w-100 justify-content-center align-items-center">
            <label for="faculty">Select Faculty</label>
            <div class="mx-2 col-md-4">
                <select name="faculty_id" id="faculty_id" class="form-control form-control-sm select2">
                    <option value=""></option>
                    <?php 
                    $faculty = $conn->query("SELECT *, concat(firstname, ' ', lastname) as name FROM faculty_list ORDER BY concat(firstname, ' ', lastname) ASC");
                    while($row = $faculty->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo isset($faculty_id) && $faculty_id == $row['id'] ? "selected" : "" ?>>
                            <?php echo ucwords($row['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Academic Year Filter -->
            <label for="academic_year">Select Academic Year</label>
            <div class="mx-2 col-md-4">
                <select id="academic_year" class="form-control form-control-sm">
                    <option value="">Select Year</option>
                    <?php foreach ($academic_years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $year == $academic_year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="academic_year" class="form-control form-control-sm">
                    <option value="">Select Year</option>
                    <?php foreach ($academic_years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $year == $academic_year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="academic_year" class="form-control form-control-sm">
                    <option value="">Select Year</option>
                    <?php foreach ($academic_years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $year == $academic_year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Table with Dynamic Content -->
<body>
    <table>
        <thead>
            <tr>
                <th rowspan="2">EVALUATOR</th>
                <th colspan="2" id="sy-header-1">SY 2019 - 2020</th>
                <th colspan="2" id="sy-header-2">SY 2020 - 2021</th>
                <th colspan="2" id="sy-header-3">SY 2021 - 2022</th>
                <th rowspan="2">AVERAGE RATING</th>
                <th rowspan="2">MAXIMUM POINTS</th>
                <th rowspan="2">EQUIVALENT POINTS</th>
            </tr>
            <tr>
                <th id="semester-1">1st Semester</th>
                <th id="semester-2">2nd Semester</th>
                <th id="semester-3">1st Semester</th>
                <th id="semester-4">2nd Semester</th>
                <th id="semester-5">1st Semester</th>
                <th id="semester-6">2nd Semester</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <tr>
                <td class="evaluator">STUDENTS</td>
                <td>97.47</td>
                <td>97.20</td>
                <td>95.57</td>
                <td>98.33</td>
                <td>96.73</td>
                <td>99.33</td>
                <td class="average-column">97.32</td>
                <td>36</td>
                <td>35.22</td>
            </tr>
            <tr>
                <td class="evaluator">SUPERVISOR</td>
                <td>99</td>
                <td>100</td>
                <td>100</td>
                <td>100</td>
                <td>100</td>
                <td>100</td>
                <td class="average-column">99.88</td>
                <td>24</td>
                <td>23.97</td>
            </tr>
            <tr>
                <td class="evaluator">TOTAL POINTS</td>
                <td>98.08</td>
                <td>98.32</td>
                <td>97.94</td>
                <td>99.00</td>
                <td>98.04</td>
                <td>99.60</td>
                <td class="average-column">98.54</td>
                <td>60</td>
                <td>59.19</td>
            </tr>
            <tr>
                <td class="evaluator">AVERAGE PER SY</td>
                <td colspan="2">98.20</td>
                <td colspan="2">98.47</td>
                <td colspan="2">98.82</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>
</body>

<script>
document.getElementById('academic_year').addEventListener('change', function() {
    var selectedYear = this.value;
    var syHeaders = {
        '2019 - 2020': ['SY 2019 - 2020', '1st Semester', '2nd Semester'],
        '2020 - 2021': ['SY 2020 - 2021', '1st Semester', '2nd Semester'],
        '2021 - 2022': ['SY 2021 - 2022', '1st Semester', '2nd Semester'],
        '2022 - 2023': ['SY 2022 - 2023', '1st Semester', '2nd Semester'],
    };
    if (selectedYear) {
        document.getElementById('sy-header-1').innerHTML = syHeaders[selectedYear][0];
        document.getElementById('semester-1').innerHTML = syHeaders[selectedYear][1];
        document.getElementById('semester-2').innerHTML = syHeaders[selectedYear][2];
    }
});
</script>
</html>
