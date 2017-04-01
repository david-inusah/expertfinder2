<?php
include('classes/DB.php');

if(isset($_POST['login'])){
	$username=$_POST['username'];
	$password=$_POST['password'];

	if (DB::query('SELECT username FROM users WHERE username=:username', array(':username'=>$username))){
		if (password_verify($password, DB::query('SELECT password FROM users WHERE username=:username', array(':username'=>$username))[0]['password'])){
			echo "Logged In!";
			$cstrong = True;
			$token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
			$user_id = DB::query('SELECT id from users WHERE username=:username', array(':username'=>$username))[0]['id'];
			DB::query('INSERT INTO login_tokens VALUES (\'\',:token,:user_id)', array(':token'=>sha1($token), 'user_id'=>$user_id)); 
			setcookie("SID", $token, time()+60*60*24*7,'/', NULL, NULL, TRUE);
			setcookie("SSID", 1, time()+60*60*24*2,'/', NULL, NULL, TRUE); 
			header('Location: index.php/');
		}else{
			echo "Incorrect Password";
			// header('Location: login.php/');
		}
	}else{
		die("User Not Registered");
	}
}	
?>
<h1>Login to your account</h1>
<form action="login.php" method="post">
	<p>	<input type="text" name="username" value="" placeholder="Username"></p>
	<p><input type="password" name="password" value="" placeholder="Password"></p>
	<p><input type="submit" name="login" value="Login"></p>
</form>