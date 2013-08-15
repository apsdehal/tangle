<?php 
require_once('startsession.php');
require_once('appvars.php');
require_once('connectvars.php');$page_title= 'Change Password';
require_once('header.php');
if(isset($_POST['submit'])){
$user_id=$_SESSION['user_id'];
$dbc= mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
$query="SELECT password FROM tangle_user WHERE user_id='".$user_id."'";
$data=mysqli_query($dbc,$query);
if(mysqli_num_rows($data)==1){
	$row=mysqli_fetch_array($data);
	$password=$row['password'];
	} else '<p>There was an error</p>';
		$old_password=$_POST['oldpassword'];
		$password1=mysqli_real_escape_string($dbc,trim($_POST['password1']));
		$password2=mysqli_real_escape_string($dbc,trim($_POST['password2']));
		if(sha1($old_password)==$password){
			if($password1==$password2){
				$query = "UPDATE tangle_user SET password=SHA('$password1') WHERE user_id='$user_id'";
				mysqli_query($dbc,$query)
				or die('Password change was unsuccessful');
				echo 'Password was successfully changed';
				echo '<p><a href="index.php">Click Here</a> to go to the main page</p>';exit();} else {
					echo '<p>Password don\'t match';}}
		else{ echo '<p>Old password is not correct</p>';}}
		
echo '<p>Please enter following to change password:</p>';
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method ="post">
    <fieldset>
    <legend>Change Password</legend>
    <label for="oldpassword">Old Password:</label>
    <input type="password" name="oldpassword" id="oldpassword" /><br/>
    
    <label for="password1">New Password:</label>
    <input type="password" name="password1" id="password1" /><br/>
    
    <label for="password2">New Password(Retype):</label>
    <input type="password" name="password2" id="password2" /><br/>
    <input type="submit" name="submit" value="Change Password"/>
    </fieldset>
    </form>
    <?php require_once('footer.php'); ?>