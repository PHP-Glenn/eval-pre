<style>
  .user-img {
    border-radius: 50%;
    height: 30px; /* Reduced height */
    width: 30px; /* Reduced width */
    object-fit: cover;
  }

  .centered-content {
    display: flex;
    justify-content: center;
    align-items: center;
    flex: 1; /* Allows the centered content to expand in available space */
  }

  .navbar-nav-left,
  .navbar-nav-right {
    display: flex;
    align-items: center;
  }

  .navbar-logo {
    height: 40px; /* Reduced height */
    width: auto;
    margin-right: 10px; /* Reduced margin for logos */
    margin-left: 10px; /* Add space between logo and content */
    background: none; /* Removes any white background for the logo */
  }

  .navbar-system-name {
    font-size: 20px; /* Adjusted font size */
    font-weight: bold;
    color: #fff;
    letter-spacing: 0.5px;
    text-align: center;
    white-space: nowrap; /* Prevents text from breaking onto new lines */
  }

  .main-header.navbar {
    height: 70px; /* Reduced navbar height */
    padding: 5px 25px; /* Reduced top and bottom padding */
    background: linear-gradient(135deg, #007bff, #00c6ff);
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: center; /* Adjusts center content and aligns logos closer */
    align-items: center;
  }

  .navbar-nav .nav-link {
    color: #fff;
    font-weight: 500;
    transition: background-color 0.3s ease, color 0.3s ease;
  }

  .navbar-nav .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: #f8f9fa;
    border-radius: 5px;
  }

  .dropdown-menu {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    padding: 10px 15px;
    transition: opacity 0.3s ease;
  }

  .dropdown-item {
    padding: 8px 15px;
    transition: background-color 0.3s ease, color 0.3s ease;
  }

  .dropdown-item:hover {
    background-color: #007bff;
    color: #fff;
  }

  .nav-link .fas.fa-bars,
  .fas.fa-expand-arrows-alt {
    color: white;
  }

  /* Adds margin between the logos and the system name */
  .navbar-nav-right .navbar-logo {
    margin-left: 15px; /* Adds space between the system name and the right logo */
  }

  .navbar-nav-left .navbar-logo {
    margin-right: 15px; /* Adds space between the left logo and the system name */
  }
</style>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-primary navbar-dark">
  <!-- Left navbar links -->
  <ul class="navbar-nav navbar-nav-left">
    <?php if(isset($_SESSION['login_id'])): ?>
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <?php endif; ?>
    <li>
      
        <img src="assets/uploads/zppsu.png" alt="Left Image" class="navbar-logo"> <!-- Left Image -->
      </a>
    </li>
  </ul>

  <!-- Centered system name -->
  <div class="centered-content">
    <span class="navbar-system-name"><?php echo $_SESSION['system']['name'] ?></span>
  </div>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto navbar-nav-right">
    <li>
      
        <img src="assets/uploads/cics.png" alt="Right Image" class="navbar-logo"> <!-- Right Image -->
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-widget="fullscreen" href="#" role="button">
        <i class="fas fa-expand-arrows-alt"></i>
      </a>
    </li>
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" aria-expanded="true" href="javascript:void(0)">
        <span>
          <div class="d-flex align-items-center badge-pill">
            <span><img src="assets/uploads/<?php echo $_SESSION['login_avatar'] ?>" alt="" class="user-img border"></span>
            <span class="ml-2"><b><?php echo ucwords($_SESSION['login_firstname']) ?></b></span>
            <span class="fa fa-angle-down ml-2"></span>
          </div>
        </span>
      </a>
      <div class="dropdown-menu" aria-labelledby="account_settings" style="left: -2.5em;">
        <a class="dropdown-item" href="javascript:void(0)" id="manage_account"><i class="fa fa-cog"></i> Manage Account</a>
        <a class="dropdown-item" href="ajax.php?action=logout"><i class="fa fa-power-off"></i> Logout</a>
      </div>
    </li>
  </ul>
</nav>

<script>
  $('#manage_account').click(function() {
    uni_modal('Manage Account', 'manage_user.php?id=<?php echo $_SESSION['login_id'] ?>');
  });
</script>
