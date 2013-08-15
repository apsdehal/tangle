<?php
  require_once('startsession.php');

  $page_title = 'Questionnaire';
  require_once('header.php');

  require_once('appvars.php');
  require_once('connectvars.php');

  
  if (!isset($_SESSION['user_id'])) {
    echo '<p class="login">Please <a href="login.php">log in</a> to access this page.</p>';
    exit();
  }

  require_once('navmenu.php');

  $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $query = "SELECT * FROM tangle_response WHERE user_id = '" . $_SESSION['user_id'] . "'";
  $data = mysqli_query($dbc, $query);
  if (mysqli_num_rows($data) == 0) {
    $query = "SELECT topic_id FROM tangle_topic ORDER BY category_id, topic_id";
    $data = mysqli_query($dbc, $query);
    $topicIDs = array();
    while ($row = mysqli_fetch_array($data)) {
      array_push($topicIDs, $row['topic_id']);
    }

    foreach ($topicIDs as $topic_id) {
      $query = "INSERT INTO tangle_response (user_id, topic_id) VALUES ('" . $_SESSION['user_id']. "', '$topic_id')";
      mysqli_query($dbc, $query);
    }
  }

  if (isset($_POST['submit'])) {
    
    foreach ($_POST as $response_id => $response) {
      $query = "UPDATE tangle_response SET response = '$response' WHERE response_id = '$response_id'";
      mysqli_query($dbc, $query);
    }
    echo '<p>Your responses have been saved.</p>';
  }

 
  $query = "SELECT tr.response_id, tr.topic_id, tr.response, tt.name AS topic_name, tc.name AS category_name " .
    "FROM tangle_response AS tr " .
    "INNER JOIN tangle_topic AS tt USING (topic_id) " .
    "INNER JOIN tangle_category AS tc USING (category_id) " .
    "WHERE tr.user_id = '" . $_SESSION['user_id'] . "'";
  $data = mysqli_query($dbc, $query);
  $responses = array();
  while ($row = mysqli_fetch_array($data)) {
    array_push($responses, $row);
  }

  mysqli_close($dbc);
//Other method but complex
 /* $query = "SELECT response_id, topic_id, response FROM tangle_response WHERE user_id = '" . $_SESSION['user_id'] . "'";
  $data = mysqli_query($dbc, $query);
  $responses = array();
  while ($row = mysqli_fetch_array($data)) {
    // Look up the topic name for the response from the topic table
    $query2 = "SELECT name, category_id FROM tangle_topic WHERE topic_id = '" . $row['topic_id'] . "'";
    $data2 = mysqli_query($dbc, $query2);
    if (mysqli_num_rows($data2) == 1) {
      $row2 = mysqli_fetch_array($data2);
      $row['topic_name'] = $row2['name'];
	  $query3 = "SELECT name FROM tangle_category WHERE category_id='".$row2['category_id']."'";
	  $data3=mysqli_query($dbc,$query3);
	  if(mysqli_num_rows($data3)==1){
		  $row3=mysqli_fetch_array($data3);
      $row['category_name'] = $row3['name'];}
      array_push($responses, $row);
    }
  }*/

  echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
  echo '<p>How do you feel about each topic?</p>';
  $category = $responses[0]['category_name'];
  echo '<fieldset><legend>' . $responses[0]['category_name'] . '</legend>';
  foreach ($responses as $response) {
    if ($category != $response['category_name']) {
      $category = $response['category_name'];
      echo '</fieldset><fieldset><legend>' . $response['category_name'] . '</legend>';
    }

    echo '<label ' . ($response['response'] == NULL ? 'class="error"' : '') . ' for="' . $response['response_id'] . '">' . $response['topic_name'] . ':</label>';
    echo '<input type="radio" id="' . $response['response_id'] . '" name="' . $response['response_id'] . '" value="1" ' . ($response['response'] == 1 ? 'checked="checked"' : '') . ' />Love ';
    echo '<input type="radio" id="' . $response['response_id'] . '" name="' . $response['response_id'] . '" value="2" ' . ($response['response'] == 2 ? 'checked="checked"' : '') . ' />Hate<br />';
  }
  echo '</fieldset>';
  echo '<input type="submit" value="Save Questionnaire" name="submit" />';
  echo '</form>';

  require_once('footer.php');
?>
