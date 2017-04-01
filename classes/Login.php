<?php
/**
* 
*/
class Login
{	
	public static function isLoggedIn()
	{
		if(isset($_COOKIE['SID'])){
			if (DB::query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($_COOKIE['SID'])))){
				$user_id=DB::query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($_COOKIE['SID'])))[0]['user_id'];
				if(isset($_COOKIE['SSID'])){
					return $user_id;
				}else{
					$cstrong = True;
					$token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
					DB::query('INSERT INTO login_tokens VALUES (\'\',:token,:user_id)', array(':token'=>sha1($token), 'user_id'=>$user_id));	
					DB::query('DELETE FROM login_tokens WHERE token=:token', array(':token'=>sha1($_COOKIE['SID'])));

					setcookie("SID", $token, time()+60*60*24*7,'/', NULL, NULL, TRUE);
					setcookie("SSID", 1, time()+60*60*24*2,'/', NULL, NULL, TRUE);	

					return $user_id;
				}
			}
		}
		return false;
	}

	public static function firstLogin($userid){
		$firstLogin = False;
		$loginCount = DB::query('SELECT count(*) FROM login_tokens WHERE user_id=:userid', array(':userid'=>$userid))[0]['count(*)'];
		if ($loginCount==1){
			// echo $loginCount;
			$firstLogin=True;
			return $firstLogin;
		} 
	}

	public static function signupLogin($username, $password){
		$cstrong = True;
			$token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
			$user_id = DB::query('SELECT id from users WHERE username=:username', array(':username'=>$username))[0]['id'];
			DB::query('INSERT INTO login_tokens VALUES (\'\',:token,:user_id)', array(':token'=>sha1($token), 'user_id'=>$user_id)); 
			setcookie("SID", $token, time()+60*60*24*7,'/', NULL, NULL, TRUE);
			setcookie("SSID", 1, time()+60*60*24*2,'/', NULL, NULL, TRUE); 
			header('Location: profile_info.php?username='.$username);
	}
}
	?>