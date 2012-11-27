//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: show.js
//GUI: default
//description: Javascript for showing words (show.php)
//Author: David Glenck
//Licence: GNU General Public Licence (see license.txt in Mainfolder)
//////////////////////////////

/////////////
//-----------
// SHOW
//-----------
/////////////

//////////
// UPDATE
//////////
// 05.04.2012 - Fix invisible X icon in wordtable

//////////
//INITIALIZER
//////////

initializer_add = 
initializer_show = function() {
  //initialize elements in toolbar
  update_toolbar( plk.here('page') );
  //initialize the checkscroll function
  checkscroll();
}

function initializer_search() { 
  get_searchresult();
}

function initializer_overview() {
  //make sure, checkscroll is started when switching to show-tab
  local.wordlist_complete = 1;
  update_overview();
}

//////////
//EVENT
//////////

function eventloader_overview( where ) { 
  //default
  eventloader_show( where );

  //unhighlight all
  where.select('[class~="highlighted_element"]').each(function(item) {
    item.removeClassName('highlighted_element');
  })
  //highlight current element
  var elements = ['group','tag','save','wordclass','without'];
  for(var i=0; i<elements.length; i++) {
    //highlight now
    where.select('.'+elements[i]+'_'+plk.here( elements[i]+'id' )).each(function(item) {
      item.addClassName('highlighted_element');
    })
  }
}

//Adds events to the elements. Must be called if new elements are loaded.
eventloader_search =
eventloader_add =
eventloader_show = function( where ) {
  //show lastchild of lastchild on mouseover and hide it on mouseout
  where.select('.event_hidelast_in').each( function(item) {
    item.onmouseover=function() { this.lastChild.lastChild.removeClassName('hidden'); }; //Fix : 05.04.2012
    item.onmouseout=function() { this.lastChild.lastChild.addClassName('hidden'); }; 
  } );

  //switch on tabclick
  where.select('[id^="tab_"]').each( function(item) {
    item.onclick=function() { tab_switch(this.id.slice(4)); };
  } );

  //do all for wordtable tr -> highlight and show lastchild
  where.select('tr[id^="wordlist_tr"]').each( function(item) {
    item.onmouseover=function() { 
      this.lastChild.lastChild.removeClassName('hidden'); //Fix : 05.04.2012
      highlighthover(this, true);
    };
    item.onmouseout=function() { 
      this.lastChild.lastChild.addClassName('hidden'); //Fix : 05.04.2012
      highlighthover(this, false);
    }; 
  } );

  //dropdown giver have a dropdown on click and should work well with dropdowns
  where.select('.dropdown_giver').each( function(item) {
    item.onmouseover = hold_dropdown;
    item.onmouseout = function() { hide_dropdown(); }; //doing it like this because otherwise this has no delay
  });
}

//////////
//PRINT LOADED
//////////

//appendit: appends html code to the wordlist
function appendit(input,position) {
  try{
    position = position || 0; //default
    var tbody = $('wordlist').down('tbody');
    if( position == 0 ) {  //insert at the end
      tbody.insert( { bottom: input } );  
    } else if( position == 1 ) { //insert on top
      tbody.insert( { top: input } );  
    }

    // attaches events to the elements
    eventloader( tbody );
    // hide hidden columns
    hidecolumns();

    close_shutter();

    if(input) { 
      // this makes the flowloader go on
      checkscroll();
    } else {
      // set flag that checkscroll() stopped running
      local.wordlist_complete = 1;
    }
  } catch(err) { msg('Error[appendit]: '+err); }
}

//Load words for the wordtable to append it.
function appendwords(param, fromlim, tolim, position) {
  try{
    var parameters = Object.clone(param);
    var params = {};
    //set limits
    parameters['fromlim'] = fromlim;
    parameters['tolim'] = tolim;

    //set location and function to call
    params['location'] = 'php/wordtable.php';
    params['function'] = 'wordform';
    params['json'] = 1;

    //checkboxes
    if( !params['parameters'] ) { //not defined yet
      var checked=$('check_allmarked'); //this checkbox will decide if all are checked
      if( checked ) {
        params['parameters'] = Object.toJSON([parameters, checked.checked?1:0, plk.here('registerid')?0:1]); //checkboxes checked?
      } else {
        params['parameters'] = Object.toJSON([parameters, 0, plk.here('registerid')?0:1 ]);
      }
    }

    //load html
    plk.req('get_function', params, function(i,p,s) { appendit(i.output, s) }, position);

  } catch(err) {
    msg('Error[appendwords]: '+err);
  }
}

//////////
//WORDTABLE EFFECTS
//////////

//highlights or unhighlights a line in the wordtable
function highlighthover(item, on) {
  //if is not marked
  if( !Element.hasClassName(item, 'list_marked') ) {
    //toggle hover
    if( on ) {
      item.addClassName('list_hover').removeClassName('list_none');
    } else { 
      item.addClassName('list_none').removeClassName('list_hover');
    }
  }
}

//checks the checkbox of a line ..
function checkthisbox(box) {
  var chbox = $('check_'+box); //get element
  if( chbox ) {
    chbox.checked=!chbox.checked; //check
    checkbox_updatehighlight(chbox);   //highlight
  }
}

//(un)highlights line of item if (un)checked
function checkbox_updatehighlight(item) {
  //line of checkbox
  var tr = $('wordlist_tr_'+item.value);
  if(tr) {
    if(item.checked) { //checkbox is checked
      tr.addClassName('list_marked').removeClassName('list_none').removeClassName('list_hover');
    } else {  //unchecked
      tr.addClassName('list_none').removeClassName('list_marked');
    }
  }
}

//(un)check all words in the wordtable
function checkbox_checkall(on) {
  //loop through every checkbox in wordform
  $$('input[id^="check_"]').each( function(item) {
    item.checked = on; //(un)check
    checkbox_updatehighlight(item); //highlight
  } )
}

//////////
//WORDTABLE COLUMNS
//////////

//shows and hides columns in wordtable
function displaycolumn(show,column) {
  //hide the dropdown
  hide_dropdown(0);
  //hide row
  $$('[id^='+column+'_]').each( function(item) {
    if( show ) { item.show(); }
    else { item.hide(); }
  } );
}

//hide hidden columns
function hidecolumns() {
  $$('th[id$="_head"]').each( function(item) {
    if(item.getStyle('display') == 'none') {
      var column = item.identify().slice(0, -5);
      displaycolumn(0,column);
    }
  } );
}

//////////
//FLOWLOADER
//////////

//Checks if user scrolled enough to see next words
function checkscroll() {
  //if this is not show or add, stop.
  //will start again, when changing tab
  if ( plk.here('page')!='show' && plk.here('page')!='add' ) {
    local.wordlist_complete=1; //so it will be restarted
    return false; //quit
  }
  //the content div
  var cntnt=$('content');
  //check if scrolling down
  if(cntnt.scrollHeight - cntnt.scrollTop-cntnt.clientHeight <= 300 
     && document.body.scrollHeight - document.body.scrollTop - document.body.clientHeight <= 300) {
    //count elements in Table
    fromlim=parseFloat($('wordlist').down('tbody').childNodes.length);

    //notify if no words were found (only in show)
    if( fromlim == 0 && plk.here('page') == 'show' ) { do_info( plk.la.err_102 ); }
    else { close_info( plk.la.err_102 ); }

    // show/hide related elements
    $$('.with_wordlist').each(function(item) {
      if( fromlim ) { item.show(); }
      else { item.hide(); }
    }); 
    // hide/show unrelated elements
    $$('.-with_wordlist').each(function(item) {
      if( fromlim ) { item.hide(); }
      else { item.show(); }
    }); 

    //update_information(); //20120407 - fix : this would hide the info that similar words exists

    // show/hide row of register
    displaycolumn( plk.here('registerid')?0:1, 'register' );

    //get those words and append them
    if( fromlim == 0 ) { do_shutter(1); } //shutter if list is empty
    appendwords(plk.here(), fromlim, 25, 0);

  } else {
    //try again in a second
    window.setTimeout('checkscroll()',1000);
  }
}

//////////
//LINK
//////////

//overwrite links from gui_functions.php
//make link to goto somewhere knowing type and id
link_goto_generic_search =
link_goto_generic_overview = 
link_goto_generic_add =
link_goto_generic_show = function(id, type, namevalue) {
  return '<li class="link menulink" onclick="do_action(\''+ id +'\',\''+ type +'\',\'goto\')">'+namevalue+'</li>';   
}

//////////
//HEAD

//link to hide row
link_hide_group =
link_hide_tag =
link_hide_wordclass =
link_hide_word = function(id, type) {
  return '<li class="menulink link columnbutton" onclick="javascript: displaycolumn(0,\''+type+'\')">'+plk.la['hide']+'</li>';
}

//////////
//ACTION
//////////

//overwrite action from gui_functions.php
//goto somewhere knowing type and id
action_goto_generic_show =
action_goto_generic_overview =
action_goto_generic_add =
action_goto_generic_search = function(id, type, params) {
  //shutter
  do_shutter(1);

  //remove wordid //mostly useless
  if( type != 'word' ) {
    plk.here.set({ wordid: null});
  }

  //update location
  if(type != null) {
    var param = {}; 
    param[type+'id'] = id;
    plk.here.set( param );
  }

  //switch to show or search
  if( plk.here('page') == 'add' ) { //stay on add page
    tab_switch('add',1);
  } else if( plk.here('searchid') != null ) {
    tab_switch('search',1);
  } else if(type == 'register') {
    tab_switch('overview',1); //force reload
  } else {
    tab_switch('show',1); //force reload
  }
}

action_goto_search_show =
action_goto_search_overview =
action_goto_search_add =
action_goto_search_search = function(id) {
  //shutter
  do_shutter(1);

  //remove wordid
  plk.here.clear('wordid');
  //update search
  plk.here.set({ searchid: id});  
  //also update searchtext
  plk.here.set({ searchtext: 'like: '+ id});
  //load show if search is empty
  if(plk.here('searchid') == null) {
    tab_switch('show',1); //force reload
  } else {
    tab_switch('search',1);
  }
}

//////////
//PAGE UPDATER
//////////

function update_show() {
  //close notification
  close_info();

  //clean wordtable
  //TODO: clean only if necessary
  $$('tr[id^="wordlist_tr"]').each(function(item) {
    item.remove();
  })

  //define if adding words or showing
  if( plk.here('page') == 'add' ) {
    var now = new Date().getTime() / 1000;
    now = Math.floor(now);
    plk.here.set({ "timerange" : [now - 3*60*60, now, 'DESC'] });
    //local.wordlist_complete = 1; //force reload of words
  } else { //else load normal
    plk.here.clear("timerange");
  }

  //fill with new content
  if( local.wordlist_complete == 1 && plk.here('page') != 'search') { //checkscroll isn't running now
    local.wordlist_complete = 0;
    checkscroll();
  } else { 
    close_shutter();
  }
  //switch tabs
  //tab_switch('show');
}

function update_overview() {
  try {
    //load new
    if( !local.cached_overview ) {
      local.cached_overview = 1;
      //shutter
      do_shutter(1);
      //reload overview
      var param = { 'location': 'php/tab.php', 
                    'function': 'overview',
                    'json': 1,
                    'parameters': Object.toJSON([ plk.here('registerid') ]) }
      plk.req('get_function', param, function(info) {
        var content = $('content_tab_overview');
        if( !content ) { throw 'could not find HTML Element with id tab_overview_content in update_overview'; }
        else {
          content.update( info.output ); //fill new content
          eventloader( content ); //load events
          refresh_subcount();
          close_shutter();
        }
      })
    } else {
      eventloader(); //update highlighting
      refresh_subcount();
      close_shutter();
    }
  } catch(err) {
    msg('Error[update_overview]: '+err);
  }
}

//search page is called
function update_search() {
  //set searchtext
  plk.here.set({ searchtext: 'like: '+plk.here('searchid') });
  //update results
  get_searchresult();

  close_shutter();
}

function refresh_subcount( itype ) {
  //update counts
  //default: refresh all
  itypes = itype ? itype : ['group','tag','wordclass','save','without'];
  //which types to refresh?
  if( itype !== undefined ) {
    if( typeof itype == 'string' ) {
      itypes = [ itype ];
    } else {
      itypes = itype;
    }
  }

  //save time: check if at least one type is defined here
  var none = true;
  for( var i = 0; i < itypes.length; i++ ) {
    if( plk.here( itypes[i] + 'id' ) != null ) {
      none = false; break;
    }
  }
  //stop if none are defined //no refresh needed
  if( none ) { 
    //restore all subcounts to invisible
    $$('[class*="_subcount_"]').each( function(item) { 
      item.hide();
    })    
    return false; 
  }

  //else refresh necessary
  //iterate
  for( var i=0; i<itypes.length; i++ ) {
    //20120407 - FIX : show correct numbers
    //first show them all with zeros
    //TODO "disable" element
    $$('[class*="'+itypes[i]+'_subcount_"]').each(function(item) { 
      item.show().update('0/'); 
    })

    //prepare request for counting.
    var params = plk.here(); //already cloned
    params[ itypes[i] + 'id' ] = null; //ignore ids from same type
    params['type'] = itypes[i]; //pass type to postfuntion
    params['gettags'] = 0;

    //withoutid is special
    if( itypes[i] === 'without' ) {
      //for tags
      var tparams = Object.clone( params );
      tparams['tagid'] = null;
      tparams['withoutid'] = 'tag';
      tparams['count'] = '1'; //count them   
      plk.req('get_word', tparams, update_subcount); 

      //for save
      //params is passed by reference before, shouldn't change it now.
      var sparams = Object.clone( params );
      sparams['saveid'] = null;
      sparams['withoutid'] = 'save'; 
      sparams['count'] = '1'; //count them  
      plk.req('get_word', sparams, update_subcount); 
      
    } else { //normal case
      params['count'] = itypes[i];
      plk.req('get_word', params, update_subcount); 
    }
  } //endfor
}

//function to load and write counters
//called from refresh_subcount above
function update_subcount(info,params) {
  var type = params.type;

  //go through each id recieved of type
  for(var j=0; j<info.count; j++) {
    //get id //withoutid is different;
    if(type == 'without') { var id = params.withoutid; }
    else { var id = info[type+'id'][j]; }
    //get fullcount
    var fullcount = $$('[class~="'+ type +'_fullcount_'+ id +'"]')[0];
    //iterate elements with subcount for that type
    if( fullcount && info['wordcount'][j] == fullcount.innerHTML ) {
      update_subcount_of(type, id); //hide subcount 
    } else {
      update_subcount_of(type, id, info['wordcount'][j]);
    }
    /* fix : same as above
    $$('[class~="'+ type +'_subcount_'+ id +'"]').each(function(item) {
      //if subcount = fullcount hide subcount.
      if( fullcount && info['wordcount'][j] == fullcount.innerHTML ) {
        item.hide();
      } else {
        item.update( info['wordcount'][j]+'/' );
      }
    }) 
    */ 
  }
}

//helper update subcount of specific type
function update_subcount_of(type, id, value) {
  $$('[class~="'+ type +'_subcount_'+ id +'"]').each(function(item) {
    //if subcount = null, hide subcount.
    if( value === null || value === undefined ) {
      item.hide();
    } else {
      item.update( value+'/' );
    }
  }) 
}

function update_toolbar(totab) {
  var tab_active = $$('.tab_active')[0];
  var tab_totab = $('tab_'+totab);

  if( tab_active ) tab_active.removeClassName('tab_active').addClassName('tab_inactive');
  if( tab_totab ) tab_totab.removeClassName('tab_inactive').addClassName('tab_active');

  //hide all elements in tabs
  $$('[class*="intab_"]').each(function(item) {
    item.hide();
  })
  //show elements in tab to show
  $$('[class~="intab_'+totab+'"]').each(function(item) {
    item.show();
  })
}

//////////
// UPDATE INFORMATION
//////////

function update_information_show() {
  if(plk.here('wordid') != null) { 
    do_info( plk.la.info_singleword +' <span class="link" onclick="do_action(null,\'word\',\'goto\')">'+ plk.la.showall +'</span>' );
  }
}

function update_information_search() {
  //write information when searching
  if(plk.here('registerid') != null) { 
    do_info( plk.la.info_singlesearch +' <span class="link" onclick="do_action(null,\'register\',\'goto\')">'+ plk.la.searchall +'</span>' );
  }
}

//////////
//TAB SWITCHER
//////////

function tab_switch( totab, force ) {
  // quit if tab is already showing
  if(plk.here('page') == totab && !force) { return false; }

  //shutter
  do_shutter(1);

  //load elements
  var content = $('content');
  //throw errors
  var error=0;
  if( !content ) { error= 1; throw "Warning [tab_switch]: could not switch tabs: $('content') is not an element"; }
  //update toolbar
  update_toolbar(totab);

  //update page
  update_page(totab); //redirects to update_"totab" in the end.f
}

/////////////
//-----------
// MANIPULATE
//-----------
/////////////

//////////
// Functions to manipulate multiple word data

//////////
// Load
// Fill Select
// List Dropdown
// Actions
// After Send
//////////
// Word
// Group
// Tag
// Save
//////////

//////////
//LOAD

//load tagdata of this location of specific wordids
//never cache
function load_data_tag_word(filltype, fillwhere, loadtype) {
  try{
    var tfill = fill(loadtype, filltype); //function to call fill_<filltype>_<loadtype>
 
    //prepare params (wordid)
    var params = plk.here();
    params['wordid[]'] = formfield2id('wordform', 'wordid[]');
    //load data from server
    plk.req('get_tag',params, function(info,p,s) {
      local.data.tag_word = info;
      window[ tfill ](s); 
    }, fillwhere);

  } catch(err) { msg('[load_data_tag_word]:'+err); }
}

//////////
//FILL SELECT

//fills a selection input with groups
function fill_select_group(fillwhere) {
  //write options
  var val='<option value="1">'+plk.la.group+' '+1+'</option>';
  val+='<option value="af">'+plk.la.af+'</option>';
  //fill
  if(fillwhere) {
    fillwhere.insert({after: val}).remove();
  }
}

//fill any select
function fill_select_generic(fillwhere, type) {
  //check if nothing found
  if(local.data[type].count == 0) { msg(plk.la['err_no'+type]); return false; }  
  //write options
  var i;
  var val = '';
  for (i=0; i<local.data[type].count; i++) {
    val += '<option value="'+local.data[type].id[i]+'"';
    if(plk.here(type+'id') == local.data[type].id[i]) { val += ' selected'; } //select here
    val += '>'+local.data[type].name[i]+'</option>';
  }
  //fill
  if(fillwhere) {
    fillwhere.insert({after: val}).remove();
  }
}

//catch tag list if empty
function fill_select_tag_word(fillwhere) {
  if(local.data.tag_word.count == 0) { msg(plk.la['err_notag']); return false; } 
  else { fill_select_generic(fillwhere, 'tag_word'); }  
}

//////////
//LIST MANIPULATION DROPDOWN

//multi edit links in dropdown
function load_data_more_options(filltype, fillwhere, loadtype) { 
  var func = fill(loadtype, filltype);
  if( func ) { window[ func ](fillwhere, loadtype) }; 
}

function fill_list_more_options( fillwhere ) {
    //Delete
    var val='<li class="menulink link icon_text" onclick="do_action(\'wordform\',\'word\',\'multi_delete\')" ><span class="icon iconx"></span>'+plk.la['deletewords']+'</li>';
    //Move
    val += '<li class="menulink link icon_text" onclick="do_action(\'wordform\',\'word\',\'multi_move\',\'popup\')"><span class="icon iconarrowright"></span>'+plk.la['movewords']+'</li>';
    if(plk.here('registerid') != null) {
      val += '<hr>';
      //Addtag
      val += '<li class="menulink link icon_text" onclick="do_action(\'wordform\',\'tag\',\'multi_addto\',\'popup\')"><span class="icon iconplus"></span>'+plk.la['addtag']+'</li>';
      //Deletetag
      val += '<li class="menulink link icon_text" onclick="do_action(\'wordform\',\'tag\',\'multi_delete\',\'popup\')"><span class="icon iconx"></span>'+plk.la['deletetag']+'</li>';
      val += '<hr>';
      //add new save
      val += '<li class="menulink link icon_text" onclick="javascript: do_action(\'wordform\',\'save\',\'add\',\'popup\')"><span class="icon iconplus"></span>'+plk.la['newsave']+'</li>';
      //Addtosave
      val += '<li class="menulink link icon_text" onclick="do_action(\'wordform\',\'save\',\'multi_addto\',\'popup\')"><span class="icon iconarrowright"></span>'+plk.la['inserttosave']+'</li>';
    }
  //Deletefromsave
    if(plk.here('saveid') != null) {
      val += '<li class="menulink link icon_text" onclick="do_action(\'wordform\',\'save\',\'multi_removefrom\',\'popup\')"><span class="icon iconx"></span>'+plk.la['removefromsave']+'</li>';
    }
  //insert the list
  put_list(val, fillwhere);
}

//////////
//MANIPULATION ACTIONS

//multi_move_word moves multiple words
function action_multi_move_word() {
  try {
    //load wordid from wordform to id, quit if empty
    id = formfield2id('wordform','wordid[]');
    if(!id) { return false; }  

     
    var fparams = new plk.formObj('moveto','move');
    //create select for register with text: loading
    //fparams.addselect('movetoreg','register');
    //fparams.select['movetoreg'].addoption({'null':plk.la.loading});
    //create select for group with text: loading
    fparams.addselect('movetogroup','group');
    fparams.select['movetogroup'].addoption({'null':plk.la.loading});
    //prepare request
    var moveto = new plk.reqObj( 'edit_multiword',
      {'wordid[]':id},
      after_send, 
      {id:id,action:'multi_move',type:'word',key:0}
    );
    //show form
    request_form( moveto, fparams );  

    //now load an fill the selects
    load_data('select', $('movetogroup').down(), 'group');
    //load_data('register', 'select', $('movetoreg').down());
    
  } catch(err) { msg('[action_multi_move_word]:'+err); }
}

function action_multi_delete_word( id ) {
  try {
    //load wordid from wordform toid, quit if empty
    id = formfield2id('wordform','wordid[]');
    if(!id) { return false; }  

    //prepare request
    var delmarked = new plk.reqObj( 'delete_word', {'wordid[]':id}, after_send, {id:id,action:'multi_delete',type:'word',key:0} )

    //ask again
    ask(plk.la['askworddels'], function() { 
      delmarked.send();
    });
  } catch(err) { msg('[action_multi_delete_word]:'+err); }
}

function action_multi_addto_tag( id ) {
  try {  
    //load wordid from wordform to id, quit if empty
    id = formfield2id('wordform','wordid[]');
    if(!id) { return false; }  

    //prepare form
    var fparams = new plk.formObj('addtag');
    fparams.addinput('newtag','name');

    //prepare request
    var addtag = new plk.reqObj( 'add_tag',
      {'registerid':plk.here('registerid'), 'wordid[]':id},
      after_send, 
      {id:id,action:'multi_addto',type:'tag',key:0}
    );

    //show form
    request_form( addtag ,fparams );    
  } catch(err) { msg('[action_multi_addto_tag]:'+err); } 
}

//delete one tag from multiple words
function action_multi_delete_tag( id ) {
  try {
    //load wordid from wordform to id, quit if empty
    id = formfield2id('wordform','wordid[]');
    if(!id) { return false }  
  
    var fparams = new plk.formObj('deletetag','delete');
    //create select for tads with text: loading
    fparams.addselect('tagid','tag');
    fparams.select['tagid'].addoption({'null':plk.la.loading});

    //prepare request
    var deletetag = new plk.reqObj( 'delete_tag',
      {'wordid[]': id},
      after_send, 
      {id:id,action:'multi_delete',type:'tag',key:0}
    );

    //show form
    request_form( deletetag, fparams );  

    //now load an fill the selects
    load_data('select', $('tagid').down(), 'tag_word');

  } catch(err) { msg('[action_multi_delete_tag]:'+err); }
}

//create a new save with the selected words
function action_add_save( id ) {
  try {
    //load wordid from wordform to id, quit if empty
    id = formfield2id('wordform','wordid[]');
    if(!id) { return false; }  

    //prepare form
    var fparams = new plk.formObj('newsave');
    fparams.addinput('newsave','name');

    //prepare request
    var addsave = new plk.reqObj( 'create_save',
      {registerid: plk.here('registerid') , 'wordid[]':id},
      after_send, 
      {id:id,action:'add',type:'save',key:0}
    );

    //show form
    request_form( addsave, fparams );
  } catch(err) { msg('[action_add_save]:'+err); }  
}

function action_multi_addto_save( id ) {
  try {  
    //load wordid from wordform to id, quit if empty
    id = formfield2id('wordform','wordid[]');
    if(!id) { return false; }  


    //prepare form
    var fparams = new plk.formObj('insertto','insert');
    fparams.addselect('newsaveid','savepoint');
    fparams.select['newsaveid'].addoption({'null':plk.la.loading});

    //prepare request
    var insertinto = new plk.reqObj( 'add_tosave',
      {'registerid':plk.here('registerid'), 'wordid[]': id},
      after_send, 
      {id:id,action:'multi_addto',type:'save',key:0}
    );

    //show form
    request_form( insertinto ,fparams);    

    //load saves and update the select
    load_data('select', $('newsaveid').down(),'save');

  } catch(err) { msg('[action_multi_addto_save]:'+err); }  
}

function action_multi_removefrom_save( id ) {
  try {  
    //load wordid from wordform to id, quit if empty
    id = formfield2id('wordform','wordid[]');
    if(!id) { return false; }  

    var remfsave = new plk.reqObj( 'delete_fromsave',
      {'saveid':plk.here('saveid'), 'registerid':plk.here('registerid'), 'wordid[]':id},
      after_send, 
      {id:id,action:'multi_removefrom',type:'save',key:0}
    ); 
    
    ask(plk.la['delwordfromsave'], function() { 
      remfsave.send();
    });
  } catch(err) { msg('[action_multi_addto_tag]:'+err); } 
}

//////////
//AFTER SEND

//Updates Information of multiple words in the Table
//for function moveword
function after_send_multi_move_0_word(info,params) {
  close_popup();

  //make wordid and array
  var wordid = params['wordid[]'];
  if(typeof wordid != 'object') { var wordid=[wordid]; }
  //walk through wordids
  for(var i=0; i<wordid.length; i++) {
    var tid=wordid[i];
    //if word isn't here anymore //params.movetoreg != here.registerid ||
    if(plk.here('groupid') != null && params.movetogroup != plk.here('groupid')) {
      $$('[id="wordlist_tr_'+tid+'"]')[0].remove();
    //if group has changed
    } else if(params.movetogroup!=0) {
      if( params.movetogroup == 'af' || params.movetogroup == 'ar' ) {
        var gr = plk.la['a_'+params.movetogroup];
      } else { var gr = params.movetogroup }
      $$('[id="group_span_'+tid+'"]')[0].update( gr );
    }
  }

  //notify
  if( wordid.length != 0 ) {
    do_info( wordid.length +' '+ (wordid.length==1?plk.la.info_word_moved:plk.la.info_words_moved) );
  }
}

//words have been deleted
function after_send_multi_delete_0_word(info,params) {
  //remove words from table  
  cleaner(params["wordid[]"],"word");

  //notify
  if( info.count != 0 ) {
    do_info( info.count +' '+ (info.count==1?plk.la.info_word_deleted:plk.la.info_words_deleted) );
  } 
}

//after multiple tags were added to multiple words update them
function after_send_multi_addto_0_tag(info, params) {
  try {
    close_popup();

    //load html element for tag
    var nparams = get_function_writeone('tag');
    nparams['json']=1; //because id may be an array
    for(var i=0; i<info.countword; i++) {
      var ttagid = [];
      var ttags = [];
      //remove each tag from list, that allready is there
      for(var j=0; j<info.id.length; j++) {
        //only copy if it doesn't exist
         if( !$('tag_span_'+info.wordid[i]+'_'+info.id[j]) ) {
          ttagid.push(info.id[j]);
          ttags.push(info.name[j]);
        }
      }
      if( info.id.length == 0 ) { return false; } //if nothing is left

      nparams['parameters'] = Object.toJSON([info.wordid[i], ttagid, ttags]); 
      plk.req('get_function', nparams, function(info,p,s) {
        //append each tag
        appender(info.output, 'tag', $('tag_'+s), 'top');
      }, info.wordid[i]);
    }

    //notify // # tags have been added
    if( info.id.length != 0 ) {
      do_info( info.id.length +' '+ (info.id.length==1?plk.la.info_tag_added:plk.la.info_tags_added) );
    }
  } catch(err) { msg('[after_send_multi_addto_0_tag]:'+err); errnum=1; }
}

//remove tags after deleting tags from multiple words
function after_send_multi_delete_0_tag(info,params) {
  //close the form
  close_popup();
  //shortcut id
  var id = params['wordid[]'];
  //make it an array
  if(typeof id != 'object') { id=[id]; }
  //walk through and remove
  for(var i=0; i<id.length; i++) {
    //remove tag from list
    if( cleaner(id[i]+'_'+params.tagid, "tag") ) { //if tag removed
      if( plk.here('tagid') == params.tagid ) { //if the tag we are showing was removed
        cleaner(id[i], "word" ) //also remove the whole word from list
      }
    }
  }

  //notify // # tags have been deleted
  if( id.length != 0 ) {
    do_info( plk.la.info_tag_deleted );
  }
}

//words have been added to storage
function after_send_multi_addto_0_save(info,params) {
  close_popup();
  
  //notify
  var len = params['wordid[]'].length;
  if( len != 0 ) {
    do_info( len +' '+ (len==1?plk.la.info_word_addedto_save:plk.la.info_words_addedto_save) );
  }  
}

//storage created
function after_send_add_0_save(info,params) {
  close_popup();
  
  //notify
  if( params['wordid[]'].length != 0 ) {
    do_info( plk.la.info_save_created );
  }  
}

//words remove from storage
function after_send_multi_removefrom_0_save(info,params) {
  //remove words from table
  cleaner(params["wordid[]"],"word");

  //notify
  if( info.count != 0 ) {
    do_info( info.count +' '+ (info.count==1?plk.la.info_word_removedfrom_save:plk.la.info_words_removedfrom_save) );
  }    
}

/////////////
//-----------
// ADD WORD
//-----------
/////////////

//Checks if form is filled in correctly
//in the addword form
function checkform() {
  try {
    var addw=$('addword');
    if(addw.newwordfirst.value=='') { 
      do_info( plk.la.err_231 ); 
      addw.newwordfirst.focus(); 
      return false; 
    } else if(addw.newwordfore.value=='') { 
      do_info( plk.la.err_232 ); 
      addw.newwordfore.focus(); 
      return false; 
    } else {
      var errmsga = plk.word.validate(addw.newwordfirst.value);
      var errmsgb = plk.word.validate(addw.newwordfore.value);
      if(errmsga>0) { 
        do_info(plk.la.err_invalidsyntax+'<!-- '+errmsga+' -->');
        addw.newwordfirst.focus();
        return false;
      } else if(errmsgb>0) { 
        do_info(plk.la.err_invalidsyntax+'<!-- '+errmsgb+' -->');
        addw.newwordfore.focus();
        return false;
      }
    }
    close_info();
    return true;
  } catch (err) { msg('[checkform]:'+err); return false; }
}

//sending the addword form will go here
function action_add_word() {
  //check if everything is alright
  if( checkform() ) {
    //no specific sort of sending
    //local.current.inputkey=0;
    //send request //reset form before //
    var input = $('addword').serialize(true);
    input.force = 1; //always force
    //local.current.force = 0 //reset for next call
    //call resetform then create word then call after_send_<action>_0_<type>()
    plk.req('create_word', input, [resetform, after_send], {id:0, type:'word', action:'add', key:0});
  }
}

//after a word is created
//switch through all possible cases
function after_send_add_0_word(info, params, id, type) {
  try{
    //similar word was found  
    if(info['similar']==1) {
      //notify and compare
      info.similarid.unshift(info.id);
      do_info(plk.la.info_similar+" <span class='link' id='link_compare'>"+plk.la.compare+"</span>");
      $('link_compare').onclick = function() {
        plk.here.clear(['tagid','wordclassid','groupid']);
        do_action( info.similarid, "word", "show");
        info.similarid = null; //anti-memory-leak
      }
    } else {
      close_info();
    }

    //stop here if no id was passed (nothing added)
    if(info.id == null) { return false; }

    //update location with some ids
    //pick relevant ids
    var ids = ['register','group','wordclass'];
    for(var j=0; j<ids.length; j++) {
      var param = {}; param[ ids[j] +'id'] = info[ ids[j] ];
      plk.here.set(param);
    }
    if(info.taglist.count) { 
      plk.here.set({'tagid' : info.taglist.id[0]}); //only one tag can be saved
    }

    //update this location with inputs
    plk.hash.update();
    update_navigator();

    //show everything
    $('wordlist').show();  //show table of recently added words
    $('lastlabel').show(); 
    //Append new added word: fromlim:0, tolim:1, insert on top:1
    appendwords( {'wordid': info.id} ,0,1,1); //(in show.js)

  } catch (err) { msg("[after_send_add_0_word]"+err); }
}

//load location to add a word //overwrite
action_addword_generic_show =
action_addword_generic_overview =
action_addword_generic_search = function(id, type) {
  //update location
  if(type) {
    var params = {}; params[ type+'id' ] = id;
    plk.here.set(params);
  }
  //load "tab"
  tab_switch('add');
}

//(Re)Store Form addword
// Stores Form an resets it
function resetform() {
  var addw=$('addword');
  //save to restore later
  local.register=addw.newregister.selectedIndex;
  local.group=addw.newgroup.selectedIndex;
  local.wordfirst=addw.newwordfirst.value;
  local.wordfore=addw.newwordfore.value;
  local.satz=addw.newsentence.value;
  local.tags=addw.newtags.value;
  local.wordclass=addw.newwordclass.selectedIndex;
  //reset manualy
  addw.newwordfirst.value = '';
  addw.newwordfore.value = '';
  addw.newsentence.value = '';
  //rest is not changed
  //focus
  addw.newwordfirst.focus();
}
// restores the form from resetform()
function restoreform() {
  var addw=$('addword');
  addw.newwordfirst.value=local.wordfirst;
  addw.newwordfore.value=local.wordfore;
  addw.newsentence.value=local.satz;
  addw.newtags.value=local.tags;
  addw.newwordclass.selectedIndex=local.wordclass;
}
//update form with new here
function update_add() {
  //close the notification
  close_info();
  //remove wordid from path
  plk.here.clear('wordid');

  //insert options corresponding to current location
  var addw=$('addword');
  var regsel = $(addw.newregister).select('option[value="'+plk.here('registerid')+'"]')[0]; //FIX: IE $(...)
  if( regsel ) regsel.selected = true;
  var groupsel = $(addw.newgroup).select('option[value="'+plk.here('groupid')+'"]')[0];
  if( groupsel ) groupsel.selected = true;
  addw.newwordclass.selectedIndex = plk.here('wordclassid');

  //reappend submit button (IE8 "hidden submit button" fix)
  var submit = $('addword_submit');
  submit.insert( {after: submit} );

  //focus first input
  addw.newwordfirst.focus();  
  
  //refresh words
  update_show();
}

//////////
//SEARCH RESULTS

function update_searchresult_show() {
  //stop checkscroll
  local.wordlist_complete = 1;
  update_searchresult_search();
}

//update search results on screen for page search
// -> load words of results
function update_searchresult_search() {

  if( plk.here('searchid') == '' ) { 
    tab_switch('show'); // switch back if searchstring is empty 
    return 0;
  }

  var limit = 50; //set limit for words to be loaded
  var st = plk.here('searchid'); //shortcut
  var subt = local.current.subtext;
  var result = local.current.searchresult //shortcut

  //if words are not loaded -> load
  var get_wordid = [];
  for( var i=0; i<result.length; i++ ) {
    var s = result[i]; //shortcut
    if(s[0] != 'word') { continue; } //only want words
    if(plk.here('registerid') && local.as[ subt ]['word']['registerid'][ s[2] ] != plk.here('registerid')) { continue; } //only want local words
    if( $( 'wordlist_tr_'+local.as[ subt ]['word']['id'][ s[2] ] ) ) { continue; } //this word is loaded
    get_wordid.push( local.as[ subt ]['word']['id'][ s[2] ] );

    if( get_wordid.length > limit ) { break; } //stop at limit
  }

  //get words if not empty
  if( get_wordid.length ) {
    var param = {};
    param['location'] = 'php/wordtable.php';
    param['function'] = 'wordform';
    param['json'] = 1;
    param['parameters'] = Object.toJSON([ { wordid: get_wordid }, 0, plk.here('registerid')?0:1 ]);    
    plk.req('get_function',param, function(info) {
      appendit(info.output); //append found words
      refresh_searchtab();
    })
  }
  //if empty: all words are already loaded
  else {
    refresh_searchtab();
  }
}

// helper
function put_wordtable_line( element , index) {
  try {
    var elem = $('wordlist');
    if( !elem ) { throw '[0] $("wordlist") is not defined'; }
    var elem = elem.down('tbody');
    if( index < elem.childNodes.length ) {
      // child # i -> move the line there and show it
      Element.insert( elem.childNodes[index] , { before: element } );
    } else {
      //append at the bottom
      Element.insert( elem , { bottom : element } );
    }
  } catch(err) { 
    msg('[put_wordtable_line]:'+err); 
  }
}

// Update the Searchresults to the wordtable
function refresh_searchtab() {
  try {
    var result = local.current.searchresult //shortcut
    var st = plk.here('searchid'); //shortcut
    var subt = local.current.subtext;

    //break if no search results were are saved 
    if( !result ) { return 0; }

    //hide all lines from wordlist
    $$( '[id^="wordlist_tr_"]' ).invoke('hide');

    var j=0;
    for( var i=0; i<result.length; i++ ) {
      var s = result[i]; //shortcut
      if(s[0] != 'word') { continue; } //only want words
      if(plk.here('registerid') && local.as[ subt ]['word']['registerid'][ s[2] ] != plk.here('registerid')) { continue; } //only want local words
      //get wordid;
      var id = local.as[ subt ]['word']['id'][ s[2] ];

      var tr = $( 'wordlist_tr_'+id ); //the line    
      if( tr ) { //line exists
        //put the line to the right position
        put_wordtable_line( tr , j);
        tr.show();
        j++;
      }
    }

    //show columns register if needed
    displaycolumn( plk.here('registerid')?0:1, 'register' );

    //but show the table
    $('wordlist').show();

  } catch(err) { 
    msg('[refresh_searchtab]:'+err+' id: '+id); 
    local.error = {tr:tr};
  }
}
