<?php
$plk_here->page="query";
load_head();
?>

<!--Wenn die Abfrage beendet ist-->

<div class="middlebox q_fin" id="resultbox">

  <?=$plk_la['queryfin'] ?><br>
  <?=$plk_la['corransws'] ?>: <?=q('correct'); ?>/<?=q('total'); ?></span><br>
  <?=$plk_la['inpercent'] ?>: <?=q('percent'); ?><?php if($queryinfo['total']!=0) { echo round($queryinfo['correct']/$queryinfo['total']*100,4); } ?>%<br>
  <br>
  <?=q('wrong'); ?>
  <br><br>
  <?=$plk_la['queryendopt'] ?><br>
  <span class="link block query_options" onclick="plk.req('query_restart',{queryid: plk.here('queryid')}, function() { plk.qe.start(1);})"><?=$plk_la['queryrepeat'] ?></span>
  <?=link_querysave('querysave',0) ?>
  <span class="link -w block query_options" onclick="plk.req('query_restart',{queryid: plk.here('queryid'), wrong: 1}, function() { plk.qe.start(1);})"><?=$plk_la['queryrepeatwrong'] ?></span>
  <?=link_querysave('querysavewrong',1) ?>
  <?php if($plk_here->registerid != NULL) { //for some rare cases (query directly from dashboard) ?>
  <a class="block query_options" href="<?=$plk_here->path(2) ?>"><?=$plk_la['gotoregister'] ?></a>
  <?php } ?>
  <a class="block query_options" href="<?=$plk_here->path(1) ?>"><?=$plk_la['gohome'] ?></a>

</div>

<!--Wenn Abfrage noch läuft-->

<div class="middlebox q_run">
  <?=q('modeinfo'); ?><br>
  <?=q('modeinfodir'); ?><br>
</div>

<div class="middlebox q_run">
  <?=q('done'); ?>/<?=q('total'); ?><br>
  <?=$plk_la['corrtnow'] ?>: <?=q('correct'); ?>/<?=q('done'); ?><br>
  <br>

  <!--<form id="wordform" name="wordform" action="#" onsubmit="javascript: return qeditswitch()">-->
    <div id="resultdiv" class="invisible">
      <?=q('result'); ?><br>
      <?=q('qquestion'); ?>: <?=q('lastword','ondblclick="queryedit(this,0)"'); ?><br>
      <?=q('qanswer'); ?>: <?=q('lastresult','ondblclick="queryedit(this,1)"'); ?><br>
      <?=$plk_la['entry'] ?>: <?=q('lastanswer'); ?><br>
      <span class="-r link" onclick="plk.qe.correction(); this.style.display='none'" id="correction"><?=$plk_la['correction'] ?></span><br>
    </div>
  <!--</form>-->

  <br>
  <form id="queryform" class="-f" name="queryform" action="" onsubmit="return plk.qe.send(this)">
    <?=q('qquestion'); ?>: <?=q('thisword'); ?><br>
    <?=q('qanswer'); ?>: <input id="q_answer" type="text" name="answer"></input><br>
    <input type="submit" value="<?=$plk_la['ok'] ?>">
    <input name="skipbutton" type="button" onclick="plk.qe.skip()" value="<?=$plk_la['skip'] ?>">
  </form>

  <span class="f link" onclick="plk.qe.showResult()"><?=$plk_la['showres'] ?></span>
  <span class="-f link" onclick="plk.req('query_cancel',{queryid: plk.here('queryid')}, function() { plk.qe.start(1) })"><?=$plk_la['querycancel'] ?></span>

</div>
<?php
load_foot('queryl');
?>
