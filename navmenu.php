<?php echo '<div id="navmenu">';
  echo '<span><a href="index.php" >Home</a></span>';
  if(isset($_SESSION['username'])){
    echo '<span>&#10084<a href ="viewprofile.php"> View Profile </a></span>'; 
  echo '<span>&#10084<a href ="editprofile.php"> Edit Profile </a></span>'; 
  echo '<span id="log1">&#10084<a href ="logout.php"> Logout ('.$_SESSION['username'].') </a></span>';
  echo '<span>&#10084<a href="mytangle.php">My Tangle</a></span>';
  echo '<span>&#10084<a href="questionnare.php">Questionnare</a></span>';
  echo '<span id="changepassword"><a href="changepassword.php">Change Password</a><span>';
  } else{
  echo '<span>&#10084<a href="login.php">Login</a></span>';
  echo '<span>&#10084<a href="signup.php">Sign Up to Tangle</a></span>';}
  
  echo '</div>'; ?>