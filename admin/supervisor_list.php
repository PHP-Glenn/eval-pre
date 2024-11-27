<?php include 'db_connect.php'; ?>
<div class="col-lg-12">
    <div class="card card-outline card-success">
        <div class="card-header">
            <div class="card-tools">
                <a class="btn btn-block btn-sm btn-default btn-flat border-primary" href="./index.php?page=new_supervisor"><i class="fa fa-plus"></i> Add New Supervisor</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table tabe-hover table-bordered" id="list">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>School ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Avatar</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    // Fetch supervisors from the supervisor_list table
                    $qry = $conn->query("SELECT *,concat(firstname,' ',middlename,' ',lastname) as name FROM supervisor_list order by concat(firstname,' ',middlename,' ',lastname) asc");
                    while($row= $qry->fetch_assoc()):
                    ?>
                    <tr>
                        <th class="text-center"><?php echo $i++ ?></th>
                        <td><b><?php echo $row['school_id'] ?></b></td>
                        <td><b><?php echo ucwords($row['name']) ?></b></td>
                        <td><b><?php echo $row['email'] ?></b></td>
                        <td class="text-center">
                            <img src="assets/uploads/<?php echo $row['avatar'] ?>" alt="Avatar" class="img-thumbnail" style="height: 50px; width: 50px;">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-default btn-sm btn-flat border-info wave-effect text-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                              Action
                            </button>
                            <div class="dropdown-menu" style="">
                              <a class="dropdown-item view_supervisor" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">View</a>
                              <div class="dropdown-divider"></div>
                              <a class="dropdown-item" href="./index.php?page=edit_supervisor&id=<?php echo $row['id'] ?>">Edit</a>
                              <div class="dropdown-divider"></div>
                              <a class="dropdown-item delete_supervisor" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">Delete</a>
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
    $(document).ready(function(){
        $('.view_supervisor').click(function(){
            uni_modal("<i class='fa fa-id-card'></i> Supervisor Details","<?php echo $_SESSION['login_view_folder'] ?>view_supervisor.php?id="+$(this).attr('data-id'))
        })
        $('.delete_supervisor').click(function(){
            _conf("Are you sure to delete this supervisor?","delete_supervisor",[$(this).attr('data-id')])
        })
        $('#list').dataTable()
    })
    function delete_supervisor($id){
        start_load()
        $.ajax({
            url:'ajax.php?action=delete_supervisor',
            method:'POST',
            data:{id:$id},
            success:function(resp){
                if(resp==1){
                    alert_toast("Data successfully deleted",'success')
                    setTimeout(function(){
                        location.reload()
                    },1500)
                }
            }
        })
    }
</script>
