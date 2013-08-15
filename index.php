<?php require_once('startsession.php'); ?>
<?php $page_title='where friends meet'; 
require_once('header.php');?>  
<?php
  require_once('appvars.php');
  require_once('connectvars.php');
  echo '<div id="wrap">';
  echo '<div id="header">';
  require_once('navmenu.php');
  echo '</div>';
  echo '</div>';
  // Connect to the database 
  $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

  // Retrieve the user data from MySQL
  $query = "SELECT user_id, first_name, picture FROM tangle_user WHERE first_name IS NOT NULL ORDER BY join_date DESC LIMIT 5";
  $data = mysqli_query($dbc, $query);


  echo '<div class="body">';
  echo '<br/>';
  echo '<h4> Latest Members </h4>';
  echo '<table>';
  while($row = mysqli_fetch_array($data)){
	  if(is_file(UPLOADPATH.$row['picture']) && filesize(UPLOADPATH.$row['picture'])>0){
		  echo '<tr><td><img src="'.UPLOADPATH.$row['picture'].'" alt= "'.$row['first_name'].'"/></td>';
	  } else {
		  echo '<tr><td><img src="'.UPLOADPATH.'nopic.jpg'.'" alt= "'.$row['first_name'].'"/></td>';
		  }
	if(isset($_SESSION['user_id'])){
		echo '<td><a href="viewprofile.php?user_id='.$row['user_id'].'">'.$row['first_name'].'</a></td></tr>';
	} else {
	  echo '<td>' .$row['first_name'].'</td></tr>';
  }
  }
  echo '</table>';
  echo '</div>';
  require_once('footer.php');
  mysqli_close($dbc);
  ?>