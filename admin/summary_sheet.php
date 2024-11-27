<?php
$faculty_id = isset($_GET['fid']) ? $_GET['fid'] : '';

function ordinal_suffix($num) {
    $num = $num % 100;
    if ($num < 11 || $num > 13) {
        switch ($num % 10) {
            case 1: return $num.'st';
            case 2: return $num.'nd';
            case 3: return $num.'rd';
        }
    }
    return $num.'th';
}

$academic_year = isset($_SESSION['academic']['year']) ? $_SESSION['academic']['year'] : 'N/A';
$academic_semester = isset($_SESSION['academic']['semester']) ? $_SESSION['academic']['semester'] : 'N/A';
?>
<div class="col-lg-12">
    <div class="callout callout-info">
        <div class="d-flex w-100 justify-content-center align-items-center">
            <label for="faculty">Select Faculty:</label>
            <div class="mx-2 col-md-4">
                <select id="faculty_id" class="form-control form-control-sm select2">
                    <option value="">Select a Faculty</option>
                    <?php 
                    $faculty = $conn->query("SELECT *, CONCAT(firstname, ' ', lastname) AS name FROM faculty_list ORDER BY CONCAT(firstname, ' ', lastname) ASC");
                    while ($row = $faculty->fetch_assoc()):
                    ?>
                        <option value="<?php echo $row['id'] ?>" <?php echo isset($faculty_id) && $faculty_id == $row['id'] ? "selected" : "" ?>>
                            <?php echo ucwords($row['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </div>
    
    <style>
        @media print {
            #print-btn, #export-btn {
                display: none !important;
            }
        }
    </style>

    <div class="container mt-4">
    <div class="col-md-12 text-center mt-3">
                    <button class="btn btn-success" id="print-btn" style="display: none;">
                        <i class="fa fa-print"></i> Print
                    </button>
                    <button class="btn btn-info" id="export-btn" style="display: none;">
                        <i class="fa fa-file-excel"></i> Export to Excel
                    </button>
                </div>
        <div class="card shadow-lg p-4" id="printable-area">
            
            <div class="text-center">
                <p class="mb-0">ZAMBOANGA CITY STATE POLYTECHNIC COLLEGE</p>
                <p class="mb-0">Region IX, Zamboanga Peninsula</p>
                <p class="mb-0">R.T. Lim Boulevard, Zamboanga City</p>
                <p class="mb-0">National Budget Circular No. 461</p>
                <p class="mb-0">Qualitative Contribution Evaluation (QCE)</p>
                <p class="font-weight-bold mb-3">KEY RESULT AREA 1: INSTRUCTION</p>
                <p class="mb-0">A. TEACHING EFFECTIVENESS</p>
                <p class="mb-0">Faculty Performance Evaluation by Students and Supervisor</p>
                <p class="mb-0">Evaluation Period: <u>July 1, 2019 to July 31, 2023</u></p>
            </div>
            
            <hr>
            <form>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name of Faculty:</strong> <span id="fname">N/A</span></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p><strong>Academic Year:</strong> 
                            <span id="year"><?php echo strtoupper($academic_year); ?></span> - 
                            <span id="semester">
                                <?php echo $academic_semester == 1 ? "1st Semester" : ($academic_semester == 2 ? "2nd Semester" : ucwords($academic_semester)); ?>
                            </span>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="text-center mt-4">Evaluation Summary</h5>
                        <table class="table table-bordered table-hover">
                            <thead class="table-secondary">
                                <tr>
                                    <th style="width: 40%;">Evaluator</th>
                                    <th style="width: 20%;">Average Rating</th>
                                    <th style="width: 20%;">Weight</th>
                                    <th style="width: 20%;">Equivalent Point</th>
                                </tr>
                            </thead>
                            <tbody id="supervisor-summary-body"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-center fw-bold">TOTAL/WEIGHTED AVERAGE</td>
                                    <td class="text-center">100%</td>
                                    <td id="total-weighted-average" class="text-center fw-bold">N/A</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
              
            </form>
        </div>
    </div>
    <script>
    $('#faculty_id').change(function () {
        const facultyId = $(this).val();

        if (facultyId) {
            $.ajax({
                url: 'ajax.php?action=get_summary',
                method: 'POST',
                data: { faculty_id: facultyId },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#fname').text(response.data.faculty_name || 'N/A');
                        let summaryRows = '';
                        response.data.evaluators.forEach(evaluator => {
                            summaryRows += `
                                <tr class="text-center">
                                    <td>${evaluator.evaluator}</td>
                                    <td>${evaluator.average_rating}</td>
                                    <td>${evaluator.weight}%</td>
                                    <td>${evaluator.equivalent_point}</td>
                                </tr>
                            `;
                        });
                        $('#supervisor-summary-body').html(summaryRows);
                        $('#total-weighted-average').text(response.data.total_weighted_average || 'N/A');
                        
                        // Show buttons only if there is data
                        $('#print-btn').show();
                        $('#export-btn').show();
                    } else {
                        alert(response.message);
                        clearSummary();
                    }
                },
                error: function () {
                    alert('An error occurred while fetching the summary.');
                    clearSummary();
                }
            });
        } else {
            clearSummary();
        }
    });

    function clearSummary() {
        $('#fname').text('N/A');
        $('#supervisor-summary-body').html('<tr><td colspan="4" class="text-center text-muted">No data available.</td></tr>');
        $('#total-weighted-average').text('N/A');
        $('#print-btn').hide();
        $('#export-btn').hide();  // Hide buttons when there is no data
    }

    $('#print-btn').click(function (e) {
        e.preventDefault();
        const printableContent = document.getElementById('printable-area').innerHTML;
        const originalContent = document.body.innerHTML;

        document.body.innerHTML = printableContent;
        window.print();

        document.body.innerHTML = originalContent;
        window.location.reload();
    });

    // Export to Excel logic
    $('#export-btn').click(function (e) {
        e.preventDefault();
        const facultyId = $('#faculty_id').val();

        if (facultyId) {
            // Redirect to the PHP script for Excel export
            window.location.href = 'summary_sheet_excel.php?fid=' + facultyId;
        } else {
            alert('Please select a faculty.');
        }
    });
    </script>
