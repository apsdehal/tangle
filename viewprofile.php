<?php $page_title='View Profile';
require_once('startsession.php');
require_once('appvars.php');
require_once('connectvars.php');
require_once('header.php');
require_once('navmenu.php');
?><?php
if(!isset($_SESSION['user_id'])){
	echo '<p>You need to <a href="login.php">Login</a> in order to access this page.</p>';
	exit();
} else {
	echo('<p>You\'re logged in as '.$_SESSION['username'].'. <a href="logout.php">Log Out</a>');} 
?>
<?php
if(isset($_SESSION['user_id'])){
	  $user_id= $_SESSION['user_id'];}
$dbc =mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
  if(isset($_GET['user_id'])){
	  $query = "SELECT username, first_name, last_name, gender, birthdate, city, state, picture FROM tangle_user WHERE user_id='".$_GET['user_id']."'";
  } else {
	  $query = "SELECT username, first_name, last_name, gender, birthdate, city, state, picture FROM tangle_user WHERE user_id='$user_id'";
  }
  $data = mysqli_query($dbc,$query);
  if(mysqli_num_rows($data)==1){
	  //Displaying profile if single result is found
	  $row = mysqli_fetch_array($data);
	  echo '<table>';
	  if(!empty($row['username'])){
		  echo '<tr><td> Username: </td><td>'.$row['username'].'</td></tr>';
		  }
		if(!empty($row['first_name'])){
			echo '<tr><td> First Name: </td><td>'.$row['first_name'].'</td></tr>';  
  }
       if(!empty($row['last_name'])){
		   echo '<tr><td> Last Name: </td><td>'.$row['last_name'].'</td></tr>';
	   }
	   if(!empty($row['gender'])){
		   echo '<tr><td> Gender </td><td>';
		   if($row['gender']='M'){
			   echo 'Male';}
		   else if($row['gender']=='F'){
			   echo 'Female';}
		   else {
			   echo '?';
		   }echo '</td></tr>';
		   }
		   if (!empty($row['birthdate'])) {
      if (!isset($_GET['user_id']) || ($user_id == $_GET['user_id'])) {
        // Show the user their own birthdate
        echo '<tr><td> Birthdate:</td><td>' . $row['birthdate'] . '</td></tr>';
      }
      else {
        // Show only the birth year for everyone else
        list($year, $month, $day) = explode('-', $row['birthdate']);
        echo '<tr><td> Year born:</td><td>' . $year . '</td></tr>';
      }
    }
	if(!empty($row['city']) || !empty($row['state'])){
		echo '<tr><td> Location: </td><td>'.$row['city'].', '.$row['state'].'</td></tr>';
	}
	if (!empty($row['picture'])) {
      echo '<tr><td class="label">Picture:</td><td><img src="' . UPLOADPATH . $row['picture'] .
        '" alt="Profile Picture" /></td></tr>';
    }
	echo '</table>';
	if (!isset($_GET['user_id']) || ($user_id == $_GET['user_id'])) {
      echo '<p>Would you like to <a href="editprofile.php">edit your profile</a>?</p>';
    }
} else {
    echo '<p style= "font-weight: bold">There was a problem accessing your profile.</p>';
  } require_once('footer.php');
  mysqli_close($dbc);
  ?>
 </body>
 </html>	  