<?php
session_start();
?>
<?php 
require_once('connectvars.php');
$error_msg="";
if(!isset($_SESSION['user_id'])){
	if(isset($_POST['submit'])){
	
$dbc = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
$user_username = mysqli_real_escape_string($dbc,trim($_POST['username']));
$user_password=mysqli_real_escape_string($dbc,trim($_POST['password']));
if(!empty($user_username) && !empty($user_password)){
$query = "SELECT user_id, username FROM tangle_user WHERE username= '$user_username' AND password =SHA('$user_password')";
$data = mysqli_query($dbc,$query);
if(mysqli_num_rows($data)==1){
	$row = mysqli_fetch_array($data);
	$_SESSION['user_id']=$row['user_id'];
	$_SESSION['username']=$row['username'];
	setcookie('user_id',$row['user_id'],time()+(60*60*24*30));
	setcookie('username',$row['username'],time()+(60*60*24*30));
	$home_url = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/index.php';
	header('Location: '.$home_url);
} else {
	$error_msg='Sorry, you must enter a valid username and password to log in';
}} else{
	$error_msg ='Sorry, you must enter username and password';}
	}
}
?>
<?php 
if(empty($_SESSION['user_id'])){
	echo '<p>'.$error_msg.'</p>';
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Log-in</title>
</head>
<body>
<h3>Tangle-Log In</h3>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
<fieldset>
<legend>Log In</legend>
<label for="username">Username:</label>
<input type="text" id= "username" name="username" value="<?php if(!empty($user_username)) echo $user_username; ?>"/><br/>
<label for="password" >Password:</label>
<input type="password" id ="password" name="password"/>

</fieldset>
<input type="submit" name="submit" value="Log In"/>
</form>
<?php
}
if(isset($_SESSION['username']))
echo ('<p class="login">Your are logged in as '.$_SESSION['username'].'.</p>');
?>
</body>
</html>	