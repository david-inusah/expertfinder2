<?php
include('./classes/DB.php');
include('./classes/Login.php');
include('./classes/SearchRank.php');

$userid="";
$username="";
if (Login::isLoggedIn()) {
	$userid = Login::isLoggedIn();
	if (DB::query('SELECT username FROM users WHERE id=:userid', array(':userid'=>$userid))) {
        $username = DB::query('SELECT username FROM users WHERE id=:userid', array(':userid'=>$userid))[0]['username'];
    }
}
else{
	die('User not logged');
}

if (isset($_POST['collaboration_searchbox'])) {
	$keyword = explode(" ", $_POST['collaboration_searchbox']);
        // for ($i=0;$i<count($keyword);$i++) {
        // 	if ($i==0){
	$tosearch=str_split($keyword[0], 2);
        	// }else{
        	// 	$tosearch1=str_split($keyword[$i], 3);
        	// 	for ($p=0;$p<count($tosearch1);$p++) {
        	// 		array_push($tosearch, $tosearch1[$i]);		
        	// 	}
        	// }
        // }
	$whereclause = "";
	$experts="";
	$paramsarray = array(':userid'=>$userid,':keyword'=>'%'.$_POST['collaboration_searchbox'].'%');
	for ($i = 0; $i < count($tosearch); $i++) {
		if (strlen($tosearch[$i])==2){
			$whereclause .= " OR username LIKE :u$i ";
			$paramsarray[":u$i"] = $tosearch[$i];
		}
	}
        // echo "$whereclause";
        // echo "<pre>";
        // print_r($paramsarray);
        // echo "<pre>";
	$experts = DB::query('SELECT users.username, skills.skill, users.worklocation  FROM users, skills, user_skills WHERE user_skills.user_id=users.id AND user_skills.skill_id=skills.id AND users.id!=:userid AND (skills.skill LIKE :keyword'.$whereclause.')', $paramsarray);
	// echo "<pre>";
	// print_r($experts);
	// echo "<pre>";
// }	
	$searchername= DB::query('SELECT username FROM users WHERE id=:userid',array(':userid'=>$userid))[0]['username'];
	$searcherworklocation = DB::query('SELECT worklocation FROM users WHERE id=:userid',array(':userid'=>$userid))[0]['worklocation'];

	echo "Searching User:"."</br>";
	echo $searchername.'</br>';	
	echo $searcherworklocation.'</br>';
	echo "</br></br>";

	if (count($experts)>0) {
		foreach ($experts as $key) {
			$expert= $key['username'];
			$expertskill = $key['skill']."</br>";
			$expertworklocation = $key['worklocation'];
		// echo "</br></br>";
			$obj = new SearchRank($expert, $searcherworklocation,$expertworklocation);
			$obj->displayDetails();
		// echo "</br></br>";
			echo "<div style='width: 110px; color: navy; background-color: pink; border: 2px solid blue; padding: 5px;'>
			Found expert</br>
			$expert.</br>
			$expertskill
			$expertworklocation.</br>
		</div></br>";
	}
}else{
	echo "Sorry <b><?php echo $username;?></b>, there are no users with this interests right now :)";
}
		// $obj->getDistanceMatrix();
		// echo $response.'ssasas';

// $oJSON=json_decode($response);
// if ($oJSON->status=='OK')
//         $fDistanceInMiles=(float)preg_replace('/[^\d\.]/','',$oJSON->rows[0]->elements[0]->distance->text);
// else
//         $fDistanceInMiles=0;

// echo 'Distance in Miles: '.$fDistanceInMiles.PHP_EOL;

}
?>
<h2>Search by interest to find other experts to collaborate with here</h2>
<form action="collaboration_search.php" method="post">
	<input type="text" name="collaboration_searchbox" value="">
	<input type="submit" name="collab_search" value="Search">
</form>