<?php
?>
<div class="col-lg-12">
	<div class="card">
		<div class="card-body">
			<form action="" id="manage_faculty">
				<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
				<div class="row">
					<div class="col-md-6 border-right">
						<div class="form-group">
							<label for="" class="control-label">ID Number</label>
							<input type="number" name="school_id" class="form-control form-control-sm" required value="<?php echo isset($school_id) ? $school_id : '' ?>">
						</div>
						<div class="form-group">
							<label for="" class="control-label">First Name</label>
							<input type="text" name="firstname" class="form-control form-control-sm" required value="<?php echo isset($firstname) ? $firstname : '' ?>" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
						</div>
						<div class="form-group">
							<label for="" class="control-label">Middle Name</label>
							<input type="text" name="middle_name" class="form-control form-control-sm" required value="<?php echo isset($middle_name) ? $middle_name : '' ?>" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
						</div>
						<div class="form-group">
							<label for="" class="control-label">Last Name</label>
							<input type="text" name="lastname" class="form-control form-control-sm" required value="<?php echo isset($lastname) ? $lastname : '' ?>" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
						</div>
						<div class="form-group">
							<label for="" class="control-label">Academic Rank</label>
							<input type="text" name="acad_rank" class="form-control form-control-sm" required value="<?php echo isset($acad_rank) ? $acad_rank : '' ?>">
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="" class="control-label">Avatar</label>
							<div class="custom-file">
		                      <input type="file" class="custom-file-input" id="customFile" name="img" onchange="displayImg(this,$(this))">
		                      <label class="custom-file-label" for="customFile">Choose file</label>
		                    </div>
						</div>
						<div class="form-group d-flex justify-content-center align-items-center">
							<img src="<?php echo isset($avatar) ? 'assets/uploads/'.$avatar :'' ?>" alt="Avatar" id="cimg" class="img-fluid img-thumbnail ">
						</div>
						<div class="form-group">
							<label class="control-label">Email</label>
							<input type="email" class="form-control form-control-sm" name="email" required value="<?php echo isset($email) ? $email : '' ?>">
							<small id="#msg"></small>
						</div>
					</div>
				</div>
				<hr>
				<div class="col-lg-12 text-right justify-content-center d-flex">
					<button class="btn btn-primary mr-2">Save</button>
					<button class="btn btn-secondary" type="button" onclick="location.href = 'index.php?page=faculty_list'">Cancel</button>
				</div>
			</form>
		</div>
	</div>
</div>
<style>
	img#cimg{
		height: 15vh;
		width: 15vh;
		object-fit: cover;
		border-radius: 100% 100%;
	}
</style>
<script>
	$('#manage_faculty').submit(function(e){
		e.preventDefault()
		$('input').removeClass("border-danger")
		start_load()
		$('#msg').html('')

		$.ajax({
			url:'ajax.php?action=save_faculty',
			data: new FormData($(this)[0]),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    type: 'POST',
			success:function(resp){
				if(resp == 1){
					alert_toast('Data successfully saved.',"success");
					setTimeout(function(){
						location.replace('index.php?page=faculty_list')
					},750)
				}else if(resp == 2){
					$('#msg').html("<div class='alert alert-danger'>Email already exists.</div>");
					$('[name="email"]').addClass("border-danger")
					end_load()
				}else if(resp == 3){
					$('#msg').html("<div class='alert alert-danger'>School ID already exists.</div>");
					$('[name="school_id"]').addClass("border-danger")
					end_load()
				}
			}
		})
	})
</script>
