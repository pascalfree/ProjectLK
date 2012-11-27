<?php
$toolbar=link_back();
load_head($toolbar);

//load all content from database
$helpcontent = plk_request('get_help',array('all' => 1, 'language' => $plk_you -> language));

//Content overview
echo '<div class="middlebox">';
  echo '<span class="title">',$plk_la['content'],'</span>';
  for($i=0; $i<$helpcontent['count']; $i++) {
    echo '<a href="#',$helpcontent['title'][$i],'" class="block">',$helpcontent['titletext'][$i],'</a>';
  }
echo '</div>';

//Content output
for($i=0; $i<$helpcontent['count']; $i++) {
  echo '<div class="middlebox">';
  echo '<a class="title" name="',$helpcontent['title'][$i],'">',$helpcontent['titletext'][$i],'</a>';
  echo '<p>',nl2br($helpcontent['valuetext'][$i]),'</p>';
  echo '</div>';
}

load_foot();
?>
