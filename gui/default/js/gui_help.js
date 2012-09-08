//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: help.js
//GUI: default
//description: giving hints on some actions.
//author: David Glenck
//licence: GNU General Public Licence (see license.txt in Mainfolder)
//////////////////////////////

//////////
//GLOBAL VARIABLES
//////////

local.help = {};
local.help.loaded = {};
local.help.title = {};
local.help.value = {};

//////////
//INITIALIZE
//////////

//Wait till dom is ready
document.observe("dom:loaded", function() {
  //minimize in the beginnin:
  help_toggle();
  //load content
  req('get_help',{ language : you.language, gettitle : 1}, help_content);
  //edit word
  $$('a.wordedit').each(function(item) { item.onmouseover=function() {help_load('wordedit');} });
});

//////////
//LOAD
//////////
//Load data
function help_load(ttitle) {
  //only load if not loaded
  if(local.help.opened!=ttitle) {
    //set this one as loaded
    local.help.opened=ttitle;
    //if already loaded content
    if(local.help.title[ttitle]) {
      help_show({titletext:local.help.title[ttitle], valuetext:local.help.value[ttitle]},ttitle);
    //else load content
    } else {
      req('get_help',{title : ttitle, language : you.language}, function(i,p,s) { help_show(i,s) }, ttitle);
    }
  }
}

//Make a content overview
function help_content(info) {
  var content='';
  for(var i=0; i<info.count; i++) {
    content+='<span class="link" onclick="help_load(\''+info.title[i]+'\')">'+info.titletext[i]+'</span><br>';
  }
  help_show({titletext:la.content, valuetext:[content]},'content');
}

//////////
//VISUALIZE
//////////

//show a helper window
function help_show(info,ttitle) {
  //save content
  if(!local.help.title[ttitle]) {
    local.help.title[ttitle]=info.titletext;
    local.help.value[ttitle]=info.valuetext;
  }
  $('helper_head_span').innerHTML=info.titletext;
  var body = $('helper_body');
  var content = info.valuetext[0].replace(/\n/g,'<br>');
  if(ttitle!='content') { //append a link back to overwiev
    content +='<br><br><span class="link" onclick="help_load(\'content\')">'+la.content+'</span><br>';
  }
  body.update(content);
  var helper=$('helper');
  helper.show();
}

function help_toggle() {
  var body=$('helper_body');
  if( !body ) { return 0; } //helper_body not found
  if(body.hasClassName('hide')) {
    body.removeClassName('hide');
    $('helper').setStyle({ 'opacity' : 1 });
  } else {
    body.addClassName('hide');
    $('helper').setStyle({ 'opacity' : 0.5 });
  }
}

function help_close() {
  $('helper_body').removeClassName('hide'); //so it will be hidden when loading again
  local.help.opened=null;
  $('helper').hide();
}
