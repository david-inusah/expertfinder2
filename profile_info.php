<?php
include('./classes/Login.php');
include('./classes/DB.php');

if (isset($_GET['username'])) {
	# code...
	$username=$_GET['username'];
// echo $username;
	if(Login::isLoggedIn()){
		// echo "string";
		$userid=Login::isLoggedIn();
		// echo $username;
		if (Login::firstLogin($userid)) {
			if (DB::query('SELECT username FROM users WHERE id=:userid', array(':userid'=>$userid)) {
			$username = DB::query('SELECT username FROM users WHERE id=:userid', array(':userid'=>$userid))[0]['username'];			
			// echo $username;
		}else{
			die('Unauthorized user');
		}
		}else{
			die('irrelevant page');
		}
	}else{
		die('user not logged in');
	}

	if (isset($_POST['bio'])) {
		$bio = $_POST['bio'];
		DB::query('UPDATE users SET bio=:bio WHERE id=:userid', array(':bio'=>$bio,':userid'=>$userid));
		// echo "bio added";
	}
	if (isset($_POST['skills'])) {
		$skills_array = explode(',', $_POST['skills']);
		for ($i=0; $i< count($skills_array); $i++) { 
			$skill = $skills_array[$i];
			if(!DB::query('SELECT skill FROM skills WHERE skill=:skill', array(':skill'=>$skill))) {
				DB::query('INSERT INTO skills VALUES (\'\', :skill)', array(':skill'=>$skill));
			}
			$skillid=DB::query('SELECT id FROM skills WHERE skill=:skill', array(':skill'=>$skill))[0]['id'];
			DB::query('INSERT INTO user_skills VALUES (\'\', :skillid, :userid)', array(':skillid'=>$skillid, ':userid'=>$userid));
// echo "skills added";
		}
	}
	if (isset($_POST['worklocation'])) {
		$worklocation=$_POST['worklocation'];
		DB::query('UPDATE users SET worklocation=:worklocation WHERE id=:userid', array(':worklocation'=>$worklocation,':userid'=>$userid));
	// echo "worklocation added";
		header('Location: recommended_users.php?username='.$username);
	}
}else{
	die('Unauthorized view');
}


?>
<h3>Welcome <?php echo $username;?></h3>
<form action="profile_info.php?username=<?php echo $username; ?>" method="post">
	<p>Bio: <input type="text" name="bio" value=""></p>
	<p>Skills: <input type="text" name="skills" value=""></p>
	<p>Workshed Location: <input type="text" name="worklocation" value=""></br>
		<span>Separate multiple skills with ','</span></p>

		<input type="submit" name="done" value="Done">
	</form>