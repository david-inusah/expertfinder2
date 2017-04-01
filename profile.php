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
    echo 'Bio: '.$bio.'</br>';
    echo 'Skills: '.$skills;
    echo '</br> followers: '.$followers;
    echo '</br> following: '.$following.'</br>';
    if (isset($_POST['follow'])) {
        if (FollowUser::follow($userid, $followerid)){
            echo "User followed";
            $isFollowing = True;
        }else{
            echo "Unable to follow user";
        }                
    }
    if (isset($_POST['unfollow'])) {
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
    if (isset($_POST['request_collaboration'])) {
        if (RequestCollaboration::sendRequest($userid, $followerid)){
            echo "Request sent";
            $requestedCollaboration = True;
        }else{
            echo "Unable to send request";
        }       
    }
    if (isset($_POST['deletepost'])) {
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
    if (isset($_GET['postid']) && !isset($_POST['deletepost'])) {
        Post::likePost($_GET['postid'], $followerid);
    }
    $posts = Post::displayPosts($userid, $username, $followerid);
} else {
    die('User not found!');
}
?>
<h1><?php echo $username; ?>'s Profile<?php if ($verified) { echo ' - Verified'; } ?></h1>
<span><a href="index.php">View Newsfeed</a></span></br></br>
<form action="profile.php?username=<?php echo $username; ?>" method="post">
    <?php
    if ($userid != $followerid) {

        if ($isFollowing) {
            echo '<input type="submit" name="unfollow" value="Unfollow">';
        } else {
            echo '<input type="submit" name="follow" value="Follow">';
        }
    }
    ?>
</form>
<form action="profile.php?username=<?php echo $username; ?>" method="post">
    <?php
    if ($userid != $followerid) {
        if ($requestedCollaboration) {
            echo "<div style='width: 150px; height: 60px; color: navy; background-color: pink; border: 2px solid blue;'><p>Pending Collaboration request</p></div></br>";
        }else{
          echo '<input type="submit" name="request_collaboration" value="Request Collaboration">';
      }
  }
  ?>
</form>

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


    <div class="posts">
        <?php 
        if (strlen($posts)>0){
            echo $posts; }else{
                echo "No posts yet";
            }?>
        </div>

        <!-- <!--Import jQuery before materialize.js -->
        <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
        <script type="text/javascript" src="js/materialize.min.js"></script>
    </body>
    </html>