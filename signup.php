
<?php
$page_title= 'Sign-Up';
require_once('header.php');
require_once('appvars.php');
require_once('connectvars.php');
$dbc = mysqli_connect(DB_HOST, DB_USER,DB_PASSWORD,DB_NAME);
if(isset($_POST['submit'])){
	$username=mysqli_real_escape_string($dbc,trim($_POST['username']));
	$password1=mysqli_real_escape_string($dbc,trim($_POST['password1']));
	$password2=mysqli_real_escape_string($dbc,trim($_POST['password2']));
	if(!empty($username) && !empty($password1) && !empty($password2) && ($password1==$password2)){
		//Making sure username doesn't exists
	$query = "SELECT * FROM tangle_user WHERE username ='$username'";
	$data = mysqli_query($dbc,$query);
	if(mysqli_num_rows($data)==0){
		//Unique username
		$query = "INSERT INTO tangle_user (username,password,join_date) VALUES ('$username',SHA('$password1'),NOW())";
		mysqli_query($dbc,$query);
		//confirm success
		echo '<p> Your new account is successfully created. You\'re ready to log in and <a href="editprofile.php">Edit your profile</a>.</p>';
		 mysqli_close($dbc);
		exit();
	} else {
		echo '<p>An account already exists for this username. Please use a different address.</p>';
		$username = "";
	} } else {
		if($password1!=$password2) echo 'Password don\'t match';	else {
      echo '<p class="error">You must enter all of the sign-up data, including the desired password twice.</p>';
    }
  }
}
	mysqli_close($dbc);
	?>
    <p>Please enter your username and desired password to sign up to Tangle.</p>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <fieldset>
      <legend>Registration Info</legend>
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" value="<?php if (!empty($username)) echo $username; ?>" /><br />
      <label for="password1">Password:</label>
      <input type="password" id="password1" name="password1" /><br />
      <label for="password2">Password (retype):</label>
      <input type="password" id="password2" name="password2" /><br />
    </fieldset>
    <input type="submit" value="Sign Up" name="submit" />
  </form>
<?php require_once('footer.php'); ?>
	