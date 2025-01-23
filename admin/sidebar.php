<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sidebar</title>

  <!-- Font Awesome for Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  
  <!-- Custom Sidebar CSS -->

</head>
<body>

  <!-- Sidebar HTML -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <div class="dropdown">
      <a href="./" class="brand-link">
        <h5 class="text-center p-0 m-0"><b>Admin</b></h5>
      </a>
    </div>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Dashboard -->
          <li class="nav-item dropdown">
            <a href="./" class="nav-link nav-home">
              <i class="nav-icon fas fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <!-- Questionnaires -->
          <li class="nav-item dropdown">
            <a href="./index.php?page=questionnaire" class="nav-link nav-questionnaire">
              <i class="nav-icon fas fa-question-circle"></i>
              <p>Questionnaires</p>
            </a>
          </li>
          <!-- Evaluation Criteria -->
          <li class="nav-item dropdown">
            <a href="./index.php?page=criteria_list" class="nav-link nav-criteria_list">
              <i class="nav-icon fas fa-check-circle"></i>
              <p>Evaluation Criteria</p>
            </a>
          </li>
          <!-- Year and Section -->
          <li class="nav-item dropdown">
            <a href="./index.php?page=class_list" class="nav-link nav-class_list">
              <i class="nav-icon fas fa-school"></i>
              <p>Year and Section</p>
            </a>
          </li>
          <!-- Academic Year -->
          <li class="nav-item dropdown">
            <a href="./index.php?page=academic_list" class="nav-link nav-academic_list">
              <i class="nav-icon fas fa-calendar-alt"></i>
              <p>Academic Year</p>
            </a>
          </li>
          <!-- Subjects -->
          <li class="nav-item dropdown">
            <a href="./index.php?page=subject_list" class="nav-link nav-subject_list">
              <i class="nav-icon fas fa-book"></i>
              <p>Subjects</p>
            </a>
          </li>
          <!-- Faculties -->
          <li class="nav-item">
            <a href="#" class="nav-link nav-edit_faculty">
              <i class="nav-icon fas fa-chalkboard-teacher"></i>
              <p>
                Faculty
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="./index.php?page=new_faculty" class="nav-link nav-new_faculty tree-item">
                  <i class="fas fa-plus nav-icon"></i>
                  <p>Add New</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./index.php?page=faculty_list" class="nav-link nav-faculty_list tree-item">
                  <i class="fas fa-list nav-icon"></i>
                  <p>List</p>
                </a>
              </li>
            </ul>
          </li>
          <!-- Supervisor -->
          <li class="nav-item">
            <a href="#" class="nav-link nav-edit_supervisor">
              <i class="nav-icon fas fa-user-tie"></i>
              <p>
                Supervisor
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="./index.php?page=new_supervisor" class="nav-link nav-new_supervisor tree-item">
                  <i class="fas fa-plus nav-icon"></i>
                  <p>Add New</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./index.php?page=supervisor_list" class="nav-link nav-supervisor_list tree-item">
                  <i class="fas fa-list nav-icon"></i>
                  <p>List</p>
                </a>
              </li>
            </ul>
          </li>
          <!-- Students -->
          <li class="nav-item">
            <a href="#" class="nav-link nav-edit_student">
              <i class="nav-icon fas fa-user-graduate"></i>
              <p>
                Students
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="./index.php?page=new_student" class="nav-link nav-new_student tree-item">
                  <i class="fas fa-plus nav-icon"></i>
                  <p>Add New</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./index.php?page=student_list" class="nav-link nav-student_list tree-item">
                  <i class="fas fa-list nav-icon"></i>
                  <p>List</p>
                </a>
              </li>
            </ul>
          </li>
          <!-- Evaluation Report -->
          <li class="nav-item">
          <a href="#" class="nav-link nav-report">
    <i class="nav-icon fas fa-file-alt"></i>
    <p>
       Evaluation Reports
        <i class="right fas fa-angle-left"></i>
    </p>
</a>
<ul class="nav nav-treeview">
    <!-- Student Evaluation Report Dropdown -->
    <li class="nav-item">
        <a href="./index.php?page=report" class="nav-link nav-student_evaluation_report tree-item">
            <i class="fas fa-chart-line nav-icon"></i>
            <p>Student Report</p>
        </a>
    </li>
      <!-- Supervisor Report Dropdown -->
    <li class="nav-item">
        <a href="./index.php?page=supervisor_report" class="nav-link nav-supervisor_report tree-item">
            <i class="fas fa-user-tie nav-icon"></i>
            <p>Supervisor Report</p>
        </a>
    </li>
    <!-- Summary Sheet Dropdown -->
    <li class="nav-item">
        <a href="./index.php?page=summary_sheet" class="nav-link nav-summary_sheet tree-item">
            <i class="fas fa-table nav-icon"></i>
            <p>Summary Sheet</p>
        </a>
    </li>
    
</ul>
   <!-- Academic Cycle Summary
<li class="nav-item dropdown">
            <a href="./index.php?page=academic_cycle" class="nav-link nav-academic_cycle">
              <i class="nav-icon fas fa-book"></i>
              <p>Academic Cycle</p>
            </a>
          </li>
        </ul>
        -->
      </nav>
    </div>
</aside>

<script>
  $(document).ready(function(){
    var page = '<?php echo isset($_GET['page']) ? $_GET['page'] : 'home' ?>';
    var s = '<?php echo isset($_GET['s']) ? $_GET['s'] : '' ?>';
    if(s != '')
      page = page + '_' + s;
    
    if($('.nav-link.nav-' + page).length > 0){
      $('.nav-link.nav-' + page).addClass('active');
      
      if($('.nav-link.nav-' + page).hasClass('tree-item') === true){
        $('.nav-link.nav-' + page).closest('.nav-treeview').siblings('a').addClass('active');
        $('.nav-link.nav-' + page).closest('.nav-treeview').parent().addClass('menu-open');
      }
      if($('.nav-link.nav-' + page).hasClass('nav-is-tree') === true){
        $('.nav-link.nav-' + page).parent().addClass('menu-open');
      }
    }
  });
    // Toggle Sidebar visibility
    function toggleSidebar() {
      const sidebar = document.querySelector('.main-sidebar');
      sidebar.classList.toggle('open');
    }
</script>

</body>
</html>
