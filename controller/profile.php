<?php
include('./classes/DB.php');
include('./classes/Login.php');
include('./classes/Post.php');
include('./classes/Image.php');
include('./classes/Notify.php');
include('./classes/RequestCollaboration.php');
include('./classes/FollowUser.php');

$username = "";
$verified = False;
$isFollowing = False;
$requestedCollaboration = False;
$posts="";
$userid="";
$followerid="";
$skills="";
if (isset($_GET['username'])) {
    $userid = Login::isLoggedIn();
    if (DB::query('SELECT username FROM users WHERE id=:userid', array(':userid'=>$userid))) {
        $loggedInUsername = DB::query('SELECT username FROM users WHERE id=:userid', array(':userid'=>$userid))[0]['username'];
    }
    if (DB::query('SELECT username FROM users WHERE username=:username', array(':username'=>$_GET['username']))) {
        $username = DB::query('SELECT username FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['username'];
        $userid = DB::query('SELECT id FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['id'];
        $verified = DB::query('SELECT verified FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['verified'];
        $bio = DB::query('SELECT bio FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['bio'];
        $skills_array = DB::query('SELECT `skill` from skills,user_skills,users WHERE username=:username and users.id=user_skills.user_id and user_skills.skill_id=skills.id', array(':username'=>$_GET['username']));
        $followers = DB::query('SELECT count(follower_id) from users, followers WHERE username=:username and users.id=followers.user_id
            ', array(':username'=>$_GET['username']))[0]['count(follower_id)'];
        $following = DB::query('SELECT count(follower_id) FROM followers, users WHERE username=:username and followers.follower_id=users.id
            ', array(':username'=>$_GET['username']))[0]['count(follower_id)'];
        $followerid = Login::isLoggedIn();
        foreach ($skills_array as $key) {
            $skills.=$key['skill'].',';
        }
    }else{
        echo "Unauthorized User";
    }
    $profileInfo = '<div class="col s10 offset-s1 center-align"><h3>Bio: '.$bio.'</h3><h4>Skills: '.$skills.'</h4><h6>followers: '.$followers.'<h6><h6>following: '.$following.'<h6></div>';
    if (isset($_GET['follow'])) {
        if (FollowUser::follow($userid, $followerid)){
            echo "User followed";
            $isFollowing = True;
        }else{
            echo "Unable to follow user";
        }                
    }
    if (isset($_GET['unfollow'])) {
     if (FollowUser::unfollow($userid, $followerid)){
            // Notify::createFollowNotify();
        echo "User unfollowed";
        $isFollowing = False;
    }else{
        echo "Unable to unfollow user";
    }       
}
if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid))) {
                        //echo 'Already following!';
    $isFollowing = True;
}
if (isset($_GET['request_collaboration'])) {
    if (RequestCollaboration::sendRequest($userid, $followerid)){
        echo "Request sent";
        $requestedCollaboration = True;
    }else{
        echo "Unable to send request";
    }       
}
if (isset($_GET['deletepost'])) {
    $postid=$_GET['postid'];
    if (Post::deletePost($postid, $followerid)){
        echo 'Post deleted!';
    }
}
if (isset($_POST['post'])) {
    if ($_FILES['postimg']['size'] == 0) {
        Post::createPost($_POST['postbody'], Login::isLoggedIn(), $userid);
    } else {
        $postid = Post::createImgPost($_POST['postbody'], Login::isLoggedIn(), $userid);
        Image::uploadImage('postimg', "UPDATE posts SET postimg=:postimg WHERE id=:postid", array(':postid'=>$postid));
    }
}
if (isset($_GET['postid']) && isset($_GET['like'])) {
    Post::likePost($_GET['postid'], $followerid);
}
$posts = Post::displayProfilePagePosts($userid, $username, $followerid);
} else {
    die('User not found!');
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
            <li class="active"><a href="profile.php?username=<?php echo $loggedInUsername; ?>"><i class="material-icons">person</i></a></li>
            <li><a href="index.php">News Feed</a></li>
            <li><a href="collaboration_search.php">Collaborate</a></li>
            <li><a href="#!" class="collection-item dropdown-button"><span class="new badge"><?php echo Notify::notificationsCount($userid);?></span></a></li>
            <!-- <li><a class="dropdown-button" href=""><i class="material-icons">notifications</i></a></li> -->
            <li><a class="dropdown-button" href="#!"> <i class="material-icons">more_vert</i></a></li>
            <!-- <li></li> -->
        </ul>
    </div>
</nav>
<?php echo $profileInfo;?>
<div style="float: left;">
<img src="images/profile.png" style="height:100px;" class="col s10 offset-s1 circle responsive-img">
<h5><?php echo $username; ?>'s Profile</h5>
</div>

<!-- <span><a href="index.php">View Newsfeed</a></span></br></br> -->
<!-- <form action="profile.php?username='<"?php echo $username; ?>" method="post"> -->
<div class="col s10 offset-s1 center-align">
    <?php
    if ($userid != $followerid) {

        if ($isFollowing) {
            echo '<a class="waves-effect waves-light btn" href="profile.php?unfollow&username='.$username.'">Follow</a></br></br>';
        } else {
            echo '<a class="waves-effect waves-light btn" href="profile.php?follow&username='.$username.'">Unfollow</a></br></br>';
        }
    }
    ?>
<!-- </form> -->
<!-- <form action="profile.php?username=<'?php echo $username; ?>" method="post">
 -->    
 <?php
    if ($userid != $followerid) {
        if ($requestedCollaboration) {
            // echo "<div style='width: 150px; height: 60px; color: navy; background-color: pink; border: 2px solid blue;'><p>Pending Collaboration request</p></div></br>";

            echo '<a class="waves-effect waves-light btn disabled" href="profile.php?request_collaboration&username='.$username.'">Pending Response</a></br></br>';
        }else{
          echo '<a class="waves-effect waves-light btn" href="profile.php?request_collaboration&username='.$username.'">Collaborate</a></br></br>';
      }
  }
  ?>
<!-- </form> -->
</div>

<?php
// echo 'Bio: '.$bio.'</br>';
// echo 'Skills: '.$skills;
// echo '</br> followers: '.$followers;
// echo '</br> following: '.$following.'</br>';

if ($userid == $followerid) {
    echo '<h5>Make a post</h5><form action="profile.php?username='.$username.'" method="post" enctype="multipart/form-data">
    <textarea name="postbody" rows="8" cols="80"></textarea>
    <br />Upload an image:
    <input type="file" name="postimg">
    <input type="submit" name="post" value="Post">
</form>';
}
?>

<div class="row">
 <!--  <div class="col s2">
      <div class="card">
        <div style="height:200px;" class="card-image">
          <img src="https://s-media-cache-ak0.pinimg.com/originals/aa/dd/c1/aaddc1ab529b6cb3b01301965a8958fb.jpg">
          <span class="card-title" style="width:100%; background: rgba(0, 0, 0, 0.5);">Sample1</span>
      </div>
      <div class="card-content">
          <p>Hello World!</p>
      </div>
      <div class="card-action">
          <a href="#">This is a link</a>
      </div>
  </div>
</div> -->

<div class="posts">
    <?php 
    if (strlen($posts)>0){
        echo $posts; }else{
            echo "No posts yet";
        }?>
    </div>

    </div>

    <!-- <!--Import jQuery before materialize.js -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
</body>
</html>