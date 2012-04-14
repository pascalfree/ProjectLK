<?php
load_head(link_back());

$options=request('get_option');

//get available options
$languages=getlanguages();
$subthemes=getthemes();
$guis=getgui();

?>
<form id="settings_form" action="" onsubmit="change_settings(); return false" method="POST">
<div class="middlebox">

<?php
echo '<b>',$la['settings'],'</b><br>';

echo $la['username'],': ',$options['name'],'<br>';
echo $la['email'],': <input name="newemail" type="text" value="',$options['email'],'" size="',strlen($options['email']),'"></input><br>';
echo $la['hints'],': <select size=1 name="newhints">';
echo '<option value="'.($options['hints']?'1':'0').'" selected>'.$la['bool'][$options['hints']].'</option>';
echo '<option value="'.($options['hints']?'0':'1').'">'.$la['bool'][!$options['hints']].'</option>';
echo '</select><br>';

echo $la['gui'],': <select size=1 name="newgui">';
$len=count($guis);
for($i=0;$i<$len;$i++) {
  echo '<option ';
  if($guis[$i] == $options['gui']) { echo 'selected'; }
  echo '>'.$guis[$i].'</option>';
}
echo '</select><br>';

echo $la['theme'],': <select size=1 name="newtheme">';
$len=count($subthemes);
for($i=0;$i<$len;$i++) {
  echo '<option ';
  if($subthemes[$i] == $options['theme']) { echo 'selected'; }
  echo '>'.$subthemes[$i].'</option>';
}
echo '</select><br>';

echo $la['language'],': <select size=1 name="newlang">';
$len=count($languages);
for($i=0;$i<$len;$i++) {
  echo '<option ';
  if($languages[$i] == LANG) { echo 'selected'; } //20120409 fix : use LANG constant (visible language)
  echo '>'.$languages[$i].'</option>';
}
echo '</select><br>';
echo LANG;
echo '<input type="submit" value="'.$la['save'].'">';
echo '<input type="reset" value="'.$la['reset'].'">';

?>
</div>
</form>

<!--Change Password-->
<form id="password_form" action="" onsubmit="change_password(); return false" method="POST">
<div class="middlebox">
<b><?=$la['changepassword'] ?></b><br>
<?=$la['oldpassword'] ?>: <input type="password" name="oldpassword"><br>
<?=$la['newpassword'] ?>: <input type="password" name="newpassword"><br>
<?=$la['repeat'] ?>: <input type="password" name="checkpassword"><br>
<input type="submit" name="changepass" value="<?=$la['changepassword'] ?>"><br>
</div>
</form>

<?php
load_foot('settings');
?>
