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
			header('Location: index.php');
		}else{
			echo "Incorrect Password";
			// header('Location: login.php/');
		}
	}else{
		die("User Not Registered");
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
<!-- 
<div class="widget-item z-depth-1">
<b>Sign In</b>

<div>
<div class="row">
<form class="col s12">
<div class="row">

<div class="input-field col s12">
<i class="mdi-action-account-circle prefix"></i>
<input id="username" type="text" class="validate">
<label for="username">Username</label>
</div>

<div class="input-field col s12">
<i class="mdi-action-https prefix"></i>
<input id="password" type="password" class="validate">
<label for="password">Password</label</div>

<button class="btn waves-effect waves-light" type="submit">
Login<i class="mdi-action-lock-open right"></i></button>

</div>
</form>

</div>
</div>
</div>
 -->

<h1>Login to your account</h1>
<form action="login.php" method="post">
	<p>	<input type="text" name="username" value="" placeholder="Username"></p>
	<p><input type="password" name="password" value="" placeholder="Password"></p>
	<p><input type="submit" name="login" value="Login"></p>
</form>

<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
        <script type="text/javascript" src="js/materialize.min.js"></script>
    </body>
    </html>