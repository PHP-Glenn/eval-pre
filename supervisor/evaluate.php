<?php
include('db_connect.php');

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

$faculty_id = isset($_GET['fid']) ? $_GET['fid'] : '';
?>
<style>
    .radio-inline {
    display: inline-block;
    margin-right: 10px;
    font-size: 16px; /* Increase font size for better readability */
}

.radio-inline input[type="radio"] {
    transform: scale(1.5); /* Increase the size of the radio button */
    margin-right: 5px; /* Add space between the button and the label */
    cursor: pointer; /* Change the cursor to a pointer for better user feedback */
}

.radio-inline label {
    font-size: 16px; /* Increase font size for labels */
    cursor: pointer; /* Ensure clicking the label selects the radio button */
}

/* Add spacing between each set of radio buttons */
.text-center td {
    padding: 8px 0;
}
</style>

<h4>Faculties to be Evaluated:</h4>
<div class="col-lg-12">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <!-- Faculty List -->
                <?php 
                $faculties = $conn->query("SELECT f.id as fid, CONCAT(f.firstname, ' ', f.lastname) as faculty 
                                            FROM faculty_list f 
                                            WHERE f.id NOT IN (SELECT faculty_id FROM supervisor_evaluation_list 
                                                               WHERE academic_id ={$_SESSION['academic']['id']} 
                                                               AND supervisor_id = {$_SESSION['login_id']})");
                while ($row = $faculties->fetch_array()): 
                    if (empty($faculty_id)) {
                        $faculty_id = $row['fid'];
                    }
                ?>
                <a class="list-group-item list-group-item-action <?= isset($faculty_id) && $faculty_id == $row['fid'] ? 'active' : '' ?>" 
                href="./index.php?page=evaluate&fid=<?= htmlspecialchars($row['fid'], ENT_QUOTES, 'UTF-8') ?>">
                <?= ucwords($row['faculty']) ?>
                </a>
                <?php endwhile; ?>
            </div>
        </div>  
        <div class="col-md-9">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <b>Evaluation Questionnaire for Academic: <?= $_SESSION['academic']['year'] . ' ' . ordinal_suffix($_SESSION['academic']['semester']) ?> </b>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-flat btn-primary bg-gradient-primary mx-1" form="manage-evaluation">Submit Evaluation</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="header-info text-center mb-3">
                        <p style="font-size: 14px; font-weight: bold; margin: 0;">ZAMBOANGA CITY STATE POLYTECHNIC COLLEGE</p>
                        <p style="font-size: 12px; margin: 0;">Region IX, Zamboanga Peninsula</p>
                        <p style="font-size: 12px; margin: 0;">R.T. Lim Blvd., Zamboanga City</p>
                        <p style="font-size: 12px; font-weight: bold; margin: 5px 0;">QCE for NBC No. 461 Instrument for Instruction</p>
                        <p style="font-size: 12px; font-weight: bold; margin: 0;">NINTH CYCLE</p>
                        <p style="font-size: 12px; margin: 0;">Rating Period: <u><?= date("F j, Y", strtotime($_SESSION['academic']['start_date'] ?? 'July 1, 2019')) ?></u> to <u><?= date("F j, Y", strtotime($_SESSION['academic']['end_date'] ?? 'June 30, 2022')) ?></u></p>
                    </div>
                    <fieldset class="border border-info p-2 w-100">
                       <legend class="w-auto">Rating Legend</legend>
                       <p>5 = Strongly Agree, 4 = Agree, 3 = Uncertain, 2 = Disagree, 1 = Strongly Disagree</p>
                    </fieldset>
                    <form id="manage-evaluation" method="POST">
                        <input type="hidden" name="faculty_id" value="<?= $faculty_id ?>">
                        <input type="hidden" name="supervisor_id" value="<?= $_SESSION['login_id'] ?>">
                        <input type="hidden" name="academic_id" value="<?= $_SESSION['academic']['id'] ?>">

                        <div class="clear-fix mt-2"></div>
                        <?php 
                        $criteria = $conn->query("SELECT * FROM criteria_list WHERE id IN 
                                                  (SELECT criteria_id FROM question_list WHERE academic_id = {$_SESSION['academic']['id']}) 
                                                  ORDER BY abs(order_by) ASC");
                        while ($crow = $criteria->fetch_assoc()):
                        ?>
                        <div class="mb-3">
                            <h5 class="bg-gradient-secondary p-2"><?= $crow['criteria'] ?></h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Question</th>
                                        <th class="text-center" width="30%">Scale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $questions = $conn->query("SELECT * FROM question_list WHERE criteria_id = {$crow['id']} AND academic_id = {$_SESSION['academic']['id']} ORDER BY abs(order_by) ASC");
                                    while ($row = $questions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['question'] ?><input type="hidden" name="qid[]" value="<?= $row['id'] ?>"></td>
                                        <td class="text-center">
                                            <?php for ($c = 5; $c >= 1; $c--): ?>
                                            <label class="radio-inline">
                                                <input type="radio" name="rate[<?= $row['id'] ?>]" value="<?= $c ?>"> <?= $c ?>
                                            </label>
                                            <?php endfor; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="text-right"><b>Total Score for <?= $crow['criteria'] ?>:</b> <span class="criteria-total" data-criteria="<?= $crow['id'] ?>">0</span></div>
                        </div>
                        <?php endwhile; ?>
                        <hr>
                        <div class="text-right mt-3" style="border-top: 1px solid #ccc; padding-top: 10px; margin-top: 20px;">
                            <b style="font-size: 25px;">Total Score for All Criteria:</b> 
                            <span id="total-score" style="font-size: 20px; font-weight: bold;">0</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function updateCriteriaTotals() {
        $('.criteria-total').each(function() {
            let criteriaId = $(this).data('criteria'), total = 0;
            $(`input[name^="rate"]:checked`).each(function() {
                let questionId = $(this).attr('name').match(/\d+/)[0];
                if ($(`input[name="rate[${questionId}]"]`).closest('table').parent().find('.criteria-total').data('criteria') == criteriaId) {
                    total += parseInt($(this).val());
                }
            });
            $(this).text(total);
        });
    }

    function updateTotalScore() {
        let totalScore = 0;
        $('.criteria-total').each(function() {
            totalScore += parseInt($(this).text()) || 0;
        });
        $('#total-score').text(totalScore);
    }

    $('input[type="radio"]').change(function() {
        updateCriteriaTotals();
        updateTotalScore();
    });

    $('#manage-evaluation').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax.php?action=save_supervisor_evaluation',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                var response = JSON.parse(resp);
                if (response.status == 1) {
                    alert("Evaluation successfully saved.");
                    location.reload();
                }
            }
        });
    });

    updateCriteriaTotals();
    updateTotalScore();
});
</script>
