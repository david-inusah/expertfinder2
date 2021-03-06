
<?php
include('./classes/DB.php');
include('./classes/Login.php');
include('./classes/Post.php');
include('./classes/Notify.php');
include('./classes/Comment.php');
$showTimeline = False;
$username="";
if (Login::isLoggedIn()) {
    $userid = Login::isLoggedIn();
    if (DB::query('SELECT username FROM users WHERE id=:userid', array(':userid'=>$userid))) {
        $username = DB::query('SELECT username FROM users WHERE id=:userid', array(':userid'=>$userid))[0]['username']; 
        // echo $username;
        $showTimeline = True;
    }else {
        echo 'Not logged in';
    }
    if (isset($_GET['postid'])) {
        Post::likePost($_GET['postid'], $userid);
    }
    if (isset($_POST['comment'])) {
        Comment::createComment($_POST['commentbody'], $_GET['postid'], $userid);
    }

    if (isset($_POST['searchbox'])) {
        $tosearch = explode(" ", $_POST['searchbox']);
        if (count($tosearch) == 1) {
            $tosearch = str_split($tosearch[0], 2);
        }
        $whereclause = "";
        $paramsarray = array(':username'=>'%'.$_POST['searchbox'].'%');
        for ($i = 0; $i < count($tosearch); $i++) {
            $whereclause .= " OR username LIKE :u$i ";
            $paramsarray[":u$i"] = $tosearch[$i];
        }
        $users = DB::query('SELECT users.username FROM users WHERE users.username LIKE :username '.$whereclause.'', $paramsarray);
        // print_r($users);
        foreach ($users as $key) {
            $reco_username=$key['username'];
            $userLink=Post::link_add('@'.$reco_username);
            echo "<div style='width: 90px; color: navy; background-color: pink; border: 2px solid blue; padding: 5px;'>
            <p>".$userLink."</p>
        </div></br>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <!-- Import Google Icon Font -->
  <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- Import materialize.css -->
  <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>

  <!-- Let browser know website is optimized for mobile -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>

<body>

    <nav>
        <div class="nav-wrapper">
          <a href="#" class="brand-logo">Patatte</a>
          <ul id="nav-mobile" class="right hide-on-med-and-down">
          <li><a href="profile.php?username=<?php echo $username; ?>"><i class="material-icons">person</i></a><p><?php echo $username; ?></p></li>
            <li class="active"><a href="index.php">News Feed</a></li>
            <li><a href="collaboration_search.php">Collaborate</a></li>
            <li><a class="dropdown-button" href=""><i class="material-icons">notifications</i></a></li>
            <li><a class="dropdown-button" href="#!"> <i class="material-icons">more_vert</i></a></li>
            <li><?php echo Notify::notificationsCount($userid);?></li>

        </ul>
    </div>
    <div class="input-field">
      <input id="search" type="search" required>
      <label class="label-icon" for="search"><i class="material-icons">search</i></label>
      <i class="material-icons">close</i>
  </div>
</nav>
<h3>News Feed</h3>
<form action="index.php" method="post">
    <input type="text" name="searchbox" placeholder="Find other users" value="">
    <input type="submit" name="search" value="Search">
</form>

<!-- <span><a href="profile.php?username=<?php echo $username; ?>">View Profile</a></span></br></br> -->
<!-- <span><a href="collaboration_search.php">Search for Collaborators</a></span></br></br> -->

<?php
// $followingposts = DB::query('SELECT posts.id, posts.body, posts.likes, users.`username` FROM users, posts, followers
//     WHERE posts.user_id = followers.user_id
//     AND users.id = posts.user_id
//     AND follower_id = :userid
//     ORDER BY posts.likes DESC;', array(':userid'=>$userid));
// foreach($followingposts as $post) {
//     echo $post['body']." ~ ".$post['username'];
//     echo "<form action='index.php?postid=".$post['id']."' method='post'>";
//     if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$post['id'], ':userid'=>$userid))) {
//         echo "<input type='submit' name='like' value='Like'>";
//     } else {
//         echo "<input type='submit' name='unlike' value='Unlike'>";
//     }
//     echo "<span>".$post['likes']." likes</span>
// </form>
// <form action='index.php?postid=".$post['id']."' method='post'>
//     <textarea name='commentbody' rows='3' cols='50'></textarea>
//     <input type='submit' name='comment' value='Comment'>
// </form>
// ";
// Comment::displayComments($post['id']);
// echo "
// <hr /></br />";
// }
$posts = Post::displayNewsFeedPosts($username, $userid);
echo $posts;
}
?>

<!-- <!--Import jQuery before materialize.js -->
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/materialize.min.js"></script>
</body>
</html>