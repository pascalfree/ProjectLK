<?php
$here->page="query";
load_head();
?>

<!--Wenn die Abfrage beendet ist-->

<div class="middlebox q_fin" id="resultbox">

  <?=$la['queryfin'] ?><br>
  <?=$la['corransws'] ?>: <?=q('correct'); ?>/<?=q('total'); ?></span><br>
  <?=$la['inpercent'] ?>: <?=q('percent'); ?><?php if($queryinfo['total']!=0) { echo round($queryinfo['correct']/$queryinfo['total']*100,4); } ?>%<br>
  <br>
  <?=q('wrong'); ?>
  <br><br>
  <?=$la['queryendopt'] ?><br>
  <span class="link block query_options" onclick="req('query_restart',{queryid: here.queryid}, function() { qe.start(1);})"><?=$la['queryrepeat'] ?></span>
  <?=link_querysave('querysave',0) ?>
  <span class="link -w block query_options" onclick="req('query_restart',{queryid: here.queryid, wrong: 1}, function() { qe.start(1);})"><?=$la['queryrepeatwrong'] ?></span>
  <?=link_querysave('querysavewrong',1) ?>
  <?php if($here->registerid != NULL) { //for some rare cases (query directly from dashboard) ?>
  <a class="block query_options" href="<?=$here->path(2) ?>"><?=$la['gotoregister'] ?></a>
  <?php } ?>
  <a class="block query_options" href="<?=$here->path(1) ?>"><?=$la['gohome'] ?></a>

</div>

<!--Wenn Abfrage noch läuft-->

<div class="middlebox q_run">
  <?=q('modeinfo'); ?><br>
  <?=q('modeinfodir'); ?><br>
</div>

<div class="middlebox q_run">
  <?=q('done'); ?>/<?=q('total'); ?><br>
  <?=$la['corrtnow'] ?>: <?=q('correct'); ?>/<?=q('done'); ?><br>
  <br>

  <!--<form id="wordform" name="wordform" action="#" onsubmit="javascript: return qeditswitch()">-->
    <div id="resultdiv" class="invisible">
      <?=q('result'); ?><br>
      <?=q('qquestion'); ?>: <?=q('lastword','ondblclick="queryedit(this,0)"'); ?><br>
      <?=q('qanswer'); ?>: <?=q('lastresult','ondblclick="queryedit(this,1)"'); ?><br>
      <?=$la['entry'] ?>: <?=q('lastanswer'); ?><br>
      <span class="-r link" onclick="qe.correction(); this.style.display='none'" id="correction"><?=$la['correction'] ?></span><br>
    </div>
  <!--</form>-->

  <br>
  <form id="queryform" class="-f" name="queryform" action="" onsubmit="return qe.send(this)">
    <?=q('qquestion'); ?>: <?=q('thisword'); ?><br>
    <?=q('qanswer'); ?>: <input id="q_answer" type="text" name="answer"></input><br>
    <input type="submit" value="<?=$la['ok'] ?>">
    <input name="skipbutton" type="button" onclick="qe.skip()" value="<?=$la['skip'] ?>">
  </form>

  <span class="f link" onclick="qe.showres()"><?=$la['showres'] ?></span>
  <span class="-f link" onclick="req('query_cancel',{queryid: here.queryid}, function() { qe.start(1) })"><?=$la['querycancel'] ?></span>

</div>
<?php
load_foot('queryl');
?>
