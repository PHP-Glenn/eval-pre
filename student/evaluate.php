<?php 
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

$rid = isset($_GET['rid']) ? $_GET['rid'] : '';
$faculty_id = isset($_GET['fid']) ? $_GET['fid'] : '';
$subject_id = isset($_GET['sid']) ? $_GET['sid'] : '';

$restriction = $conn->query("
    SELECT r.id, s.id as sid, f.id as fid, 
           CONCAT(f.firstname, ' ', f.lastname) as faculty, 
           s.code, s.subject 
    FROM restriction_list r 
    INNER JOIN faculty_list f ON f.id = r.faculty_id 
    INNER JOIN subject_list s ON s.id = r.subject_id 
    WHERE academic_id = {$_SESSION['academic']['id']} 
      AND class_id = {$_SESSION['login_class_id']} 
      AND r.id NOT IN (
          SELECT restriction_id 
          FROM evaluation_list 
          WHERE academic_id = {$_SESSION['academic']['id']} 
            AND student_id = {$_SESSION['login_id']}
      )
");

$selected_faculty = 'N/A';
$selected_subject = 'N/A';

?><style>
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


<h4>Subjects to be Evaluated:</h4>
<div class="col-lg-12">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <?php 
                while ($row = $restriction->fetch_array()) {
                    if (empty($rid)) {
                        $rid = $row['id'];
                        $faculty_id = $row['fid'];
                        $subject_id = $row['sid'];
                    }

                    if ($rid == $row['id']) {
                        $selected_faculty = $row['faculty'];
                        $selected_subject = $row['subject'];
                    }
                ?>
                <a class="list-group-item list-group-item-action <?= $rid == $row['id'] ? 'active' : '' ?>" 
                   href="./index.php?page=evaluate&rid=<?= $row['id'] ?>&sid=<?= $row['sid'] ?>&fid=<?= $row['fid'] ?>">
                    <?= ucwords($row['faculty']).' - ('.$row["code"].') '.$row['subject'] ?>
                </a>
                <?php } ?>
            </div>
        </div>  

        <div class="col-md-9">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <b>Evaluation Questionnaire for Academic: <?= $_SESSION['academic']['year'].' '.ordinal_suffix($_SESSION['academic']['semester']) ?> </b>
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
                <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 10px;">
                    <span style="flex: 1; text-align: left;">Faculty: <?= $selected_faculty ?></span>
                    <span style="flex: 1; text-align: right;">Subject: <?= $selected_subject ?></span>
                </div>
                <p style="margin-left: 1px;"><b>Instruction:</b> Please evaluate the faculty using the scale below. Click your rating.</p>

                <fieldset class="border border-info p-2 w-100" style="font-size: 12px;">
                    <legend class="w-auto" style="font-size: 14px;">Rating Legend</legend>
                    <table class="table table-bordered text-center" style="font-size: 12px;">
                        <thead>
                            <tr style="background-color: #f8f9fa;">
                                <th style="width: 10%;">Scale</th>
                                <th style="width: 20%;">Descriptive Rating</th>
                                <th style="width: 70%;">Qualitative Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>5</td><td>Outstanding</td><td>Almost always exceeds the job requirements.</td></tr>
                            <tr><td>4</td><td>Very Satisfactory</td><td>Often exceeds the job requirements.</td></tr>
                            <tr><td>3</td><td>Satisfactory</td><td>Meets job requirements.</td></tr>
                            <tr><td>2</td><td>Fair</td><td>Needs some development.</td></tr>
                            <tr><td>1</td><td>Poor</td><td>Fails to meet job requirements.</td></tr>
                        </tbody>
                    </table>
                </fieldset>

                <form id="manage-evaluation">
                    <input type="hidden" name="class_id" value="<?= $_SESSION['login_class_id'] ?>">
                    <input type="hidden" name="faculty_id" value="<?= $faculty_id ?>">
                    <input type="hidden" name="restriction_id" value="<?= $rid ?>">
                    <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
                    <input type="hidden" name="academic_id" value="<?= $_SESSION['academic']['id'] ?>">

                    <?php 
                    $criteria = $conn->query("SELECT * FROM criteria_list WHERE id IN (SELECT criteria_id FROM question_list WHERE academic_id = {$_SESSION['academic']['id']}) ORDER BY abs(order_by) ASC");
                    while ($crow = $criteria->fetch_assoc()):
                    ?>
                    <div class="mb-3">
                        <h5 class="bg-gradient-secondary p-2"><?= $crow['criteria'] ?></h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>Question</th><th class="text-center" width="30%">Scale</th></tr>
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

<script>
	$(document).ready(function(){
		if('<?php echo $_SESSION['academic']['status'] ?>' == 0){
			uni_modal("Information","<?php echo $_SESSION['login_view_folder'] ?>not_started.php")
		}else if('<?php echo $_SESSION['academic']['status'] ?>' == 2){
			uni_modal("Information","<?php echo $_SESSION['login_view_folder'] ?>closed.php")
		}
		else if('<?php echo $_SESSION['academic']['status'] ?>' == 2){
			uni_modal("Information","<?php echo $_SESSION['login_view_folder'] ?>closed.php")
		}
		if(<?php echo empty($rid) ? 1 : 0 ?> == 1)
			uni_modal("Information","<?php echo $_SESSION['login_view_folder'] ?>done.php")
	})
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
            url: 'ajax.php?action=save_evaluation',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                if (resp == 1) {
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
