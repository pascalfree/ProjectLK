<?php
load_head(link_back());

$options=plk_request('get_option');

//get available options
$languages = plk_util_getLanguages();
$subthemes = plk_util_getThemes();
$guis = plk_util_getGui();

?>
<form id="settings_form" action="" onsubmit="change_settings(); return false" method="POST">
<div class="middlebox">

<?php
echo '<b>',$plk_la['settings'],'</b><br>';

echo $plk_la['username'],': ',$options['name'],'<br>';
echo $plk_la['email'],': <input name="newemail" type="text" value="',$options['email'],'" size="',strlen($options['email']),'"></input><br>';
echo $plk_la['hints'],': <select size=1 name="newhints">';
echo '<option value="'.($options['hints']?'1':'0').'" selected>'.$plk_la['bool'][$options['hints']].'</option>';
echo '<option value="'.($options['hints']?'0':'1').'">'.$plk_la['bool'][!$options['hints']].'</option>';
echo '</select><br>';

echo $plk_la['gui'],': <select size=1 name="newgui">';
$len=count($guis);
for($i=0;$i<$len;$i++) {
  echo '<option ';
  if($guis[$i] == $options['gui']) { echo 'selected'; }
  echo '>'.$guis[$i].'</option>';
}
echo '</select><br>';

echo $plk_la['theme'],': <select size=1 name="newtheme">';
$len=count($subthemes);
for($i=0;$i<$len;$i++) {
  echo '<option ';
  if($subthemes[$i] == $options['theme']) { echo 'selected'; }
  echo '>'.$subthemes[$i].'</option>';
}
echo '</select><br>';

echo $plk_la['language'],': <select size=1 name="newlang">';
$len=count($languages);
for($i=0;$i<$len;$i++) {
  echo '<option ';
  if($languages[$i] == LANG) { echo 'selected'; } //20120409 fix : use LANG constant (visible language)
  echo '>'.$languages[$i].'</option>';
}
echo '</select><br>';
echo '<input type="submit" value="'.$plk_la['save'].'">';
echo '<input type="reset" value="'.$plk_la['reset'].'">';

?>
</div>
</form>

<!--Change Password-->
<form id="password_form" action="" onsubmit="change_password(); return false" method="POST">
<div class="middlebox">
<b><?=$plk_la['changepassword'] ?></b><br>
<?=$plk_la['oldpassword'] ?>: <input type="password" name="oldpassword"><br>
<?=$plk_la['newpassword'] ?>: <input type="password" name="newpassword"><br>
<?=$plk_la['repeat'] ?>: <input type="password" name="checkpassword"><br>
<input type="submit" name="changepass" value="<?=$plk_la['changepassword'] ?>"><br>
</div>
</form>

<?php
load_foot('settings');
?>
