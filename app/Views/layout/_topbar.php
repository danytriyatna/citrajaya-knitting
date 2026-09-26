<nav class="navbar top-navbar navbar-expand-md navbar-dark">
  <div class="navbar-header">
    <a class="navbar-brand" href="./">
      <b>
        <img src="uploads/situs/<?= $logo; ?>" class="light-logo" style="max-height: 50px;" alt="" />
      </b>
      <span class="m-s-3">
        <img src="uploads/situs/<?= $logo_text; ?>" class="light-logo logo-text" style="max-width: 140px;" alt="" />
      </span>
    </a>
  </div>
  <div class="navbar-collapse">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"> <a class="nav-link nav-toggler d-block d-md-none waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
      <li class="nav-item"> <a class="nav-link sidebartoggler d-none d-lg-block d-md-block waves-effect waves-dark" href="javascript:void(0)"><i class="icon-menu"></i></a> </li>
    </ul>
    <ul class="navbar-nav my-lg-0">
      <!-- <li class="nav-item dropdown u-pro">
        <a class="nav-link dropdown-toggle waves-effect waves-dark profile-pic" href="javascript:void(0)" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-moon"></i></a>
        <div class="dropdown-menu dropdown-menu-end animated flipInY">
          <a href="javascript:void(0)" class="dropdown-item" onclick="toggleTheme()">Toggle Dark Mode</a>
        </div>
      </li> -->
      <li class="nav-item dropdown u-pro">
        <a class="nav-link dropdown-toggle waves-effect waves-dark profile-pic" href="javascript:void(0)" data-bs-toggle="dropdown"   aria-haspopup="true" aria-expanded="false"><img src="<?= $avatar != "" ? "uploads/users/$avatar" : "assets/images/no-image.webp" ?>" alt="user" class="" height="30px"> <span class="hidden-md-down"><?php echo $currentUser->full_name ?> &nbsp;<i class="fa fa-angle-down"></i></span> </a>
        <div class="dropdown-menu dropdown-menu-end animated flipInY">
          <a href="<?= base_url('profile'); ?>" class="dropdown-item"><i class="ti-user"></i> My Profile</a>
          <a href="<?= base_url('ganti-password'); ?>" class="dropdown-item"><i class="ti-settings"></i> Change Password</a>
          <div class="dropdown-divider"></div>
          <a href="<?= base_url('auth/logout'); ?>" class="dropdown-item"><i class="fa fa-power-off"></i> Logout</a>
        </div>
      </li>
    </ul>
  </div>
</nav>
