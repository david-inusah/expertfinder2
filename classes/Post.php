<?php

class Post {
    public static function createPost($postbody, $loggedInUserId, $profileUserId) {
        if (strlen($postbody) > 160 || strlen($postbody) < 1) {
            die('Incorrect length!');
        }
        if ($loggedInUserId == $profileUserId) {
          if (count(Notify::createMentionsNotify($postbody)) != 0) {
            foreach (Notify::createMentionsNotify($postbody) as $key => $n) {
                $s = $loggedInUserId;
                $r = DB::query('SELECT id FROM users WHERE username=:username', array(':username'=>$key))[0]['id'];
                if ($r != 0) {
                    DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type'=>$n["type"], ':receiver'=>$r, ':sender'=>$s, ':extra'=>$n["extra"]));
                }
            }
        }
        DB::query('INSERT INTO posts VALUES (\'\', :postbody, NOW(), :userid, 0, \'\', \'\')', array(':postbody'=>$postbody, ':userid'=>$profileUserId));
    } else {
        die('Incorrect user!');
    }
}
public static function createImgPost($postbody, $loggedInUserId, $profileUserId) {
    if (strlen($postbody) > 160) {
        die('Incorrect length!');
    }
    if ($loggedInUserId == $profileUserId) {
      if (count(Notify::createMentionsNotify($postbody)) != 0) {
        foreach (Notify::createMentionsNotify($postbody) as $key => $n) {
            $s = $loggedInUserId;
            $r = DB::query('SELECT id FROM users WHERE username=:username', array(':username'=>$key))[0]['id'];
            if ($r != 0) {
                DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type'=>$n["type"], ':receiver'=>$r, ':sender'=>$s, ':extra'=>$n["extra"]));
            }
        }
    }
    DB::query('INSERT INTO posts VALUES (\'\', :postbody, NOW(), :userid, 0, \'\', \'\')', array(':postbody'=>$postbody, ':userid'=>$profileUserId));
    $postid = DB::query('SELECT id FROM posts WHERE user_id=:userid ORDER BY ID DESC LIMIT 1;', array(':userid'=>$loggedInUserId))[0]['id'];
    return $postid;
} else {
    die('Incorrect user!');
}
}

public static function deletePost($postid, $followerid){
    $postDeleted=False;
    if (DB::query('SELECT id FROM posts WHERE id=:postid AND user_id=:userid', array(':postid'=>$_GET['postid'], ':userid'=>$followerid))) {
        DB::query('DELETE FROM posts WHERE id=:postid and user_id=:userid', array(':postid'=>$_GET['postid'], ':userid'=>$followerid));
        DB::query('DELETE FROM post_likes WHERE post_id=:postid', array(':postid'=>$_GET['postid']));
        $postDeleted=True;
        Notify::deleteMentionsNotify($postid);
    }
    return $postDeleted;
}

public static function likePost($postId, $likerId) {
    if (!DB::query('SELECT user_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postId, ':userid'=>$likerId))) {
        DB::query('UPDATE posts SET likes=likes+1 WHERE id=:postid', array(':postid'=>$postId));
        DB::query('INSERT INTO post_likes VALUES (\'\', :postid, :userid)', array(':postid'=>$postId, ':userid'=>$likerId));
        Notify::createLikesNotify($postId);
    } else {
        DB::query('UPDATE posts SET likes=likes-1 WHERE id=:postid', array(':postid'=>$postId));
        DB::query('DELETE FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postId, ':userid'=>$likerId));
    }
}
public static function link_add($text) {
    $text = explode(" ", $text);
    $newstring = "";
    foreach ($text as $word) {
        if (substr($word, 0, 1) == "@") {
            $newstring .= "<a href='profile.php?username=".substr($word, 1)."'>".htmlspecialchars($word)."</a> ";
        } else {
            $newstring .= htmlspecialchars($word)." ";
        }
    }
    return $newstring;
}
private static function getProfilePagePosts($userid){
   $userposts = DB::query('SELECT * FROM posts WHERE user_id=:userid ORDER BY id DESC', array(':userid'=>$userid));
   return $userposts;    
}

public static function displayProfilePagePosts($userid, $username, $loggedInUserId) {
    $dbposts = self::getProfilePagePosts($userid);
    $posts = "";
    foreach($dbposts as $p) {
        if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$p['id'], ':userid'=>$loggedInUserId))) {

           $posts .= ' <div class="row">
           <div class="col s2">
              <div class="card">
                  <div style="height:200px;" class="card-image responsive-img">';
              // echo $p['postimg'];
                    if ($p['postimg']!="") {
                        echo $p['postimg'];
                        $posts.='<img src="'.$p['postimg'].'">';
                    }else{
                        $posts.='<img src="images/nopreview.png">';
                    }

                    $posts.='</div>
                    <div class="card-content">
                      <p>'.self::link_add($p['body']).'</p>
                  </div>
                  <div class="card-action">
                      <a href="profile.php?like&username='.$username.'&postid='.$p['id'].'"><i class="tiny material-icons">thumb_up</i> '.$p['likes'].'</a>';
                      if ($userid == $loggedInUserId) {
                        $posts .='<a href="profile.php?deletepost&username='.$username.'&postid='.$p['id'].'"><i class="tiny material-icons">delete</i></a>';
                    }
                    $posts .='</div>
                </div>
            </div>
            ';
        } else {
          $posts .= ' <div class="row">
          <div class="col s2">
              <div class="card">
                  <div style="height:200px;" class="card-image responsive-img">';
              // echo $p['postimg'];
                    if ($p['postimg']!="") {
                        echo $p['postimg'];
                        $posts.='<img src="'.$p['postimg'].'">';
                    }else{
                        $posts.='<img src="images/nopreview.png">';
                    }

                    $posts.='</div>
                    <div class="card-content">
                      <p>'.self::link_add($p['body']).'</p>
                  </div>
                  <div class="card-action">
                      <a href="profile.php?like&username='.$username.'&postid='.$p['id'].'"><i class="tiny material-icons">thumb_down</i> '.$p['likes'].'</a>';
                      if ($userid == $loggedInUserId) {
                        $posts .='<a href="profile.php?deletepost&username='.$username.'&postid='.$p['id'].'"><i class="tiny material-icons">delete</i></a>';
                    }
                    $posts .='</div>
                </div>
            </div>
            ';
        }
    }
    return $posts;
}

private static function getNewsFeedPosts($userid){
    $followingposts = DB::query('SELECT posts.id, posts.body, posts.postimg, posts.likes, users.`username`, posts.user_id FROM users, posts, followers
        WHERE posts.user_id = followers.user_id
        AND users.id = posts.user_id
        AND follower_id = :userid
        ORDER BY posts.likes DESC;', array(':userid'=>$userid));
    return $followingposts;    
}

public static function displayNewsFeedPosts($username, $loggedInUserId) {
    $dbposts = self::getNewsFeedPosts($loggedInUserId);
    $posts = "";
    foreach($dbposts as $p) {
        $userid=$p['user_id'];
        $postowner=$p['username'];
        if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$p['id'], ':userid'=>$userid))) {
           $posts .= ' <div class="row">
           <div class="col s2">
              <div class="card">
                  <div style="height:200px;" class="card-image responsive-img">';
              // echo $p['postimg'];
                    if ($p['postimg']!="") {
                        echo $p['postimg'];
                        $posts.='<img src="'.$p['postimg'].'">';
                    }else{
                        $posts.='<img src="images/nopreview.png">';
                    }
                    $posts.='<span class="card-title" style="width:100%; background: rgba(0, 0, 0, 0.5);"><img style="height:20px; width:20px; float: left;" src="images/Haq.jpg" class="circle"><font style="font-size: 12px;">'.self::link_add("@".$postowner).'</font></span></div>
                    <div class="card-content">
                      <p>'.self::link_add($p['body']).'</p>
                  </div>
                  <div class="card-action">
                      <a href="profile.php?like&username='.$username.'&postid='.$p['id'].'"><i class="tiny material-icons">thumb_up</i> '.$p['likes'].'</a>';
                      if ($userid == $loggedInUserId) {
                        $posts .='<a href="profile.php?deletepost&username='.$username.'&postid='.$p['id'].'"><i class="tiny material-icons">delete</i></a>';
                    }
                    $posts .='</div>
                </div>
            </div>';
        } else {
          $posts .= ' <div class="row">
          <div class="col s2">
              <div class="card">
                  <div style="height:200px;" class="card-image responsive-img">';
              // echo $p['postimg'];
                    if ($p['postimg']!="") {
                        echo $p['postimg'];
                        $posts.='<img src="'.$p['postimg'].'">';
                    }else{
                        $posts.='<img src="images/nopreview.png">';
                    }

                    $posts.='<span class="card-title" style="width:100%; background: rgba(0, 0, 0, 0.5);"><img style="height:20px; width:20px; float: left;" src="images/Haq.jpg" class="circle"><font style="font-size: 12px;">'.self::link_add("@".$postowner).'</font></span></div>
                    <div class="card-content">
                      <p>'.self::link_add($p['body']).'</p>
                  </div>
                  <div class="card-action">
                      <a href="profile.php?like&username='.$username.'&postid='.$p['id'].'"><i class="tiny material-icons">thumb_down</i> '.$p['likes'].'</a>';
                      if ($userid == $loggedInUserId) {
                        $posts .='<a href="profile.php?deletepost&username='.$username.'&postid='.$p['id'].'"><i class="tiny material-icons">delete</i></a>';
                    }
                    $posts .='</div>
                </div>
            </div>';
        }
    }
    return $posts;
}
}
?>