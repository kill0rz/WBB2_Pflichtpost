<?php

require './global.php';
require './acp/lib/class_parse.php';
require './acp/lib/class_parsecode.php';
require './acp/lib/options.inc.php';

if (!($wbbuserdata['userid'] == 1)) {
	header("Location: ./index.php");
	exit();
}

$optionstring = '';
$usercounter = 0;
?>

<form action="" method="post">
Post-ID: <input type="text" name="postidtoinsert" value="<?php echo $_POST['postidtoinsert']; ?>" /><br><br><br>
<?php

$result = $db->unbuffered_query("SELECT userid,username FROM bb1_users");
while ($row = $db->fetch_array($result)) {
	$optionstring .= "<option value='{$row['userid']}'>{$row['userid']} - " . htmlentities($row['username'], ENT_NOQUOTES | ENT_HTML401, 'ISO-8859-1') . "</option>";
	$usercounter++;
}

?>
User, die den Post sehen sollen (Mehrfachauswahl mit Strg-Taste):<br>
<select name="users[]" size="<?php echo $usercounter; ?>" multiple>
<?php echo $optionstring; ?>
</select>
<input type="submit" />
</form>


<?php

if (isset($_POST['postidtoinsert']) and trim($_POST['postidtoinsert']) != '') {
	$postidtoinsert = intval(trim($_POST['postidtoinsert']));
	$postidexists = false;

	$result = $db->unbuffered_query("SELECT * FROM bb" . $n . "_posts WHERE postid = '$postidtoinsert'");
	while ($row = $db->fetch_array($result)) {
		if (isset($row['postid'])) {
			$postidexists = true;
		}
	}

	$userids = array();
	$result = $db->unbuffered_query("SELECT userid FROM bb" . $n . "_users");
	while ($row = $db->fetch_array($result)) {
		$userids[] = $row['userid'];
	}

	$userstoinsert = $_POST['users'];

	if ($postidexists) {
		$zaehler_true = 0;
		$zaehler_false = 0;
		foreach ($userstoinsert as $userid) {
			$dorun = false;
			$result = $db->unbuffered_query("SELECT * FROM bb" . $n . "_users");
			while ($row = $db->fetch_array($result)) {
				if ($userid == $row['userid']) {
					$dorun = true;
				}

			}
			if ($dorun) {
				$db->unbuffered_query("INSERT INTO bb" . $n . "_pflichtpost (userid, postID) VALUES ('" . $userid . "','" . $postidtoinsert . "')");
				echo "<font color=green>UserID " . $userid . " erfolgreich eingetragen!</font><br>";
				$zaehler_true++;
			} else {
				echo "<font color=red>UserID " . $userid . " existiert nicht!</font><br>";
				$zaehler_false++;
			}
		}

		echo "<br><font color=green>" . $zaehler_true . "</font> User wurden erfolgreich eingetragen und bei <font color=red>" . $zaehler_false . "</font> Usern gab es einen Fehler.";
	} else {
		echo "Den Post mit der ID " . $postidtoinsert . " gibt es nicht!";
	}

}