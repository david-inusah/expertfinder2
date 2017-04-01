<?php
include('./classes/Login.php');
include('./classes/DB.php');
include('./classes/Post.php');
include('./classes/FollowUser.php');
// if (isset($_GET['username'])) {
	$username="";
	$skills="";
	$users="";
	if(Login::isLoggedIn()){
		$userid=Login::isLoggedIn();
		if (Login::firstLogin($userid)) {
			$username = DB::query('SELECT username FROM users WHERE id=:userid', array(':userid'=>$userid))[0]['username'];
		}
	}else{
		die('user not logged in');
	}

	if (isset($_POST['follow'])) {
		$reco_username=$_GET['reco_username'];
		$reco_userid = DB::query('SELECT id FROM users WHERE username=:reco_username', array(':reco_username'=>$reco_username))[0]['id'];
		if (FollowUser::follow($reco_userid, $userid)){
			echo "User followed";
		}else{
			echo "Unable to follow user";
		}

	}
	//$bio = DB::query('SELECT bio FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['bio'];
	$skills_array = DB::query('SELECT `skill` from skills,user_skills,users WHERE username=:username and users.id=user_skills.user_id and user_skills.skill_id=skills.id', array(':username'=>$_GET['username']));
        // echo "<pre>";
        // print_r($skills_array);
        // echo "<pre>";
	$skill1=$skills_array[0][0];
	for ($i=0; $i < count($skills_array); $i++) { 
		if ((count($skills_array)-$i)==2) {
			$skills.=$skills_array[$i][0].',';
		}else{
			$skills.=$skills_array[$i][0];
		}
	}
        // echo $skills.'</br>';
	$array = explode(",", $skills);
	// print_r($array);
	echo '</br>';
	$whereclause = "";
	$paramsarray = array(':username'=>$username, ':skill'=>$skill1);
	for ($i = 1; $i < count($array); $i++) {
		$whereclause .= " OR skill LIKE :u$i ";
		$paramsarray[":u$i"] = $array[$i];
	}
	// echo $whereclause.'</br>';
	// echo "<pre>";
	// print_r($paramsarray);
	// echo "<pre>";
	$users = DB::query('SELECT users.username FROM users, skills, user_skills WHERE users.id=user_skills.user_id AND user_skills.skill_id=skills.id AND users.username!=:username AND (skills.skill =:skill'.$whereclause.')', $paramsarray);
	// echo "SELECT users.username FROM users, skills, user_skills WHERE users.id=user_skills.user_id AND user_skills.skill_id=skills.id AND users.username!=:username AND (skill=:skill '.$whereclause.')";print_r($paramsarray);
	// print_r($users);
// echo "<pre>";
// print_r($users);
// echo "<pre>";
if (count($users)>0) {
	echo "Hey <b><?php echo $username;?></b>, here are some users with similar interests we recommend you follow! :)";
	foreach ($users as $key) {
		$reco_username=$key['username'];
		$userLink=Post::link_add('@'.$reco_username);
		echo "<div style='width: 90px; color: navy; background-color: pink; border: 2px solid blue; padding: 5px;'>
		<p>".$userLink."</p>
		<form action='recommended_users.php?username=".$username."&reco_username=".$reco_username."' method='post'>
			<input type='submit' name='follow' value='Follow'>
		</form>
	</div></br>";
}}else{
	echo "Sorry <b><?php echo $username;?></b>, there are no users with this interests right now :)";
}
?>

<form action="profile.php?username=<?php echo $username; ?>" method="post">
	<input type="submit" name="done" value="Done">
</form>