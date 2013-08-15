<?php
  // Custom function to draw a bar graph given a data set, maximum value, and image filename
  function draw_bar_graph($width, $height, $data, $max_value, $filename) {
    // Create the empty graph image
    $img = imagecreatetruecolor($width, $height);

    // Set a white background with black text and gray graphics
    $bg_color = imagecolorallocate($img, 255, 255, 255);       // white
    $text_color = imagecolorallocate($img, 255, 255, 255);     // white
    $bar_color = imagecolorallocate($img, 0, 0, 0);            // black
    $border_color = imagecolorallocate($img, 192, 192, 192);   // light gray

    // Fill the background
    imagefilledrectangle($img, 0, 0, $width, $height, $bg_color);

    // Draw the bars
    $bar_width = $width / ((count($data) * 2) + 1);
    for ($i = 0; $i < count($data); $i++) {
      imagefilledrectangle($img, ($i * $bar_width * 2) + $bar_width, $height,
        ($i * $bar_width * 2) + ($bar_width * 2), $height - (($height / $max_value) * $data[$i][1]), $bar_color);
      imagestringup($img, 5, ($i * $bar_width * 2) + ($bar_width), $height - 5, $data[$i][0], $text_color);
    }

    // Draw a rectangle around the whole thing
    imagerectangle($img, 0, 0, $width - 1, $height - 1, $border_color);

    // Draw the range up the left side of the graph
    for ($i = 1; $i <= $max_value; $i++) {
      imagestring($img, 5, 0, $height - ($i * ($height / $max_value)), $i, $bar_color);
    }

    // Write the graph image to a file
    imagepng($img, $filename, 5);
    imagedestroy($img);
  } // End of draw_bar_graph() function

  // Start the session
  require_once('startsession.php');

  // Insert the page header
  $page_title = 'My tangle';
  require_once('header.php');

  require_once('appvars.php');
  require_once('connectvars.php');

  // Make sure the user is logged in before going any further.
  if (!isset($_SESSION['user_id'])) {
    echo '<p class="login">Please <a href="login.php">log in</a> to access this page.</p>';
    exit();
  }

  // Show the navigation menu
  require_once('navmenu.php');

  // Connect to the database
  $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $user_id=$_SESSION['user_id'];
  

  // Only look for a tangle if the user has questionnaire responses stored
  $query = "SELECT * FROM tangle_response WHERE user_id = '" . $_SESSION['user_id'] . "'";
  $data = mysqli_query($dbc, $query);
  if (mysqli_num_rows($data) != 0) {
    // First grab the user's responses from the response table (JOIN to get the topic and category names)
    $query = "SELECT mr.response_id, mr.topic_id, mr.response, mt.name AS topic_name, mc.name AS category_name " .
      "FROM tangle_response AS mr " .
      "INNER JOIN tangle_topic AS mt USING (topic_id) " .
      "INNER JOIN tangle_category AS mc USING (category_id) " .
      "WHERE mr.user_id = '" . $_SESSION['user_id'] . "'";
    $data = mysqli_query($dbc, $query);
    $user_responses = array();
    while ($row = mysqli_fetch_array($data)) {
      array_push($user_responses, $row);
    }

    // Initialize the tangle search results
    $tangle_score = 0;
    $tangle_user_id = -1;
    $tangle_topics = array();
    $tangle_categories = array();

    // Loop through the user table comparing other people's responses to the user's responses
    $query = "SELECT user_id FROM tangle_user WHERE user_id != '" . $_SESSION['user_id'] . "'";
    $data = mysqli_query($dbc, $query);
    while ($row = mysqli_fetch_array($data)) {
      // Grab the response data for the user (a potential tangle)
      $query2 = "SELECT response_id, topic_id, response FROM tangle_response WHERE user_id = '" . $row['user_id'] . "'";
      $data2 = mysqli_query($dbc, $query2);
      $tangle_responses = array();
      while ($row2 = mysqli_fetch_array($data2)) {
        array_push($tangle_responses, $row2);
      } // End of inner while loop

      // Compare each response and calculate a tangle total
      $score = 0;
      $topics = array();
      $categories = array();
      for ($i = 0; $i < count($user_responses); $i++) {
        if ($user_responses[$i]['response'] + $tangle_responses[$i]['response'] == 3) {
          $score += 1;
          array_push($topics, $user_responses[$i]['topic_name']);
          array_push($categories, $user_responses[$i]['category_name']);
        }
      }

      // Check to see if this person is better than the best tangle so far
      if ($score > $tangle_score) {
        // We found a better tangle, so update the tangle search results
        $tangle_score = $score;
        $tangle_user_id = $row['user_id'];
        $tangle_topics = array_slice($topics, 0);
        $tangle_categories = array_slice($categories, 0);
      }
    } // End of outer while loop

    // Make sure a tangle was found
    if ($tangle_user_id != -1) {
      $query = "SELECT username, first_name, last_name, city, state, picture FROM tangle_user WHERE user_id = '$tangle_user_id'";
      $data = mysqli_query($dbc, $query);
      if (mysqli_num_rows($data) == 1) {
        // The user row for the tangle was found, so display the user data
        $row = mysqli_fetch_array($data);
        echo '<table><tr><td class="label">';
        if (!empty($row['first_name']) && !empty($row['last_name'])) {
          echo $row['first_name'] . ' ' . $row['last_name'] . '<br />';
        }
        if (!empty($row['city']) && !empty($row['state'])) {
          echo $row['city'] . ', ' . $row['state'] . '<br />';
        }
        echo '</td><td>';
        if (!empty($row['picture'])) {
          echo '<img src="' . UPLOADPATH . $row['picture'] . '" alt="Profile Picture" /><br />';
        }
        echo '</td></tr></table>';

        // Display the tangled topics in a table with four columns
        echo '<h4>You are tangled on the following ' . count($tangle_topics) . ' topics:</h4>';
        echo '<table><tr>';
        $i = 0;
        foreach ($tangle_topics as $topic) {
          echo '<td>' . $topic . '</td>';
          if (++$i > 3) {
            echo '</tr><tr>';
            $i = 0;
          }
        }
        echo '</tr></table>';

        // Calculate the tangled category totals
        $category_totals = array(array($tangle_categories[0], 0));
        foreach ($tangle_categories as $category) {
          if ($category_totals[count($category_totals) - 1][0] != $category) {
            array_push($category_totals, array($category, 1));
          }
          else {
            $category_totals[count($category_totals) - 1][1]++;
          }
        }

        // Generate and display the tangled category bar graph image
        echo '<h4>Tangled category breakdown:</h4>';
        draw_bar_graph(480, 240, $category_totals, 5, UPLOADPATH .$user_id. 'mytanglegraph.png');
        echo '<img src="' . UPLOADPATH .$user_id. 'mytanglegraph.png" alt="tangle category graph" /><br />';

        // Display a link to the tangle user's profile
        echo '<h4>View <a href=viewprofile.php?user_id=' . $tangle_user_id . '>' . $row['first_name'] . '\'s profile</a>.</h4>';
      } // End of check for a single row of tangle user results
    } // End of check for a user tangle
  } // End of check for any questionnaire response results
  else {
    echo '<p>You must first <a href="questionnaire.php">answer the questionnaire</a> before you can be tangled.</p>';
  }

  mysqli_close($dbc);

  // Insert the page footer
  require_once('footer.php');
?>

<?php /* $page_title='My Tangle';
require_once('startsession.php');
require_once('header.php');
require_once('navmenu.php');
require_once('appvars.php');
require_once('connectvars.php');
$dbc = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
if(!isset($_SESSION['user_id'])){
	echo '<p>You must be logged in to see this result.<a href="login.php">Click here</a></p>';
	exit();}
$userid = $_SESSION['user_id'];
$query= "SELECT gender FROM tangle_user WHERE user_id='".$userid."'";
$data1=mysqli_query($dbc,$query);
$row = mysqli_fetch_array($data1);
$gender=$row[0]['gender'];
$query = "SELECT response_id,response FROM tangle_response  WHERE user_id = '".$userid."' ORDER BY response_id ASC";
$data1= mysqli_query($dbc,$query);
$userid2=1;
$query2= "SELECT user_id FROM tangle_user WHERE gender != '".$gender."'";$responseids= array();
while($responseid = mysqli_fetch_array($data1)){
	array_push($responseids,$responseid);}
$data2=mysqli_query($dbc,$query2);
$bestresponses=array();
foreach($bestresponses as $i){
	$i=0;}
$loopresponses=array();
foreach($loopresponses as $i){
	$i=0;}

while($row=mysqli_fetch_array($data2)){
	$query3= "SELECT response,response_id FROM tangle_response WHERE user_id='".$row."' ORDER BY response_id ASC";
	$data3= mysqli_query($dbc,$query3);
	$responseids2=array();
	while($responseid=mysqli_fetch_array($data3)){
		array_push($responseids2,$responseid);
	}
	$i=0;$score=0;$bestscore=0;$bestuser=0;
	while($i<count($responseids)){
		if(((int)$responseids[$i]['response']+(int)$responseids2[$i]['response'])==3){
			$loopresponses[$i]=1;
			$score++;
		}}
	if($score>$bestscore){$j=0;
		foreach($bestresponses as $k){
			$k=$loopresponses[$j];
			$j++;
			$bestscore=$score;
			$bestuser=$row;}
}$i++;
}
$query="SELECT first_name, last_name FROM tangle_user WHERE user_id='".$bestuser."'";
$data=mysqli_query($dbc,$query);
$bestmatch= mysqli_fetch_array($data);
echo '<p> Your best match is '.$bestmatch['first_name'].' and you tangle in '.$bestscore.' <a href="viewprofile.php?"'.$bestuser.'>Click Here</a> to view his profile</p>';
*/?>

		
	 	
 	