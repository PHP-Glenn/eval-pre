<?php
session_start();
ini_set('display_errors', 1);
Class Action {
	private $db;

	public function __construct() {
		ob_start();
   	include 'db_connect.php';
    
    $this->db = $conn;
	}
	function __destruct() {
	    $this->db->close();
	    ob_end_flush();
	}

	function login(){
		extract($_POST);
		$type = array("","users","faculty_list","student_list", "supervisor_list");
    $type2 = array("","admin","faculty","student", "supervisor");
	
		// Query to fetch the user based on email
		$qry = $this->db->query("SELECT *,concat(firstname,' ',lastname) as name FROM {$type[$login]} WHERE email = '".$email."'");
		
		// Check if the query returns a result
		if($qry->num_rows > 0){
			$user = $qry->fetch_array();
	
			// Verify the password using password_verify()
			if (password_verify($password, $user['password'])) {
				foreach ($user as $key => $value) {
					if ($key != 'password' && !is_numeric($key)) {
						$_SESSION['login_'.$key] = $value;
					}
				}
	
				$_SESSION['login_type'] = $login;
				$_SESSION['login_view_folder'] = $type2[$login].'/';
	
				// Load academic settings
				$academic = $this->db->query("SELECT * FROM academic_list WHERE is_default = 1 ");
				if ($academic->num_rows > 0) {
					foreach ($academic->fetch_array() as $k => $v) {
						if (!is_numeric($k)) {
							$_SESSION['academic'][$k] = $v;
						}
					}
				}
	
				return 1;  // Login successful
			} else {
				return 2;  // Incorrect password
			}
		} else {
			return 2;  // No user found
		}
	}
	
	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function login2(){
		extract($_POST);
			$qry = $this->db->query("SELECT *,concat(lastname,', ',firstname,' ',middlename) as name FROM students where student_code = '".$student_code."' ");
		if($qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['rs_'.$key] = $value;
			}
				return 1;
		}else{
			return 3;
		}
	}
	function save_supervisor(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass','password')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
	
		// Check if password is provided, and if so, hash it
		if(!empty($password)){
			$hashed_password = password_hash($password, PASSWORD_DEFAULT); // Use password_hash for hashing
			$data .= ", password='$hashed_password' ";
		}
	
		// Check if the email already exists (excluding the current user ID in case of updates)
		$check = $this->db->query("SELECT * FROM supervisor_list WHERE email ='$email' ".(!empty($id) ? " AND id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2; // Email already exists
			exit;
		}
	
		// Handle avatar file upload if available
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";
		}
	
		// If the ID is empty, create a new record; otherwise, update the existing record
		if(empty($id)){
			$save = $this->db->query("INSERT INTO supervisor_list SET $data");
		} else {
			$save = $this->db->query("UPDATE supervisor_list SET $data WHERE id = $id");
		}
	
		if($save){
			return 1; // Success
		}
	}
	function delete_supervisor(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM supervisor_list WHERE id = ".$id);
		if($delete){
			return 1;
		}
	}
	
	
	function save_user(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass','password')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(!empty($password)){
					$data .= ", password=md5('$password') ";

		}
		$check = $this->db->query("SELECT * FROM users where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set $data");
		}else{
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if($save){
			return 1;
		}
	}
	function signup(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass')) && !is_numeric($k)){
				if($k =='password'){
					if(empty($v))
						continue;
					$v = md5($v);

				}
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}

		$check = $this->db->query("SELECT * FROM users where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set $data");

		}else{
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if($save){
			if(empty($id))
				$id = $this->db->insert_id;
			foreach ($_POST as $key => $value) {
				if(!in_array($key, array('id','cpass','password')) && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
					$_SESSION['login_id'] = $id;
				if(isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
					$_SESSION['login_avatar'] = $fname;
			return 1;
		}
	}

	function update_user(){
		extract($_POST);
		$data = "";
		$type = array("","users","faculty_list","student_list");
	foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass','table','password')) && !is_numeric($k)){
				
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$check = $this->db->query("SELECT * FROM {$type[$_SESSION['login_type']]} where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		if(!empty($password))
			$data .= " ,password=md5('$password') ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO {$type[$_SESSION['login_type']]} set $data");
		}else{
			echo "UPDATE {$type[$_SESSION['login_type']]} set $data where id = $id";
			$save = $this->db->query("UPDATE {$type[$_SESSION['login_type']]} set $data where id = $id");
		}

		if($save){
			foreach ($_POST as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			if(isset($_FILES['img']) && !empty($_FILES['img']['tmp_name']))
					$_SESSION['login_avatar'] = $fname;
			return 1;
		}
	}
	function delete_user(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = ".$id);
		if($delete)
			return 1;
	}
	function save_system_settings(){
		extract($_POST);
		$data = '';
		foreach($_POST as $k => $v){
			if(!is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if($_FILES['cover']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['cover']['name'];
			$move = move_uploaded_file($_FILES['cover']['tmp_name'],'../assets/uploads/'. $fname);
			$data .= ", cover_img = '$fname' ";

		}
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set $data where id =".$chk->fetch_array()['id']);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set $data");
		}
		if($save){
			foreach($_POST as $k => $v){
				if(!is_numeric($k)){
					$_SESSION['system'][$k] = $v;
				}
			}
			if($_FILES['cover']['tmp_name'] != ''){
				$_SESSION['system']['cover_img'] = $fname;
			}
			return 1;
		}
	}
	function save_image(){
		extract($_FILES['file']);
		if(!empty($tmp_name)){
			$fname = strtotime(date("Y-m-d H:i"))."_".(str_replace(" ","-",$name));
			$move = move_uploaded_file($tmp_name,'assets/uploads/'. $fname);
			$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
			$hostName = $_SERVER['HTTP_HOST'];
			$path =explode('/',$_SERVER['PHP_SELF']);
			$currentPath = '/'.$path[1]; 
			if($move){
				return $protocol.'://'.$hostName.$currentPath.'/assets/uploads/'.$fname;
			}
		}
	}
	function save_subject(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','user_ids')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$chk = $this->db->query("SELECT * FROM subject_list where code = '$code' and id != '{$id}' ")->num_rows;
		if($chk > 0){
			return 2;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO subject_list set $data");
		}else{
			$save = $this->db->query("UPDATE subject_list set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
	function delete_subject(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM subject_list where id = $id");
		if($delete){
			return 1;
		}
	}
	function save_class(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','user_ids')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$chk = $this->db->query("SELECT * FROM class_list where (".str_replace(",",'and',$data).") and id != '{$id}' ")->num_rows;
		if($chk > 0){
			return 2;
		}
		if(isset($user_ids)){
			$data .= ", user_ids='".implode(',',$user_ids)."' ";
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO class_list set $data");
		}else{
			$save = $this->db->query("UPDATE class_list set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
	function delete_class(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM class_list where id = $id");
		if($delete){
			return 1;
		}
	}
	function save_academic(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','user_ids')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$chk = $this->db->query("SELECT * FROM academic_list where (".str_replace(",",'and',$data).") and id != '{$id}' ")->num_rows;
		if($chk > 0){
			return 2;
		}
		$hasDefault = $this->db->query("SELECT * FROM academic_list where is_default = 1")->num_rows;
		if($hasDefault == 0){
			$data .= " , is_default = 1 ";
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO academic_list set $data");
		}else{
			$save = $this->db->query("UPDATE academic_list set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
	function delete_academic(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM academic_list where id = $id");
		if($delete){
			return 1;
		}
	}
	function make_default(){
		extract($_POST);
		$update= $this->db->query("UPDATE academic_list set is_default = 0");
		$update1= $this->db->query("UPDATE academic_list set is_default = 1 where id = $id");
		$qry = $this->db->query("SELECT * FROM academic_list where id = $id")->fetch_array();
		if($update && $update1){
			foreach($qry as $k =>$v){
				if(!is_numeric($k))
					$_SESSION['academic'][$k] = $v;
			}

			return 1;
		}
	}
	function save_criteria(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','user_ids')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$chk = $this->db->query("SELECT * FROM criteria_list where (".str_replace(",",'and',$data).") and id != '{$id}' ")->num_rows;
		if($chk > 0){
			return 2;
		}
		
		if(empty($id)){
			$lastOrder= $this->db->query("SELECT * FROM criteria_list order by abs(order_by) desc limit 1");
		$lastOrder = $lastOrder->num_rows > 0 ? $lastOrder->fetch_array()['order_by'] + 1 : 0;
		$data .= ", order_by='$lastOrder' ";
			$save = $this->db->query("INSERT INTO criteria_list set $data");
		}else{
			$save = $this->db->query("UPDATE criteria_list set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
	function delete_criteria(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM criteria_list where id = $id");
		if($delete){
			return 1;
		}
	}
	function save_criteria_order(){
		extract($_POST);
		$data = "";
		foreach($criteria_id as $k => $v){
			$update[] = $this->db->query("UPDATE criteria_list set order_by = $k where id = $v");
		}
		if(isset($update) && count($update)){
			return 1;
		}
	}

	function save_question(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','user_ids')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		
		if(empty($id)){
			$lastOrder= $this->db->query("SELECT * FROM question_list where academic_id = $academic_id order by abs(order_by) desc limit 1");
			$lastOrder = $lastOrder->num_rows > 0 ? $lastOrder->fetch_array()['order_by'] + 1 : 0;
			$data .= ", order_by='$lastOrder' ";
			$save = $this->db->query("INSERT INTO question_list set $data");
		}else{
			$save = $this->db->query("UPDATE question_list set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
	function delete_question(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM question_list where id = $id");
		if($delete){
			return 1;
		}
	}
	function save_question_order(){
		extract($_POST);
		$data = "";
		foreach($qid as $k => $v){
			$update[] = $this->db->query("UPDATE question_list set order_by = $k where id = $v");
		}
		if(isset($update) && count($update)){
			return 1;
		}
	}
	function save_faculty(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass','password')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(!empty($password)){
					$data .= ", password=md5('$password') ";

		}
		$check = $this->db->query("SELECT * FROM faculty_list where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		$check = $this->db->query("SELECT * FROM faculty_list where school_id ='$school_id' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 3;
			exit;
		}
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO faculty_list set $data");
		}else{
			$save = $this->db->query("UPDATE faculty_list set $data where id = $id");
		}

		if($save){
			return 1;
		}
	}
	function delete_faculty(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM faculty_list where id = ".$id);
		if($delete)
			return 1;
	}

	function save_student(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass','password')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				} else {
					$data .= ", $k='$v' ";
				}
			}
		}
		
		// Check if password is provided, and if so, hash it
		if(!empty($password)){
			$hashed_password = password_hash($password, PASSWORD_DEFAULT); // Use password_hash for hashing
			$data .= ", password='$hashed_password' ";
		}
	
		// Check if the email already exists (excluding the current user ID in case of updates)
		$check = $this->db->query("SELECT * FROM student_list WHERE email ='$email' ".(!empty($id) ? " AND id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2; // Email already exists
			exit;
		}
	
		// Handle avatar file upload if available
		if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";
		}
	
		// If the ID is empty, create a new record; otherwise, update the existing record
		if(empty($id)){
			$save = $this->db->query("INSERT INTO student_list SET $data");
		} else {
			$save = $this->db->query("UPDATE student_list SET $data WHERE id = $id");
		}
	
		if($save){
			return 1; // Success
		}
	}
	
	function delete_student(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM student_list where id = ".$id);
		if($delete)
			return 1;
	}
	function save_task(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if($k == 'description')
					$v = htmlentities(str_replace("'","&#x2019;",$v));
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO task_list set $data");
		}else{
			$save = $this->db->query("UPDATE task_list set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
	function delete_task(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM task_list where id = $id");
		if($delete){
			return 1;
		}
	}
	function save_progress(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if($k == 'progress')
					$v = htmlentities(str_replace("'","&#x2019;",$v));
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		if(!isset($is_complete))
			$data .= ", is_complete=0 ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO task_progress set $data");
		}else{
			$save = $this->db->query("UPDATE task_progress set $data where id = $id");
		}
		if($save){
		if(!isset($is_complete))
			$this->db->query("UPDATE task_list set status = 1 where id = $task_id ");
		else
			$this->db->query("UPDATE task_list set status = 2 where id = $task_id ");
			return 1;
		}
	}
	function delete_progress(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM task_progress where id = $id");
		if($delete){
			return 1;
		}
	}
	function save_restriction(){
		extract($_POST);
		$filtered = implode(",",array_filter($rid));
		if(!empty($filtered))
			$this->db->query("DELETE FROM restriction_list where id not in ($filtered) and academic_id = $academic_id");
		else
			$this->db->query("DELETE FROM restriction_list where  academic_id = $academic_id");
		foreach($rid as $k => $v){
			$data = " academic_id = $academic_id ";
			$data .= ", faculty_id = {$faculty_id[$k]} ";
			$data .= ", class_id = {$class_id[$k]} ";
			$data .= ", subject_id = {$subject_id[$k]} ";
			if(empty($v)){
				$save[] = $this->db->query("INSERT INTO restriction_list set $data ");
			}else{
				$save[] = $this->db->query("UPDATE restriction_list set $data where id = $v ");
			}
		}
			return 1;
	}
	function save_evaluation(){
		extract($_POST);
		$data = " student_id = {$_SESSION['login_id']} ";
		$data .= ", academic_id = $academic_id ";
		$data .= ", subject_id = $subject_id ";
		$data .= ", class_id = $class_id ";
		$data .= ", restriction_id = $restriction_id ";
		$data .= ", faculty_id = $faculty_id ";
		$save = $this->db->query("INSERT INTO evaluation_list set $data");
		if($save){
			$eid = $this->db->insert_id;
			foreach($qid as $k => $v){
				$data = " evaluation_id = $eid ";
				$data .= ", question_id = $v ";
				$data .= ", rate = {$rate[$v]} ";
				$ins[] = $this->db->query("INSERT INTO evaluation_answers set $data ");
			}
			if(isset($ins))
				return 1;
		}
	}
	function save_supervisor_evaluation() {
		extract($_POST);
		
		error_log("Received qid: " . print_r($qid, true));
		error_log("Received rate: " . print_r($rate, true));
	
		$data = " faculty_id = $faculty_id ";
		$data .= ", supervisor_id = {$_SESSION['login_id']} ";
		$data .= ", academic_id = $academic_id ";
	
		$save = $this->db->query("INSERT INTO supervisor_evaluation_list SET $data");
	
		if ($save) {
			$eid = $this->db->insert_id; 
			foreach ($qid as $k => $v) {
				$data = " evaluation_id = $eid ";
				$data .= ", question_id = $v ";
				$data .= ", rate = {$rate[$v]} ";
				$ins[] = $this->db->query("INSERT INTO supervisor_evaluation_answers SET $data ");
				error_log("Inserting rate for question $v: {$rate[$v]}");
			}
			if (isset($ins)) {
				return json_encode(['status' => 1]);
			}
		}
		return json_encode(['status' => 0, 'error' => 'Failed to save evaluation.']);
	}
	
	
	function get_class(){
		extract($_POST);
		$data = array();
		$get = $this->db->query("SELECT c.id,concat(c.curriculum,' ',c.level,' - ',c.section) as class,s.id as sid,concat(s.code,' - ',s.subject) as subj FROM restriction_list r inner join class_list c on c.id = r.class_id inner join subject_list s on s.id = r.subject_id where r.faculty_id = {$fid} and academic_id = {$_SESSION['academic']['id']} ");
		while($row= $get->fetch_assoc()){
			$data[]=$row;
		}
		return json_encode($data);

	}
	function get_summary() {
		extract($_POST);
	
		if ($_GET['action'] == 'get_summary') {
			$faculty_id = $_POST['faculty_id'];
	
			if (!$faculty_id) {
				echo json_encode(['status' => 'error', 'message' => 'No faculty ID provided.']);
				exit;
			}
	
			try {
				// Fetch default academic year
				$default_academic_query = $this->db->query("SELECT * FROM academic_list WHERE is_default = 1 LIMIT 1");
				if ($default_academic_query->num_rows <= 0) {
					throw new Exception("Default academic year not found.");
				}
	
				$default_academic = $default_academic_query->fetch_assoc();
				$academic_id = $default_academic['id'];
				$academic_year = $default_academic['year'];
				$academic_semester = $default_academic['semester'];
	
				// Fetch faculty data
				$faculty_query = $this->db->query("
					SELECT CONCAT(firstname, ' ', lastname) AS faculty_name 
					FROM faculty_list 
					WHERE id = $faculty_id
				");
	
				if (!$faculty_query || $faculty_query->num_rows <= 0) {
					throw new Exception("Faculty not found.");
				}
	
				$faculty_data = $faculty_query->fetch_assoc();
				$faculty_name = $faculty_data['faculty_name'];
	
				// Fetch evaluation data for students
				$evaluation_query = $this->db->query("
					SELECT 
						sl.school_id,
						ea.question_id,
						SUM(ea.rate) AS score
					FROM evaluation_answers ea
					INNER JOIN evaluation_list el ON el.evaluation_id = ea.evaluation_id
					INNER JOIN student_list sl ON sl.id = el.student_id
					WHERE el.faculty_id = $faculty_id
					AND el.academic_id = $academic_id
					GROUP BY sl.school_id, ea.question_id
				");
	
				$student_scores = [];
				$overall_total = 0;
	
				while ($row = $evaluation_query->fetch_assoc()) {
					$school_id = $row['school_id'];
					$score = $row['score'];
					$student_scores[$school_id]['total_score'] =
						($student_scores[$school_id]['total_score'] ?? 0) + $score;
					$overall_total += $score;
				}
	
				$student_count = count($student_scores);
				$students_average = $student_count > 0 ? round($overall_total / $student_count, 2) : 0;
	
				// Fetch evaluation data for supervisors
				$supervisor_query = $this->db->query("
					SELECT 
						sel.supervisor_id,
						SUM(sea.rate) AS total_score
					FROM supervisor_evaluation_answers sea
					INNER JOIN supervisor_evaluation_list sel ON sel.id = sea.evaluation_id
					WHERE sel.faculty_id = $faculty_id
					AND sel.academic_id = $academic_id
					GROUP BY sel.supervisor_id
				");
	
				$supervisor_total = 0;
				$supervisor_count = 0;
	
				while ($row = $supervisor_query->fetch_assoc()) {
					$supervisor_total += $row['total_score'];
					$supervisor_count++;
				}
	
				$criteria_count = 4;
				$max_total_score = $criteria_count * 25; 
				$supervisor_average = $supervisor_count > 0
					? round(($supervisor_total / ($supervisor_count * $max_total_score)) * 100, 2)
					: 0;
	
				$students_weight = 60; 
				$supervisor_weight = 40; 
	
				$weighted_average = round(
					(($students_average * $students_weight) + ($supervisor_average * $supervisor_weight)) / 100,
					2
				);
	
				$response = [
					'status' => 'success',
					'data' => [
						'faculty_name' => $faculty_name,
						'academic_year' => $academic_year,
						'academic_semester' => $academic_semester,
						'evaluators' => [
							[
								'evaluator' => 'Students',
								'average_rating' => $students_average,
								'weight' => $students_weight,
								'equivalent_point' => round(($students_average * $students_weight) / 100, 2),
							],
							[
								'evaluator' => 'Supervisor',
								'average_rating' => $supervisor_average,
								'weight' => $supervisor_weight,
								'equivalent_point' => round(($supervisor_average * $supervisor_weight) / 100, 2),
							],
						],
						'total_weighted_average' => $weighted_average,
					],
				];
	
				echo json_encode($response);
			} catch (Exception $e) {
				echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
			}
	
			exit;
		}
	}
	
	
	function get_report() {
		extract($_POST);
	
		$faculty_id = intval($faculty_id ?? 0);
		$subject_id = intval($subject_id ?? 0);
		$class_id = intval($class_id ?? 0);
	
		$data = [
			'tse' => 0,
			'student_scores' => [],
			'overall_totals' => 0,
			'overall_average' => 0,
			'criteria_map' => [],
			'acad_rank' => '',
		];
	
		// Get Academic Rank
		$faculty_query = $this->db->query("
			SELECT acad_rank 
			FROM faculty_list 
			WHERE id = $faculty_id
		");
	
		if ($faculty_query->num_rows > 0) {
			$faculty_row = $faculty_query->fetch_assoc();
			$data['acad_rank'] = $faculty_row['acad_rank'];
		}
	
		// Get Criteria and Map Questions
		$criteria_query = $this->db->query("
			SELECT q.id AS question_id, c.id AS criteria_id, c.criteria
			FROM question_list q
			INNER JOIN criteria_list c ON c.id = q.criteria_id
			WHERE q.academic_id = {$_SESSION['academic']['id']}
		");
	
		$criteria_map = [];
		while ($row = $criteria_query->fetch_assoc()) {
			$criteria_map[$row['criteria_id']]['criteria'] = $row['criteria'];
			$criteria_map[$row['criteria_id']]['questions'][] = $row['question_id'];
		}
		$data['criteria_map'] = $criteria_map;
	
		// Get Student Evaluation Data
		$evaluation_query = $this->db->query("
			SELECT 
				sl.school_id,
				ea.question_id,
				SUM(ea.rate) AS score
			FROM evaluation_answers ea
			INNER JOIN evaluation_list el ON el.evaluation_id = ea.evaluation_id
			INNER JOIN student_list sl ON sl.id = el.student_id
			WHERE el.academic_id = {$_SESSION['academic']['id']}
			  AND el.faculty_id = $faculty_id
			  AND el.subject_id = $subject_id
			  AND el.class_id = $class_id
			GROUP BY sl.school_id, ea.question_id
		");
	
		$student_scores = [];
		while ($row = $evaluation_query->fetch_assoc()) {
			$school_id = $row['school_id'];
			$question_id = $row['question_id'];
			$score = $row['score'];
	
			foreach ($criteria_map as $criteria_id => $criteria_data) {
				if (in_array($question_id, $criteria_data['questions'])) {
					$student_scores[$school_id][$criteria_id]['criteria'] = $criteria_data['criteria'];
					$student_scores[$school_id][$criteria_id]['total_score'] =
						($student_scores[$school_id][$criteria_id]['total_score'] ?? 0) + $score;
					break;
				}
			}
		}
	
		$overall_total = 0;
		foreach ($student_scores as $school_id => $criteria_scores) {
			$total = array_sum(array_column($criteria_scores, 'total_score'));
			$student_scores[$school_id]['total'] = $total;
			$student_scores[$school_id]['average'] = round($total / count($criteria_map), 2);
			$overall_total += $total;
		}
	
		$data['tse'] = count($student_scores);
		$data['student_scores'] = $student_scores;
		$data['overall_totals'] = $overall_total;
		$data['overall_average'] = count($student_scores) > 0
			? round($overall_total / count($student_scores), 2)
			: 0;
	
		return json_encode($data);
	}


	function get_supervisor_report() {
		extract($_POST);
	
		$faculty_id = intval($faculty_id ?? 0);
	
		$data = [
			'tse_supervisor' => 0, // Total supervisors evaluated
			'supervisor_scores' => [], // Supervisor scores
			'overall_supervisor_totals' => 0,
			'overall_supervisor_average' => 0,
			'criteria_map' => [],
			'acad_rank' => '',
		];
	
		// Step 1: Get Academic Rank
		$faculty_query = $this->db->query("
    SELECT acad_rank 
    FROM faculty_list 
    WHERE id = $faculty_id
");

	
		if ($faculty_query->num_rows > 0) {
			$faculty_row = $faculty_query->fetch_assoc();
			$data['acad_rank'] = $faculty_row['acad_rank']; // Assign academic rank to the response
			
		}
		
	
		// Step 2: Get Criteria and Map Questions
		$criteria_query = $this->db->query("
			SELECT q.id AS question_id, c.id AS criteria_id, c.criteria
			FROM question_list q
			INNER JOIN criteria_list c ON c.id = q.criteria_id
			WHERE q.academic_id = {$_SESSION['academic']['id']}
		");
	
		$criteria_map = [];
		while ($row = $criteria_query->fetch_assoc()) {
			$criteria_map[$row['criteria_id']]['criteria'] = $row['criteria'];
			$criteria_map[$row['criteria_id']]['questions'][] = $row['question_id'];
		}
		$data['criteria_map'] = $criteria_map;
	
		// Step 3: Get Supervisor Evaluation Data
		$supervisor_query = $this->db->query("
			SELECT 
				sl.school_id,
				sea.question_id,
				SUM(sea.rate) AS score
			FROM supervisor_evaluation_answers sea
			INNER JOIN supervisor_evaluation_list sel ON sel.id = sea.evaluation_id
			INNER JOIN supervisor_list sl ON sl.id = sel.supervisor_id
			WHERE sel.academic_id = {$_SESSION['academic']['id']}
			  AND sel.faculty_id = $faculty_id
			GROUP BY sl.school_id, sea.question_id
		");
	
		$supervisor_scores = [];
		while ($row = $supervisor_query->fetch_assoc()) {
			$school_id = $row['school_id'];
			$question_id = $row['question_id'];
			$score = $row['score'];
	
			foreach ($criteria_map as $criteria_id => $criteria_data) {
				if (in_array($question_id, $criteria_data['questions'])) {
					$supervisor_scores[$school_id][$criteria_id]['criteria'] = $criteria_data['criteria'];
					$supervisor_scores[$school_id][$criteria_id]['total_score'] =
						($supervisor_scores[$school_id][$criteria_id]['total_score'] ?? 0) + $score;
					break;
				}
			}
		}
	
		// Step 4: Calculate Totals and Averages
		$overall_total = 0;
		foreach ($supervisor_scores as $school_id => $criteria_scores) {
			$total = array_sum(array_column($criteria_scores, 'total_score'));
			$supervisor_scores[$school_id]['total'] = $total;
			$supervisor_scores[$school_id]['average'] = count($criteria_map) > 0
				? round($total / count($criteria_map), 2)
				: 0;
			$overall_total += $total;
		}
	
		$data['tse_supervisor'] = count($supervisor_scores);
		$data['supervisor_scores'] = $supervisor_scores;
		$data['overall_supervisor_totals'] = $overall_total;
		$data['overall_supervisor_average'] = count($supervisor_scores) > 0
			? round($overall_total / count($supervisor_scores), 2)
			: 0;
	
		return json_encode($data);
	}
	
};	