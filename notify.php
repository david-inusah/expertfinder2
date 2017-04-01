<?php
include('./classes/DB.php');
include('./classes/Login.php');
if (Login::isLoggedIn()) {
        $userid = Login::isLoggedIn();
} else {
        echo 'Not logged in';
}
echo "<h1>Notifications</h1>";
if (DB::query('SELECT * FROM notifications WHERE receiver=:userid', array(':userid'=>$userid))) {
        $notifications = DB::query('SELECT * FROM notifications WHERE receiver=:userid ORDER BY id DESC', array(':userid'=>$userid));
        foreach($notifications as $n) {
                if ($n['type'] == 1) {
                        $senderid = DB::query('SELECT id FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['id'];
                        if ($senderid!=$userid) {
                                $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['username'];
                                if ($n['extra'] == "") {
                                        echo "You got a notification!<hr />";
                                } else {
                                        $extra = json_decode($n['extra']);
                                        echo $senderName." mentioned you in a post! - ".$extra->postbody."<hr />";
                                }
                        }
                } else if ($n['type'] == 2) {
                        $senderid = DB::query('SELECT id FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['id'];
                        if ($senderid!=$userid) {
                                $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['username'];
                                echo $senderName." liked your post!<hr />";
                        }
                }else if ($n['type'] == 3) {
                        $senderid = DB::query('SELECT id FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['id'];
                        if ($senderid!=$userid) {
                                $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['username'];
                                echo $senderName." commented your post!<hr />";
                        }
                }else if ($n['type'] == 4) {
                        $senderid = DB::query('SELECT id FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['id'];
                        if ($senderid!=$userid) {
                                $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['username'];
                                echo $senderName." followed you<hr />";
                        }
                }else if ($n['type'] == 5) {
                        $senderid = DB::query('SELECT id FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['id'];
                        if ($senderid!=$userid) {
                                $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['username'];
                                echo $senderName." sent you a collaboration request<hr />";
                        }
                }else if ($n['type'] == 6) {
                        $senderid = DB::query('SELECT id FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['id'];
                        if ($senderid!=$userid) {
                                $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['username'];
                                echo $senderName." accepted your collaboration request<hr />";
                        }
                }else if ($n['type'] == 7) {
                        $senderid = DB::query('SELECT id FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['id'];
                        if ($senderid!=$userid) {
                                $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array(':senderid'=>$n['sender']))[0]['username'];
                                echo $senderName." rejected your collaboration request<hr />";
                        }
                }
        }
}
?>