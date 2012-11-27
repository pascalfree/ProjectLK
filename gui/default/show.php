<?php 
////////////
//local functions

  //addword
  function ajax_addword($plk_here) {
    global $plk_la; 
  ?>
    <div class="middlebox">
    <span class="iconx link" onclick="tab_switch('show')"></span><span class="title"><?=$plk_la['addword'] ?></span>
    <form id="addword" method="POST" action="" onsubmit="do_action(0,'word','add',this); return false;">

      <label for="newregister"><?=$plk_la['register'] ?>:</label>
      <select name="registerid" size=1 id="newregister">
      <?php
        $registers = plk_request('get_register');
        for($i=0; $i<$registers['count']; $i++) {
          echo '<option value="', $registers['id'][$i] ,'"';
            if( $plk_here['registerid'] == $registers['id'][$i] ) { echo ' selected '; } //chosen one  
          echo '>', $registers['name'][$i] ,'</option>';
        }
      ?>    
      </select><br>

      <label for="newgroup"><?=$plk_la['group'] ?>:</label>
      <select name='newgroup' size=1 id="newgroup">
      <option value="1"><?=$plk_la['group'] ?> 1</option>
      <option value="af" <?php if($plk_here['groupid']=='af') { echo 'selected'; } ?>><?=$plk_la['af'] ?></option>
      </select><br>

      <label for="newwordfirst"><?=$plk_la['lang'] ?>:</label><?=link_help('syntax') ?><input type="text" name="newwordfirst" id="newwordfirst"></input><br>
      <label for="newwordfore"><?=$plk_la['fore'] ?>:</label><input type="text" name="newwordfore" id="newwordfore"></input><br>
      <label for="newtags"><?=$plk_la['tags'] ?>:</label><?=link_help('tag') ?><input type="text" name="newtags" id="newtags" value="<?=$tags ?>"></input><br>
      <label for="newsentence" class="satzlabel"><?=$plk_la['phrase'] ?>:</label><textarea name="newsentence" id="newsentence"></textarea><br>
      <label for="newwordclass"><?=$plk_la['wordclass'] ?>:</label>
      <select name='newwordclass' size=1 id="newwordclass">
      <?php
        for($i=0; $i<6; $i++) {
          echo '<option value=',$i;
            if( $plk_here['wordclassid']==$i ) { echo ' selected '; } //chosen one  
          echo '>',$plk_la["classname"][$i],'</option>';
        }
      ?>
      </select><br>
      <input type="hidden" name="force" value="0"></input>

      <input id="addword_submit" type="submit" value="<?=$plk_la['create'] ?>"></submit>

    </form>
    </div>
  <?php 
  }

////////////
//load page

//get the wordlist
//limited in descending order for addword
if( $plk_here->page == "add" ) {
  $now=time();
  $range=3*60*60; // 3 hours
  $plk_here->timerange = array($now-$range,$now,'DESC');
}
$wordlist=plk_request('get_word', (array) $plk_here);   //load

//create a toolbar
$toolbar=link_generic('action=addword&class=intab_show intab_overview&icon=plus&text='.urlencode($plk_la['addword'])); //add words
if($wordlist['count']>0) { //query
  $toolbar.=link_generic('action=query&type=0&id=0&icon=play&text='.urlencode($plk_la['query']));
}
//verbtable link
$toolbar.=link_generic('action=force_goto&type=keyoption&id=\'verb\'&class=intab_overview intab_show&text='.$plk_la['verbs']);
$toolbar.=link_more_options($plk_here->page=='show'); //show only if this is show
//register options
$toolbar.=link_generic('action=goto&type=keyoption&id=\'edit\'&class=intab_overview&text='.urlencode($plk_la['options']));

//create tabs
$tabs=array('show','overview');

// 20120407 fix : disable toolbar in global search
if( $plk_here->page == 'search' && $plk_here->registerid == NULL ) {
  $tabs = array();
  $toolbar = link_back();
} 

load_head($toolbar, $tabs);

//////////
//content

//----
//Tab addword
echo '<div id="content_tab_add" class="intab_add" style="display: ', $plk_here->page=="add" ? 'block' : 'none' ,';">';
  ajax_addword( (array) $plk_here );

  //show text that these are the last words added
  $invis=$wordlist['count']==0?' style="display:none"':'';
  echo '<p id="lastlabel" class="with_wordlist" '.$invis.'>'.$plk_la['lastadded'].'</p>';
echo '</div>';

//----
//Tab show
echo '<div id="content_tab_show" class="intab_show intab_add intab_search" style="display: ', ($plk_here->page=="show" ||  $plk_here->page=="add" || $plk_here->page=="search" )  ? 'block' : 'none' ,';">';

//write the wordtable
wt_form($wordlist);

//----
//Tab overview
echo '</div><div id="content_tab_overview" class="intab_overview" style="display: ', ($plk_here->page == "overview" || $plk_here->page=="search" ) ? 'block' : 'none' ,';">';

//do it later with ajax

echo '</div>';

load_foot('show');
?>
