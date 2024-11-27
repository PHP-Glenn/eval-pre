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

   <div class="row mb-1">
    <div class="col-md-12">
        <div class="d-flex justify-content-end w-100">
            <button class="btn btn-sm btn-success bg-gradient-primary mr-2" style="display:none" id="print-btn">
                <i class="fa fa-print"></i> Print
            </button>
            <button class="btn btn-sm btn-success bg-gradient-success" style="display:none" id="export-btn">
                <i class="fa fa-download"></i> Export to Excel
            </button>
        </div>
    </div>
</div>

</div>    
    <div class="row">
        <div class="col-md-3">
            <div class="callout callout-info">
                <div class="list-group" id="class-list"></div>
            </div>
        </div>

        
        <div class="col-md-9">
    <div class="callout callout-info" id="printable">
        <div>
            <div class="text-center">
                <h3 style="font-size: 14px; font-weight: bold;">Student Evaluation Report</h3>
                <hr>
                <p style="font-size: 12px; margin: 0;">ZAMBOANGA STATE POLYTECHNIC STATE UNIVERSITY</p>
                <p style="font-size: 12px; margin: 0;">R.T. Lim Boulevard, Zamboanga City</p>
                <p style="font-size: 12px; margin: 0;">National Budget Circular No. 461</p>
                <p style="font-size: 12px; margin: 0;">Qualitative Contribution Evaluation (QCE)</p>
                <p style="font-size: 12px; margin: 0;">For Instructor, Assistant Professor, and Associate Professor</p>
                <p style="font-size: 12px; margin: 0; font-weight: bold;">NINTH CYCLE</p>
                <p style="font-size: 12px; margin: 0;">July 1, 2019 - July 30, 2023</p>
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
        <td width="50%">
            <p><strong>Class:</strong> <span id="classField"></span></p>
        </td>
        <td width="50%">
            <p><strong>Subject:</strong> <span id="subjectField"></span></p>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <p><strong>Academic Rank:</strong> <span id="acad_rank"></span></p>
        </td>
    </tr>
</table>
<strong><p class="">Total Students Evaluated: <span id="tse"></span></p></strong>

            <table class="table table-condensed wborder">

    <thead>

        <tr>
            <th>School ID</th>
            <th>Commitment</th>
            <th>Knowledge of Subject Matter</th>
            <th>Teaching for Independent Learning</th>
            <th>Management of Learning</th>
            <th>Total</th>
            <th>Average</th>
        </tr>
    </thead>
    <tbody id="criteria-body">
    </tbody>
    <tfoot>
        <tr style="font-weight: bold; background: #f9f9f9;">
            <td>Overall</td>
            <td colspan="4"></td>
            <td id="overall-total" class="text-center"></td>
            <td id="overall-average" class="text-center"></td>
        </tr>
    </tfoot>
</table>
<noscript>
    <style>
         .list-group-item:hover {
        color: black !important;
        font-weight: 700 !important;
    }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table.wborder tr, table.wborder td, table.wborder th {
            border: 1px solid gray;
            padding: 3px;
        }
        table.wborder thead tr {
            background: #6c757d linear-gradient(180deg, #828a91, #6c757d) repeat-x !important;
            color: #fff;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
    
    </style>
</noscript>

<script>
$(document).ready(function() {
    $('#faculty_id').change(function() {
        if ($(this).val() > 0) {
            let newUrl = './index.php?page=report&fid=' + $(this).val();
            window.location.href = newUrl; 
        } else {
            clearReport();
        }
    });

    if ($('#faculty_id').val() > 0) {
        load_class();
    }
});

function load_report(faculty_id, subject_id, class_id) {
    start_load();
    $.ajax({
        url: 'ajax.php?action=get_report',
        method: 'POST',
        data: { faculty_id, subject_id, class_id },
        success: function (resp) {
            resp = JSON.parse(resp);

            if (!resp || (!resp.student_scores && !resp.supervisor_scores)) {
                clearReport();
                $('#print-btn').hide();
                $('#export-btn').hide();
                return;
            }

            $('#print-btn').show();
            $('#export-btn').show();
            $('#acad_rank').text(resp.acad_rank || 'N/A');


            populateStudentEvaluation(resp);
        },
        error: function (err) {
            console.error('Error fetching report:', err);
            alert_toast('An error occurred while fetching the report.', 'error');
        },
        complete: function () {
            end_load();
        }
    });
}
$('#export-btn').click(function () {
    const facultyId = $('#faculty_id').val();
    if (facultyId > 0) {
        const exportUrl = `export_to_excel.php?fid=${facultyId}`;
        window.location.href = exportUrl; // Redirect to the export script
    } else {
        alert('Please select a faculty to export the report.');
    }
});


function populateStudentEvaluation(resp) {
    $('#tse').text(resp.tse || 0);

    const studentBody = $('#criteria-body');
    studentBody.empty();

    const studentScores = resp.student_scores || {};
    const criteriaMap = resp.criteria_map || {};

    Object.keys(studentScores).forEach((schoolId) => {
        const studentData = studentScores[schoolId];
        const row = $('<tr></tr>');
        let total = 0;

        row.append(`<td>${schoolId}</td>`); 

        Object.keys(criteriaMap).forEach((criteriaId) => {
            const score = studentData[criteriaId]?.total_score || 0;
            total += score;
            row.append(`<td class="text-center">${score}</td>`);
        });

        const average = (total / Object.keys(criteriaMap).length).toFixed(2);
        row.append(`<td class="text-center">${total}</td>`);
        row.append(`<td class="text-center">${average}</td>`);

        studentBody.append(row);
    });

    $('#overall-total').text(resp.overall_totals || 0);
    $('#overall-average').text(resp.overall_average || 0);
}
function clearReport() {
    
    $('#tse').text('0');
    $('#criteria-body').html('<tr><td colspan="7" class="text-center text-muted">No data available.</td></tr>');
    $('#overall-total').text('');
    $('#overall-average').text('');
}

function load_class() {
    start_load();
    var fname = <?php echo json_encode($fname); ?>;
    $('#fname').text(fname[$('#faculty_id').val()]);
    $.ajax({
        url: "ajax.php?action=get_class",
        method: 'POST',
        data: {fid: $('#faculty_id').val()},
        error: function(err) {
            console.log('Error fetching class data:', err);
            alert_toast("An error occurred", 'error');
            end_load();
        },
        success: function(resp) {
            if (resp) {
                resp = JSON.parse(resp);
                if (Object.keys(resp).length <= 0) {
                    $('#class-list').html('<a href="javascript:void(0)" class="list-group-item list-group-item-action disabled">No data to display.</a>');
                } else {
                    $('#class-list').html('');
                    Object.keys(resp).map(k => {
                        $('#class-list').append('<a href="javascript:void(0)" data-json=\'' + JSON.stringify(resp[k]) + '\' data-id="' + resp[k].id + '" class="list-group-item list-group-item-action show-result">' + resp[k].class + ' - ' + resp[k].subj + '</a>');
                    });
                }
            }
        },
        complete: function() {
            end_load();
            anchor_func();
            if ('<?php echo isset($_GET['rid']); ?>' == 1) {
                $('.show-result[data-id="<?php echo isset($_GET['rid']) ? $_GET['rid'] : ''; ?>"]').trigger('click');
            } else {
                $('.show-result').first().trigger('click');
            }
        }
    });
}

function anchor_func() {
    $('.show-result').click(function() {
        var vars = [], hash;
        var data = $(this).attr('data-json');
        data = JSON.parse(data);
        var _href = location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for (var i = 0; i < _href.length; i++) {
            hash = _href[i].split('=');
            vars[hash[0]] = hash[1];
        }
        window.history.pushState({}, null, './index.php?page=report&fid=' + vars.fid + '&rid=' + data.id);
        load_report(vars.fid, data.sid, data.id);
        $('#subjectField').text(data.subj);
        $('#classField').text(data.class);
        $('.show-result.active').removeClass('active');
        $(this).addClass('active');
    });
}

$('#print-btn').click(function() {
    start_load();
    var ns = $('noscript').clone();
    var content = $('#printable').html();
    ns.append(content);
    var nw = window.open("Report", "_blank", "width=900,height=700");
    nw.document.write(ns.html());
    nw.document.close();
    nw.print();
    setTimeout(function() {
        nw.close();
        end_load();
    }, 750);
});
</script>

