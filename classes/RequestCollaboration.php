<?php
/**
* 
*/
class RequestCollaboration
{

    public static function sendRequest($userid, $followerid){
        $requestSent = False;
        if ($userid != $followerid) {
            if (!DB::query('SELECT id FROM collaboration_requests WHERE sender_id=:followerid  AND receiver_id=:userid', array(':userid'=>$userid,':followerid'=>$followerid))) {
                DB::query('INSERT INTO collaboration_requests VALUES (\'\', :userid, :followerid,0 ,0)', array(':userid'=>$userid, ':followerid'=>$followerid));
                $requestSent = True;
                Notify::createRequestSentNotify($followerid, $userid);
            }
        }
        //if request is pending (accepted=0 & rejected=0), user cannot send another request
        if (DB::query('SELECT id FROM collaboration_requests WHERE sender_id=:followerid AND receiver_id=:userid AND accepted =0 AND rejected=0', array(':userid'=>$userid, ':followerid'=>$followerid))) {
            $requestSent = True;
        }
        //if request is rejected, user can send another request
        if (DB::query('SELECT sender_id FROM collaboration_requests WHERE sender_id=:followerid AND receiver_id=:userid AND accepted =0 AND rejected=1', array(':userid'=>$userid, ':followerid'=>$followerid))) {
            $requestSent = False;
        }
        //if request is accepted user cannot send another request
        if (DB::query('SELECT sender_id FROM collaboration_requests WHERE sender_id=:followerid AND receiver_id=:userid  AND accepted =1 AND rejected=0', array(':userid'=>$userid, ':followerid'=>$followerid))) {
            $requestSent = True;
        }
        return $requestSent;
    }

    public static function acceptRequest($requestid){
            DB::query('UPDATE collaboration_requests SET accepted=0 WHERE id=:requestid', array(':requestid'=>$requestid));
        }

    public static function rejectRequest($requestid){
            // $requestid=DB::query('SELECT id FROM collaboration_requests WHERE sender_id=:followerid AND receiver_id=:userid sender_id=users.id AND receiver_id=users.id', array(':userid'=>$userid, ':followerid'=>$followerid))[0]['id'];
            DB::query('UPDATE collaboration_requests SET rejected=0 WHERE id=:requestid', array(':requestid'=>$requestid));
          }
}
?>  