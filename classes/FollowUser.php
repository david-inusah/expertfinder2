<?php
/**
* 
*/
class FollowUser
{
	
	public static function follow($userid, $followerid){
		$followed = False;
		if ($userid != $followerid) {
			if (!DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid))) {
				DB::query('INSERT INTO followers VALUES (\'\', :userid, :followerid)', array(':userid'=>$userid, ':followerid'=>$followerid));
				$followed = True;
			}
		}
		return $followed;
	}

	public static function unfollow($userid, $followerid){
		$unfollowed = False;
		if ($userid != $followerid) {
			if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid))) {
				DB::query('DELETE FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid));
				$unfollowed = True;
			}
		}
		return $unfollowed;
	}
}
?>