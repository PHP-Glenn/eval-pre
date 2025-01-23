<?php include 'db_connect.php'; ?>
<div style="width: 100%; margin: 20px 0;">
    <div style="border: 1px solid #007bff; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
        <div style="padding: 10px; background-color: #007bff; color: white; display: flex; justify-content: space-between; align-items: center; border-top-left-radius: 5px; border-top-right-radius: 5px;">
            <h4 style="margin: 0;">Manage Questionaire</h4>
            
        </div>
        <div style="padding: 15px;">
            <table style="width: 100%; border-collapse: collapse;" id="list">
                <colgroup>
                    <col width="5%">
                    <col width="35%">
                    <col width="15%">
                    <col width="15%">
                    <col width="15%">
                    <col width="15%">
                </colgroup>
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th style="text-align: center; padding: 8px;">#</th>
                        <th style="padding: 8px;">Academic Year</th>
                        <th style="padding: 8px;">Semester</th>
                        <th style="padding: 8px;">Questions</th>
                        <th style="padding: 8px;">Answered</th>
                        <th style="padding: 8px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $qry = $conn->query("SELECT * FROM academic_list ORDER BY ABS(year) DESC, ABS(semester) DESC");
                    while ($row = $qry->fetch_assoc()):
                        $questions = $conn->query("SELECT * FROM question_list WHERE academic_id = {$row['id']}")->num_rows;
                        $answers = $conn->query("SELECT * FROM evaluation_list WHERE academic_id = {$row['id']}")->num_rows;
                    ?>
                    <tr style="border-bottom: 1px solid #e9ecef;">
                        <th style="text-align: center; padding: 10px;"><?php echo $i++; ?></th>
                        <td style="padding: 10px;"><b><?php echo $row['year']; ?></b></td>
                        <td style="padding: 10px;"><b><?php echo $row['semester']; ?></b></td>
                        <td style="text-align: center; padding: 10px;"><b><?php echo number_format($questions); ?></b></td>
                        <td style="text-align: center; padding: 10px;"><b><?php echo number_format($answers); ?></b></td>
                        <td style="text-align: center; padding: 10px;">
                            <div style="position: relative; display: inline-block;">
                                <button type="button" style="background-color: white; border: 1px solid #17a2b8; color: #17a2b8; padding: 5px 10px; border-radius: 3px; cursor: pointer;" class="action-btn" data-id="<?php echo $row['id']; ?>">
                                    Action
                                </button>
                                <div class="dropdown-menu" style="display: none; position: absolute; background-color: white; border: 1px solid #e9ecef; border-radius: 5px; z-index: 1000; min-width: 160px;">
                                    <a class="dropdown-item manage_questionnaire" href="index.php?page=manage_questionnaire&id=<?php echo $row['id']; ?>" style="padding: 8px 10px; display: block; color: #007bff; text-decoration: none;">Manage</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item delete_academic" data-id="<?php echo $row['id']; ?>" style="padding: 8px 10px; display: block; color: red; text-decoration: none;">Delete</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.new_academic').click(function() {
            uni_modal("New academic", "<?php echo $_SESSION['login_view_folder'] ?>manage_academic.php");
        });

        // Show dropdown menu
        $(document).on('click', '.action-btn', function() {
            const dropdown = $(this).siblings('.dropdown-menu');
            dropdown.toggle();
        });

        // Manage academic
        $(document).on('click', '.manage_questionnaire', function() {
            uni_modal("Manage academic", $(this).attr('href'));
        });

        // Delete academic
        $(document).on('click', '.delete_academic', function() {
            const id = $(this).data('id');
            _conf("Are you sure to delete this academic?", "delete_academic", [id]);
        });

        $('#list').dataTable();
    });

    function delete_academic($id) {
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_academic',
            method: 'POST',
            data: { id: $id },
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Data successfully deleted", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                }
            }
        });
    }
</script>
