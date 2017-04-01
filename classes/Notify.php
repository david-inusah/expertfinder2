<?php

class Notify {
        public static function createMentionsNotify($text = "") {
                $text = explode(" ", $text);
                $notify = array();
                foreach ($text as $word) {
                        if (substr($word, 0, 1) == "@") {
                                $notify[substr($word, 1)] = array("type"=>1, "extra"=>' { "postbody": "'.htmlentities(implode($text, " ")).'" } ');
                        }
                }
                return $notify;
        }

        public static function createLikesNotify($postid = 0){
               if ($postid != 0) {
                $temp = DB::query('SELECT posts.user_id AS receiver, post_likes.user_id AS sender FROM posts, post_likes WHERE posts.id = post_likes.post_id AND posts.id=:postid', array(':postid'=>$postid));
                $r = $temp[0]["receiver"];
                $s = $temp[0]["sender"];
                if ($r!=$s) {
                        DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type'=>2, ':receiver'=>$r, ':sender'=>$s, ':extra'=>""));
                }
        }
}

public static function createCommentsNotify($comment=0){
       if (strlen($comment) != 0) {
        $temp = DB::query('SELECT posts.user_id AS receiver, comments.user_id AS sender FROM posts, comments WHERE posts.id = comments.post_id AND comments.comment=:comment', array(':comment'=>$comment));
        $r = $temp[0]["receiver"];
        $s = $temp[0]["sender"];
        if ($r!=$s) {
                DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type'=>3, ':receiver'=>$r, ':sender'=>$s, ':extra'=>""));
        }
}
}

public static function createFollowNotify($followerid, $userid){
        DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type'=>4, ':receiver'=>$userid, ':sender'=>$followerid, ':extra'=>""));
}

public static function createRequestSentNotify($sender, $receiver){
       DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type'=>5, ':receiver'=>$userid, ':sender'=>$followerid, ':extra'=>""));
}

public static function createRequestAcceptedNotify($requestid){
        if (DB::query('SELECT $sender, $receiver from notifications WHERE id=:requestid AND accepted=1', array(':requestid'=>$requestid))) {
                $receiver=DB::query('SELECT sender, receiver from notifications WHERE id=:requestid', array(':requestid'=>$requestid)) [0]['receiver'];
                $sender=DB::query('SELECT sender, receiver from notifications WHERE id=:requestid', array(':requestid'=>$requestid))[0]['sender'];        
                DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type'=>6, ':receiver'=>$receiver, ':sender'=>$sender, ':extra'=>""));
        }
}

public static function createRequestRejectedNotify($requestid){
     if (DB::query('SELECT $sender, $receiver from notifications WHERE id=:requestid', array(':requestid'=>$requestid))){
        $receiver=DB::query('SELECT sender, receiver from notifications WHERE id=:requestid AND rejected=1', array(':requestid'=>$requestid))[0]['receiver'];
        $sender=DB::query('SELECT sender, receiver from notifications WHERE id=:requestid', array(':requestid'=>$requestid))[0]['sender'];        
        DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type'=>7, ':receiver'=>$receiver, ':sender'=>$sender, ':extra'=>""));
}
}


}
?>