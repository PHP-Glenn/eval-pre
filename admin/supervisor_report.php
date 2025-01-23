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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Evaluation Report</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
        }
        .col-md-9 {
            width: 100%;
            max-width: 5000px;
        }
        
    </style>
</head>
<body>
<div class="col-lg-12">
    <div class="callout callout-info">
        <div class="d-flex w-100 justify-content-center align-items-center">
            <label for="faculty">Select Faculty</label>
            <div class="mx-2 col-md-4">
                <select name="" id="faculty_id" class="form-control form-control-sm select2">
                    <option value=""></option>
                    <?php 
                    $faculty = $conn->query("SELECT *, concat(firstname, ' ', lastname) as name FROM faculty_list ORDER BY concat(firstname, ' ', lastname) ASC");
                    $f_arr = array();
                    $fname = array();
                    while ($row = $faculty->fetch_assoc()):
                        $f_arr[$row['id']] = $row;
                        $fname[$row['id']] = ucwords($row['name']);
                    ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($faculty_id) && $faculty_id == $row['id'] ? "selected" : "" ?>>
                        <?php echo ucwords($row['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="text-right" style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
    <button id="print-btn" class="btn btn-primary" style="display: none;" onclick="printReport()">Print</button>
    <button id="export-btn" class="btn btn-success" style="display: none;" onclick="exportToExcel()">Export to Excel</button>
</div>


    <div class="col-md-9">
        <div class="callout callout-info" id="printable">
            <div>
                <div class="text-center">
                    <h3 style="font-size: 14px; font-weight: bold;">Supervisor Evaluation Report</h3>
                    <hr>
                    <p style="font-size: 12px; margin: 0;">ZAMBOANGA STATE POLYTECHNIC STATE UNIVERSITY</p>
                    <p style="font-size: 12px; margin: 0;">R.T. Lim Boulevard, Zamboanga City</p>
                    <p style="font-size: 12px; margin: 0;">National Budget Circular No. 461</p>
                    <p style="font-size: 12px; margin: 0;">Qualitative Contribution Evaluation (QCE)</p>
                    <p style="font-size: 12px; margin: 0;">For Instructor, Assistant Professor, and Associate Professor</p>
                    <p style="font-size: 12px; margin: 0; font-weight: bold;">NINTH CYCLE</p>
                    <p style="font-size: 12px; margin: 0;">August 1, 2019 - July 31, 2026</p>
                </div>
                <hr>
                <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
                    <tr>
                        <td width="50%">
                            <p><strong>Name of Faculty:</strong> <span id="fname"></span></p>
                        </td>
                        <td width="50%">
                            <p><strong>Academic Year:</strong> <span id="ay"><?php echo $_SESSION['academic']['year'] . ' ' . (ordinal_suffix($_SESSION['academic']['semester'])) ?> Semester</span></p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <p><strong>Academic Rank:</strong> <span id="acad_rank"></span></p>
                        </td>
                    </tr>
                    </table>
                <strong><p>Total Supervisors Evaluated: <span id="tse"></span></p></strong>

                <div class="col-md-12 mt-4">
                    <div class="callout callout-primary">
                        <div class="text-center">
                            <h3 style="font-size: 14px; font-weight: bold;">Supervisor Evaluation Report</h3>
                        </div>
                        <hr>
                        <table class="table table-condensed wborder">
                            <thead>
                                <tr>
                                    <th>School ID</th>
                                    <th>Commitment</th>
                                    <th>Knowledge of Subject Matter</th>
                                    <th>Teaching for Independent Learning</th>
                                    <th>Management of Learning</th>
                                    <th>Total</th>
                                    
                                </tr>
                            </thead>
                            <tbody id="supervisor-criteria-body"></tbody>
                            <tfoot>
                            <tr style="font-weight: bold; background: #f9f9f9;">
                            <td colspan="4"></td>
                            <td>Grand Total</td>
                            <td id="overall-supervisor-total" class="text-center"></td>
            
                            </tr>
                            <tr style="font-weight: bold; background: #f9f9f9;">
                            <td colspan="4"></td>
                            <td>Overall Average</td>
                            <td id="overall-supervisor-average" class="text-center"></td>
                            </tr>
                                
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Store faculty data for quick lookup
    var facultyData = <?php echo json_encode($fname); ?>;

    // Handle faculty selection change
    $('#faculty_id').change(function () {
        const selectedFacultyId = $(this).val();

        if (selectedFacultyId > 0) {
            // Update the URL with the selected faculty ID
            let newUrl = './index.php?page=supervisor_report&fid=' + selectedFacultyId;
            window.history.pushState({}, '', newUrl);

            // Set the faculty name
            if (facultyData[selectedFacultyId]) {
                $('#fname').text(facultyData[selectedFacultyId]);
            } else {
                $('#fname').text('N/A');
            }

            // Load the supervisor report for the selected faculty
            loadSupervisorReport(selectedFacultyId);
        } else {
            clearSupervisorReport();
        }
    });

    // Load the report for the currently selected faculty on page load
    if ($('#faculty_id').val() > 0) {
        const currentFacultyId = $('#faculty_id').val();

        // Set the faculty name
        if (facultyData[currentFacultyId]) {
            $('#fname').text(facultyData[currentFacultyId]);
        }

        loadSupervisorReport(currentFacultyId);
    }
});
function exportToExcel() {
    const facultyId = $('#faculty_id').val();
    if (facultyId > 0) {
        window.location.href = `supervisor_excel.php?fid=${facultyId}`;
    } else {
        alert('Please select a faculty.');
    }
}


// Function to load the supervisor report
function loadSupervisorReport(faculty_id) {
    start_load();
    $.ajax({
        url: 'ajax.php?action=get_supervisor_report',
        method: 'POST',
        data: { faculty_id: faculty_id },
        success: function (resp) {
            resp = JSON.parse(resp);

            if (!resp || !resp.supervisor_scores) {
                clearSupervisorReport();
                $('#print-btn').hide();
                return;
            }

            $('#print-btn').show();
            $('#export-btn').show();
            $('#acad_rank').text(resp.acad_rank || 'N/A');
            $('#tse').text(resp.tse_supervisor || 0);

            populateSupervisorEvaluation(resp);
        },
        error: function (err) {
            console.error('Error fetching supervisor report:', err);
            alert_toast('An error occurred while fetching the report.', 'error');
        },
        complete: function () {
            end_load();
        }
    });
}

// Function to populate supervisor evaluation data
function populateSupervisorEvaluation(resp) {
    const supervisorBody = $('#supervisor-criteria-body');
    supervisorBody.empty();

    const supervisorScores = resp.supervisor_scores || {};
    const criteriaMap = resp.criteria_map || {};

    console.log("Criteria Map:", criteriaMap); // Debugging
    console.log("Supervisor Scores:", supervisorScores); // Debugging

    Object.keys(supervisorScores).forEach((schoolId) => {
        const supervisorData = supervisorScores[schoolId];
        const row = $('<tr></tr>');
        row.append(`<td>${schoolId}</td>`); // School ID

        // Populate scores for each criterion
        Object.keys(criteriaMap).forEach((criteriaId) => {
            const score = supervisorData[criteriaId]?.total_score || 0;
            console.log(`School ID: ${schoolId}, Criteria ID: ${criteriaId}, Score: ${score}`);
            row.append(`<td class="text-center">${score}</td>`);
        });

        const total = supervisorData.total || 0;
        const average = supervisorData.average || 0;
        row.append(`<td class="text-center">${total}</td>`);
        

        supervisorBody.append(row);
    });

    $('#overall-supervisor-total').text(resp.overall_supervisor_totals || 0);
    $('#overall-supervisor-average').text(resp.overall_supervisor_average || 0);
}

// Function to clear the supervisor report data
function printReport() {
    // Clone the printable section
    const printableContent = document.getElementById('printable').cloneNode(true);

    // Create a new window for the printable content
    const printWindow = window.open('', '_blank', 'width=800,height=600');

    // Write the content to the new window
    printWindow.document.open();
    printWindow.document.write(`
        <html>
        <head>
            <title>Print Supervisor Evaluation Report</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                table, th, td { border: 1px solid black; text-align: center; padding: 8px; }
                h3, p { text-align: center; }
            </style>
        </head>
        <body>
            ${printableContent.innerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();

    // Trigger the print operation
    printWindow.print();

    // Close the print window after printing
    printWindow.onafterprint = () => printWindow.close();
}

</script>

