<!DOCTYPE HTML>
<html>
<!--
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: contentheader.php
//theme: default
//description: Header of the visible Page
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////
-->
<head>
  <title><?=u_title(); ?></title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <link rel="stylesheet" type="text/css" media="screen" href="<?=URL.GUI?>theme/<?=load_stylesheet('_basic') ?>.css">
  <link rel="stylesheet" type="text/css" media="screen" href="<?=URL.GUI?>theme/<?=load_stylesheet( $plk_you->style ) ?>.css">
  <link rel="shortcut icon" type="image/x-icon" href="<?=URL.GUI?>images/fave.ico">
</head>

<body>

<div style="display:none" id="dropdown" onmouseover="javascript: hold_dropdown()" onmouseout="javascript: hide_dropdown()"></div>
<div style="display:none" id="statusbar" onmouseover="javascript: holdstatus()" onmouseout="javascript: hidestatus()"><?=status_info() ?></div>
<div style="display:none" id="autosearcher"></div>
<div id="popup" class="hide"></div>
<!-- helper div: for giving hints live //-->
<?php if( $plk_you -> hints==1 ) { ?> 
<div id="helper">
  <div id="helper_head" onclick="help_toggle()">
    <span><?=$plk_la['help'] ?>: </span>
    <span id="helper_head_span"></span>
    <span class="iconx icon" onclick="help_close()"></span>
  </div>
  <div id="helper_body"></div>
</div>
<?php } ?>

<div id='header'>
  <div id='contentheader'>
	  <div id="headerpath">
       <?=menu_navigator((array) $plk_here) ?>
	  </div>
    <?php if( $plk_you -> id ) { ?>
    <span id="searchclick" class="link" onclick="show_search()"><?=$plk_la['search'] ?></span>
    <?php } ?>
    <?=form_search() ?>
    <span id="ajaxloader"></span>
  </div>  
  <div id='toolbar'>
      <?=tabs($tabs, $plk_here -> page); //load tabs with active tab of this page ?>     
      <?=status_button() ?>
      <?=$toolbar ?>
  </div>
</div>

<div id='shutter'><div id="loadbar"></div></div>
<div id='content' class='page_<?=$plk_here -> page ?>'>
  <div id='spcontent'>

  <div id="info" <?=$error==''?'style="display: none"':'' ?> >
    <span><?=$error ?></span>
    <span id="close_info" class="icon iconx float_right"></span>
  </div>
