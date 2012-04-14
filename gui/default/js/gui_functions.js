//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: gui_functions.js
//GUI: default
//description: Global Javascript functions for this GUI.
//Author: David Glenck
//Licence: GNU General Public Licence (see license.txt in Mainfolder)
//////////////////////////////

//////////
//CONTENT
//  Global Variables ............ Define variables for global use
//  Initialize .................. Called onload
//  Corehandle .................. Catch functions from core
//  Utility ..................... Small usefull tools 
//  Generic Events .............. Structure to handle every event
//  - Helper .................. Help redirecting function calls
//  - Updater ................. Update information on screen
//  - Constructor ............. Add information to screen
//  - Load Data ............... Get data from database
//  - Fill List ............... Fill list with received data
//  - Actions ................. initialize action
//  - Input Keynavigation ..... Define actions on keypress
//  - Send edit ............... Sending information to edit the database
//  - Send add ................ Sending information to add new entries to the database
//  - After send edit ......... Called after editing
//  - After send add .......... Called after adding
//  - Links ................... Create Link
//  - Close ................... Close something
//  Pagehash .................... Handle hash in path
//  Form ........................ Create a form from data 'formtype' (of core javascript)
//  Display Tools ............... Showing and hiding HTML elements on screen
//  - Popup
//  - Dropdown
//  - Status
//  Searchbar & Autosearch ...... Make the searchbar possible
//  Dev ......................... functions in development
//////////

//////////
//UPDATES
//////////
// 04.04.2012 : - Fix: more than selected words were queried in "add word"
//              - Fix: not every word was queried altough allmarked was checked

//////////
//GLOBAL VARIABLES
//////////

//Store Information
local.mouseisdown=0; //1 if mouse is clicked and hold
//local cache
local.current = {};
local.data = {};
local.counter = {};

//refresh mouse position
function mouseposition(e) {
  if (!e) { e = window.event; }
  local.mouseX=e.clientX;
  local.mouseY=e.clientY;
}

//////////
//INITIALIZE
//////////
function initializer() {
  //initialize the hash change detector  
  onHashChange();

  //show the loading time
  showtime(time);
  //track mouse position
  document.onmousedown=mouseposition;

  //hide ajaxloader
  $('ajaxloader').hide();

  //Searchbar
  $('searchform').onsubmit=function() { return false; };
  $('searchtext').onblur=function() { hide_search(); };
  $('searchtext').onfocus=function() { hold_search(); };

  //info
  $('close_info').onclick = function() { close_info(); };

  //call specific initializer for the page
  var func = switcher( 'initializer', here.page );
  if(func) { window[func](); }

  //call specific function to initialize info
  var func2 = switcher( 'update_information', here.page );
  if(func2) { window[func2](); }

  //close shutter when finished
  close_shutter();
}

//Adds events to the elements. Must be called if new elements are loaded.
function eventloader(where) {
  if( where == undefined ) { where = $(document.body); }

  //show lastchild on mouseover and hide on mouseout
  where.select('.event_hidelast').each( function(item) {
    item.observe('mouseover', function() { this.lastChild.removeClassName('hidden'); });
    item.observe('mouseout', function() { this.lastChild.addClassName('hidden'); });
    //and hide it now
    item.lastChild.addClassName('hidden');
  } );

  //show firstchild on mouseover
  where.select('.event_hidefirst').each( function(item) {
    item.observe('mouseover', function() { this.firstChild.removeClassName('hidden'); });
    item.observe('mouseout', function() { this.firstChild.addClassName('hidden'); }); 
    //and hide it now
    item.firstChild.addClassName('hidden');
  } );

  //hover effect for browser that doesn't support .class:hover
  if( you.browser !== 'gc' ) {
    where.select('.link').each( function(item) {
      item.observe('mouseover', function() { Element.addClassName(this, 'highlighted_element'); });
      item.observe('mouseout', function() { Element.removeClassName(this, 'highlighted_element'); }); 
    } );
  }

  //call specific eventloader for the page
  var func = switcher( 'eventloader', here.page );
  if(func) { window[func]( where ); }

  return where;
}

document.observe("dom:loaded", function() { initializer(); eventloader(); });

//shows loading time
function showtime(time) {
  try {
    var ntime = new Date().getTime() / 1000;
    var loadt = ntime-time;
    $('loadtime').update( String(Math.round(loadt*100000)/100000)+'s' );
  } catch(err) {}
}

//////////
//COREHANDLE
//////////
//Catch Events from Core

//Output: table with wrong answers
function gui_wrong(out) {
  msg(out);
}

//Output: Errors
function gui_error(out) {
  msg(out);
}

//Shows a loading icon
function gui_ajaxcatch(num) {
  var jxl=$('ajaxloader');
  if(num===0) { jxl.show(); }
  else if(num===8) { jxl.hide(); }
  hid = window.setTimeout("$('ajaxloader').hide()", 5000);
}

//Output: Request errors
function gui_request_error(info) {
  if( DEBUG != 1 ) { 
    //catch some errors to show nicely
    func = switcher( 'show_error_'+ info.errnum , info.errloc )
    if(func) { window[func](info.errloc); }

    return false; 
  }
  //basic debug error
  var out = 'DEBUG ERROR ';
  if( la['err_'+info.errnum] ) { out += la['err_'+info.errnum]; }
  else { out += la.err_unknown; }
  out += ' - '+info.errnum+': '+info.errname+' @ '+info.errloc+' - '+info.lastquery;
  msg(out);
}

//after a new hash is loaded from the url
function gui_load_hash() {
  //clear data
  clear_data();

  //switch tab //force
  tab_switch( here.page, 1 );
}

//when 'here' is changed, this is called
function gui_refresh_here(entry) {
  if(entry === 'registerid') { //registerid was changed
    clear_data(['save','tag','verb','person','form']);  //clear local cache
    local.cached_overview = 0; //will force the overview to completely reload
  }
}

//////////
//UTILITY
//////////

//Loads a simple message on screen
function msg(msge) {
  var input='<div class="popup_inner">'+msge+'</div><form action="javascript: return false"><input id="ok_button" type="button" value="'+la.ok+'" onclick="close_popup()"></form>';
  $('popup').update( input );
  do_popup();
  $('ok_button').focus();
}

//Loads a dialog before function
function ask(msg,action) {
  input=msg+"<form action='javascript: return false' name='popup_form' id='popup_form'><input type='button' onclick='close_popup(); "+action+"' id='popup_yes' value='"+la[1]+"'><input type='button' onclick='close_popup()' id='popup_no' value='"+la[0]+"'></form>";
  $('popup').update( input );
  do_popup(); 
  $('popup_yes').focus();
}

//returns the first element if is array of object. returns input otherwise
function firstelement(elem) {
  if(typeof elem === 'object') {
    return elem[0];
  } else {
    return elem;
  }
}

//if local.current.id is id of a wordform:
//save input 'field' from a form if id 'formid' to id
//return false if no form with given id, null if id is empty, id otherwise
function formfield2id(formid, field) {
  //if there is no such form -> return false //fix: check if object and not null
  var f = $(formid);
  if( !f || typeof f !== 'object' ) { return false; } 
  //serialize data from form if it exists
  var id = f.serialize(true)[field];
  //if no word is selected quit -> return false
  if(id === undefined)  { msg(la.err_209); return null; }     
  else { return id; }
}

//finds name of type with id in local data
function get_name(type, id) {
  var key = local.data[type].id.indexOf(id);
  return local.data[type].name[key];
}

//return keycode of event
function get_key(ev) {
  var keynum;
  if(ev) {
    if(window.event) { keynum = ev.keyCode; }
    else if(ev.which) { keynum = ev.which; }   
  }
  return keynum;
}

//Gets position on screen of an object
//obj: html-element
//returns position [left, top]
function getposition(obj) {
  var rleft = 0;
  var rtop = 0;
  do{ //loop through parents and add up offset
    rleft += obj.offsetLeft;
    rtop += obj.offsetTop;
  } while ( obj=obj.offsetParent )
  return [rleft, rtop];
}

//return only non-empty values of here
function get_here() {
  var ret = {};
  for(var i in here) {
    if( here[i] ) {
      ret[i] = here[i];
    } 
  }
  return ret;
}

//////////
//GENERIC EVENTS
//////////

//////////
//HELPER

//helper: switcher switches between specific and generic functions
function switcher(funcbase,type) {
  try {
    //typespecific + pagespecific
    if( typeof window[funcbase+'_'+type+'_'+here.page] === 'function') { 
      return funcbase+'_'+type+'_'+here.page;
    //typespecific
    } else if( typeof window[funcbase+'_'+type] === 'function') { 
      return funcbase+'_'+type;
    //page specific
    } else if( typeof window[funcbase+'_generic_'+here.page] === 'function') { 
      return funcbase+'_generic_'+here.page;
    //generic
    } else if( typeof window[funcbase+'_generic'] === 'function') {
      return funcbase+'_generic';
    } else { return false; }
  } catch(err) { msg('[switcher]:' + err); } 
}

//closes action: switches to funtion to do this
//delay: the delay to close
function close_action(action, type, delay) {
  //if( action === undefined ) { action = local.current.action; } //default
  var func = switcher('close_'+action, type);
  if(func) { window[ func ](delay); }
}

//helperfunction switcher shortcut for send_input
//inputkey is optional
function send_input(id, type, action, inputkey) {
  //if(inputkey !== null && inputkey !== undefined) { local.current.inputkey=inputkey; }
  var func = switcher('send_'+action, type);
  if(func) { window[ func ](id, type, inputkey, action); }
}

//helperfunction switcher shortcut for after_send
//inputkey is optional
//pass = {id, type, action, key, where}
function after_send(info,params,pass) {
  try {
    //first of all clear cache with data of this type
    clear_data(pass.type);
    //optional parameter inputkey
    //if(inputkey === null || inputkey === undefined) { inputkey=local.current.inputkey; }
    //find function
    if( !pass.key ) { pass.key=0; } //via popup
    var funcname = 'after_send_'+pass.action+'_'+pass.key;
    var func = switcher( funcname, pass.type); 
    //error function not found
    if( !func ) { throw '[0] no function found for: type = '+pass.type+' - function should have this name: '+funcname+'_<type>'; }
    //call function
    window[ func ](info, params, pass.id, pass.type, pass.where);
  } catch(err) { msg('[after_send]:'+err); errnum=1; }
}

//helperfunction switcher shortcut to fill a list or something else
//loadtype: what to fill, filltype: how to fill
function fill(loadtype,filltype) {
  try {
    //if(loadtype === undefined || loadtype === null) { loadtype = local.current.loadtype; }
    //if(filltype === undefined || filltype === null) { filltype = local.current.filltype[loadtype]; }
    var func = switcher('fill_'+filltype, loadtype);
    if( !func ) { throw '[0] no function found for: '+loadtype; } //error if not found
    return func;
  } catch(err) { msg('[fill]:'+err); errnum=1; }  
}

//helperfunction fill local information for load_data and call the function
function load_data(filltype, fillwhere, loadtype) {
  try {
    //local.current.filltype[loadtype] = filltype;  //how to fill (list,select)
    //local.current.fillwhere[loadtype] = fillwhere;  //where to fill (HTML-Element)
    //local.current.loadtype = loadtype; //check later if data is still needed

    //call right function
    var func = switcher('load_data', loadtype);
    if( !func ) { throw '[0] no function found for: '+loadtype; } //error if not found
    window[ func ](filltype, fillwhere, loadtype); 
  } catch(err) { msg('[load_data]:'+err); errnum=1; }  
}

//keynavigation in edit form
function input_keydown(id, type, action, what) {
  //get key
  //9: tab
  //13: enter
  //16: shift
  //27: esc
  //38/40: up/down
  var keynum;
  if(what) {
    if(window.event) { keynum = what.keyCode; }
    else if(what.which) { keynum = what.which; }   
  }
  //for every key check if there is a function
  var func = switcher('input_keydown_'+keynum, type);
  if(func) { window[ func ](id, type, action); }
}

//helperfunction release modifiers like shift
function input_keyup(id, type, action, what) {
  //get key
  var keynum;
  if(what) {
    if(window.event) { keynum = what.keyCode; }
    else if(what.which) { keynum = what.which; }   
  }
  var func = switcher('input_keyup_'+keynum, type);
  if(func) { window[ func ](id, type, action); }
}

//helperfunction prepare a param array for get_function writeone<type>
//type is optional
function get_function_writeone(type) {
  //if(type === undefined || type === null) { type = local.current.type; }
  var nparams = {};
  nparams['location'] = 'php/write_one.php';
  nparams['function'] = 'write_one_'+type;
  return nparams;
}

//helper to put loaded list where it belongs
//w = where
function put_list( value, w ) {
  if( !w ) { return false; }

  //insert value
  w.insert({ after: value });
  //update events
  eventloader( w.up() ); 
  //remove placeholder
  w.remove();
}

//////////
//UPDATER
////
//update information on screen at the end of an action

//cleaner removes specific HTML Elements
//used to remove deleted items from screen
//id can be array
function cleaner(id, type) {
  try {
    //errors
    if(id === undefined || id === null) { throw '[0] first parameter: id is null.'; }
    if(type === undefined || type === null) { throw '[1] second parameter: type is null.'; }
    //prepare array for return
    var ret = [];
    //if isn't array make it array
    if(typeof id !== 'object') { nid=[id]; }
    else { nid=id; }
    //for each id
    for( var i=0; i<nid.length; i++ ) {

      //this location was deleted: reload!
      if(here[type+'id'] == nid[i]) { location.reload(); break; }

      //remove elements that contain deleted elements
      var remove = $$('.'+type+'_'+nid[i]+'_remove');
      ret.push(remove.length != 0); //true if something will be deleted
      remove.each( function(item) { item.remove(); } )

      //delete local cache for that type
      clear_data(type);

    }
    //return. if function is called with array give back array.
    if(ret.length == 1) { return ret[0] } else { return ret }
  } catch(err) { msg('[cleaner]:'+err); errnum=1; }
}

//appender adds an HTML Element to a location
//where: html element
//locate: location relative to where (top, bottom, after, before);
function appender(input, type, where, dlocate) {
  try {
    //check for missing params
    if( !Object.isElement(where) ) { debug.warning('"where" is not defined (correctly). should be a html element.');  }
    if( !type ) { debug.warning('"type" is not defined.'); }
    //some optional params
    //if(dwhere != null) { var where = dwhere; }
    //else { var where = local.current.where; }
    if(dlocate != null) { locate = dlocate; }
    else { var locate = 'before'; }
    //append
    var ins = {}; ins[locate]=input;
    var nwhere = Element.insert(where, ins);
    //insert events
    if(locate == 'after' || locate == 'before') { nwhere = nwhere.parentNode; }
    if(typeof eventloader == 'function') { eventloader(nwhere); }
    //delete local cache (will be reloaded)
    clear_data(type);
  } catch(err) { msg('[appender]:'+err); errnum=1; }
}


//////////
//CONSTRUCTOR
////
//this functions are called to initialize an action in the GUI

//Create a dropdown
//type: type of the element: word, register, wordclass, group, ...
//id: id of the element
//content: array of content for the dropdown: ['addword','edit','list']
//where is only for editing. tells the html element where the input will show up
// lists will be loaded in another function (load_data_type(), where type is the type of the param) which will append it after the htmlelement with id "dropdown_list_loading" and then remove this.
//HTML Class! type_id will be edited. type_id_remove will be removed when deleted
function g_dropdown(type, id, cont, where, atelement) {
  try {
    //save this to local
    //local.current.type=type;
    //local.current.id=id;
    //local.current.where=where;
    //print links
    var li='';
    var list=0;
    for(var i=0; i<cont.length; i++) {
      if(cont[i]=='hr') { //make a line
        li+= '<hr>';
      } else if(cont[i]=='list') { //make a list
        //holder to set in list later
        li += '<li id="dropdown_list_loading">'+la.loading+'</li>'; 
        list=1; 
      } else { //link or action
        var func=switcher('link_'+cont[i], type);
        if (func) { li += window[ func ](id, type); } //link
        else { //outsource
          var func = switcher('dropdown_action', type); //call dropdown_action_<type>(content);
          if( !func ) { throw '[0] no function found for: '+type; }
          else { //action
            li += window[ func ]( cont[i], id, type, where);
          }
        }
      }
    }
    //create and show dropdown
    if(atelement!=null) { load_dropdown(atelement, '<ul>'+li+'</ul>') }
    else { load_dropdown_mouse('<ul>'+li+'</ul>'); }
    //load content of list extern and add it to the holder
    if(list==1) {
      //call load_data_<type>(type)
      //set local.current.loadtype[type]='list';
      //set local.current.loadwhere[type]=$('dropdown_list_loading')
      load_data('list', $('dropdown_list_loading'), type);
    }
  } catch(err) { msg('[g_dropdownn]:'+err); errnum=1; }
}
//executes an action
//basicly just saves the parameters so that the function for this action can handle it.
function do_action(id, type, action, where) {
  try {
    //hide dropdown
    hide_dropdown(0);
    //call the destructor of this action for cleanup
    close_action(action, type);
    //save this to local
    //if( type !== false ) { local.current.type=type; }
    //if( id !== false ) { local.current.id=id; }
    //local.current.action=action;
    //if( where !== undefined) { //this may already be defined before. only change if wanted
    //  local.current.where=where;
    //}
    //find right function
    var func = switcher('action_'+action, type);
    if( !func ) { throw '[0] no function found for: '+action; }
    window[ func ]( id, type, where );
  } catch(err) { msg('[do_action]:'+err+'<br> tried to call: "'+func+'"'); errnum=1; }
}

//////////
//LOAD DATA
////

//load_data functions, get data from database and go on to save the information localy
//it's called via load_data(loadtype)
//type is the type of the content that is loaded and also the index for the process
//filltype is the type how the data will be filled (list, select)
//fillwhere is the HTML element which will be replaced by the data.
//because loading data is asynchronious the index loadtype is necessary. Like this different datatype can be loaded and filled side by side

//loads list information of type in a generic way
//may fit in most cases
function load_data_generic(filltype, fillwhere, type) {
  try{
    //find function
    var tfill = fill(type, filltype); //function to call fill_<filltype>_<type>
    if(tfill) { 
      if(!local.data[type]) { 
        var params=Object.clone( get_here() );
        params.count = 1;
        params[type+'id']=null;
        //load data from server
        req('get_'+type,params, function(i,p,s) { 
            local.data[ s[1] ]=i; //save local
            window[ s[2] ]( s[0], s[1] ); //call fill_..(loadtype);
        }, [fillwhere, type, tfill] ); 
      } else { window[ tfill ](fillwhere, type); } //use local data
    } else { 
      //If no function was found for this
      fillwhere.update(la.err_102);
    }
  } catch(err) { msg('[load_data_generic]:'+err); }
}

//load list information for groups
function load_data_group(filltype, fillwhere) {
  try{
    var tfill = fill('group', filltype); //function to fill the data
    if(!local.data.group) { 
      req('get_reg_info', here, function(i,p,s) {
        local.data.group=i;
        window[ s[0] ](s[1], 'group');
      }, [tfill, fillwhere]);
    } else { window[ tfill ](fillwhere, 'group'); }
  } catch(err) { msg('[load_data_group]:'+err); }
  return false;
} 

//wordclasses don't have to load just redirect
function load_data_wordclass(filltype, fillwhere) { window[ fill('wordclass',filltype) ](fillwhere, 'wordclass'); }

//columns have to load every time again but not from server. redirect
function load_data_column(filltype, fillwhere) { window[ fill('column',filltype) ](fillwhere, 'column'); }

//////////
//DROPDOWN ACTION

//generic link for an action in dropdown
function dropdown_action_generic(action, id, type, where) {
  try {  
    if( !Object.isElement(where) ) { debug.warning('where is not defined as an Element'); }
    var w = $(where).identify();
    return '<li class="link menulink action_'+action+'" onclick="do_action(\''+id+'\',\''+type+'\',\''+action+'\',$(\''+w+'\'));">'+la[action]+'</li>';
  } catch(err) { msg('[dropdown_action_generic]:'+err); errnum=1; }
}

//////////
//FILL LIST
////
//fill up a list generic
function fill_list_generic(fillwhere, type) {
  var val = '';
  var lld = local.data[type];
  for(var j = 0; j < lld['count']; j++) {
    var func = switcher('link_goto', type);
	  val += window[ func ](lld['id'][j], type, lld['name'][j]);
  } 
  //insert the list
  put_list( val, fillwhere );
  position_dropdown();
}

function fill_list_group(fillwhere) {
  //create the list
  var val='';
  var func = switcher('link_goto', 'group')
  for(var i=1;i<=local.data.group.groupcount;i++) {
	  val+= window[ func ](i, 'group', la['group']+" "+i);
  }
  val+= window[ func ]('af', 'group', la['af']);
  val+= window[ func ]('ar', 'group', la['ar']);
  //insert the list
  put_list( val, fillwhere );
  position_dropdown();
}

function fill_list_column(fillwhere) { 
  var val='';
  // walk through columns
  $$('th[id$="_head"]').each( function(item) {
    if( item.getStyle('display') == 'none' ) {
      var column = item.identify().slice(0, -5);
      if(column!='register' || here.registerid==null ) {
        val+="<li class='menulink link' onclick='displaycolumn(1,\""+column+"\")'>"+$(column+'_span_head').innerHTML+"</li>"; 
      }
    }
  } );
  //insert in dropdown
  //if(local.current.fillwhere['column']) {
  if(val == '') { hide_dropdown(0); } 
  else { 
    put_list( val, fillwhere);
    position_dropdown();
  }
  //} 
}

function fill_list_wordclass(fillwhere, wordid) {
  var val='';
  for(var j=0;j<6; j++) {
    val += "<li class='menulink link' onclick='do_action(\""+j+"\",\"wordclass\",\"goto\")'>"+la.classname[j]+"</li>";
  } 

  put_list( val, fillwhere );
  position_dropdown();
}

//////////
//ACTIONS

//--------
//GOTO

//goto somewhere knowing type and id
function action_goto_generic(id, type, params) {
  //shutter
  do_shutter(1);
  //remove wordid //usually useless
  if( type != 'word' ) {
    clear_here('wordid');
  }
  //goto location
  if( params === undefined ) {
    var params = {};
    params[type+'id'] = id;
  }
  location.href = path(params);
}

//for keyoptions
action_force_goto_keyoption =
action_goto_keyoption = function( id ) {
  //shutter
  do_shutter(1);

  //different with verb
  if(id == 'verb') {
    clear_here(['formid','personid','wordid']);
  } 
  var param = {keyoption: id};

  //load page
  location.href=path(param);
}

function action_goto_word(id) { //remove verblist from path
  update_here({keyoption: 'show'}); //load show
  clear_here(['formid', 'personid', 'wordclassid', 'tagid', 'groupid', 'withoutid', 'searchid', 'saveid']); //20120408 - fix : compare similar words from whole register
  action_goto_generic(id, 'word');
}

function action_goto_verb( id, type) {
  //shutter
  do_shutter(1);

  //clear other ids
  clear_here(['wordid','personid','formid']);

  //set new id and keyoption to verb
  type = type=='verb' ? 'word': type;
  var params= {keyoption: 'verb'}; //load verb
  params[type+'id'] = id;

  //load
  location.href = path(params);
}

function action_goto_person(id) { action_goto_verb( id, 'person'); }
function action_goto_form(id) { action_goto_verb( id, 'form'); }

function action_goto_search( id ) {
  //clear useless
  clear_here(['queryid','keyoption']);
  //also update searchtext
  update_here({ searchtext: 'like: '+id});
  action_goto_generic(id, 'search');
}

//force goto somewhere knowing type and id
function action_force_goto_generic(id, type) {
  //shutter
  do_shutter(1);

  var params= {};
  params[type+'id'] = id;
  location.href=path(1,params); //remove all except register
}

function action_force_goto_user() {
  //shutter
  do_shutter(1);

  location.href = path(0);
}

//--------
//DELETE

//delete something
//deletes a type calling 'delete_<type>' with the param '<type>id'=id
//removing elements with class '<type>_<id>_remove'
//asking 'ask<type>del'
function action_delete_generic(id, type) {
  try {
    var params= { currenttype: type}; //pass it because async
    params[type+'id'] = firstelement(id);
    rvar.create('deletesomething','delete_'+type, params, function(info,params) {
      //clean
      cleaner(params[params.currenttype+'id'], params.currenttype);
      //notify
      if(la['info_'+params.currenttype+'_deleted']) {
        do_info( la['info_'+params.currenttype+'_deleted'] );
      }
    }); 
    ask(la['ask'+type+'del'],'rvar.deletesomething.sendreq()');  
  } catch(err) { msg('[action_delete_generic]:'+err); errnum=1; }
}

//remove tag from word
function action_delete_tag(id) {
  try {  
    rvar.create('deltag','delete_tag',{wordid:id[0], tagid:id[1]},function(i,params) {
      cleaner(params.wordid+'_'+params.tagid, 'tag');
    });
    ask(la['asktagdel'],'rvar.deltag.sendreq()');
  } catch(err) { msg('[action_removefrom_tag]:'+err); errnum=1; }
}

//--------
//EDIT

//edit_generic
action_rename_generic =
action_edit_generic = function(id, type, where, input, content) { //input and content are optional
  if( !id ) { debug.warning('id is not defined in action_edit_generic'); }
  if( !type ) { debug.warning('type is not defined in action_edit_generic'); }
  hide_dropdown(); //hide that
  //get current content
  if( !input ) {
    if( content == null ) { content = $$('.'+type+'_'+firstelement(id))[0].innerHTML; }
    input='<input id="input_edit" class="close_edit input_'+type+'" onkeyup="input_keyup(['+id+'], \''+type+'\', \'edit\', event)" onkeydown="input_keydown(['+id+'], \''+type+'\', \'edit\', event)" onblur="close_action(\'edit\',\''+type+'\',200)" type="text" name="new'+type+'" value="'+content+'"></input>';
  }
  //hide element and show input instead and focus it
  if( !Object.isElement(where) ) { debug.warning('where is not defined as an Element in action_edit_generic'); }
  Element.hide(where).insert({before: input});
  $$('#input_edit')[0].focus();
}

//edit a verb in the verbtable
function action_edit_kword(id, type, where) {
  if( !id ) { debug.warning('id is not defined in action_edit_kword'); }
  if( !type ) { debug.warning('type is not defined in action_edit_kword'); }
  var content= $$('.'+type+'_v'+id[0]+'_p'+id[1]+'_f'+id[2])[0].innerHTML;
  action_edit_generic(id, type, where, false,content);
}

//edit wordclass
function action_edit_wordclass(id, type, where) {
  //get current content
  content=$$('.wordclass_'+firstelement(id))[0].innerHTML;
  //write input
  //close_input has problems without this span here
  var input='<span class="close_edit"><select id="input_edit" onkeyup="input_keyup(['+id+'], \'wordclass\', \'edit\', event)" onkeydown="input_keydown(['+id+'], \'wordclass\', \'edit\', event)" name="newwordclass" size=1 onblur="close_action(\'edit\',\''+type+'\',200)" onchange="send_input(['+id+'], \'wordclass\',\'edit\',13)">';
  //when changing value it will simulate pressing enter
  for(var i=0; i<6; i++) {
    input += '<option value='+i+' '; 
    if(la.classname[i]==content) { input += 'selected' }
    input += '>'+la.classname[i]+'</option>';
  }
  input += '</select></span>';
  action_edit_generic(id, type, where, input);
}

//edit group
function action_edit_group(id, type, where) {
  if( !id ) { debug.warning('id is not defined in action_edit_group'); }
  if( !type ) { debug.warning('type is not defined in action_edit_group'); }
  //get current content
  content=$$('.'+type+'_'+firstelement(id))[0].innerHTML;
  //create select
  var input='<span class="close_edit"><select id="input_edit" name="newgroup" size=1 onkeyup="input_keyup(\''+id+'\', \'group\', \'edit\', event)" onkeydown="input_keydown(\''+id+'\', \'group\', \'edit\', event)" onblur="close_action(\'edit\',\''+type+'\',200)" onchange="send_input(\''+id+'\',\'group\',\'edit\',13)">';
  //af and ar are special
  if(content != la.a_ar && content != la.a_af) {
    input+='<option value="'+content+'" selected>'+content+'</option>';
  }
  //if archive
  if(content==la.a_ar) { input+='<option value="ar">'+la.a_ar+'</option>'; }
  //give option af
  input+='<option value="af">'+la.a_af+'</option>';
  //give option group 1
  if(content!=1) { input+='<option value="1">1</option>'; }

  input+='</select></span>';
  action_edit_generic(id, type, where, input);
}

//edit wordclass directly with a dropdown
function action_speededit_wordclass(id, type, value) {
  if( !id ) { debug.warning('id is not defined in action_speededit_wordclass'); }
  if( !type ) { debug.warning('type is not defined in action_speededit_wordclass'); }
  hide_dropdown();
  //send request and update
  req('edit_word',{wordid:firstelement(id), newwordclass:value}, after_send, {id:id,action:'edit',type:'wordclass',key:13});
}

//--------
//ADD

//add: opens input to add or create something
function action_add_generic(id, type, where) {
  if( !id ) { debug.warning('id is not defined in action_add_generic'); }
  if( !type ) { debug.warning('type is not defined in action_add_generic'); }
  if( !where ) { debug.warning('where is not defined in action_add_generic'); }
  hide_dropdown(); //hide that
  //if where is popup make a popup with the input
  if(where == 'popup') {
    var params = new formparam('new'+type);  //new form with title
    params.addinput('new'+type,'name','',0);  //input field
    //prepare request - with registerid
    rvar.create('addsomething','create_'+type,{registerid: here.registerid}, after_send, {action:'add',id:id, type:type});
    //make and show a form
    request_form('addsomething',params);

  } else {  //else create a input at where
    input='<input class="close_add" id="input_add" onkeyup="input_keyup(\''+id+'\', \''+type+'\', \'add\', event)" onkeydown="input_keydown(\''+id+'\', \''+type+'\', \'add\', event)" onblur="close_action(\'add\',\''+type+'\',200)" type="text" name="new'+type+'"></input>';
    //hide element and show input instead and focus it
    Element.hide(where).insert({before: input});
    $$('#input_add')[0].focus();
  }
}

//--------
//MORE/LESS GROUPS

//add one group to register
function action_more_group( id, type, inc ) {
  if( !id ) { debug.warning('id is not defined in action_more_group'); }
  if( !inc ) { inc = '++'; } // 05.04.2012 - fix : inc may be "".
  req('edit_register',{registerid: id, newgroupcount:inc}, function(info,params) {
    var param = { location:'php/list.php',
                  'function': 'list_group',
                  'json': 1,
                  'parameters': Object.toJSON([params.registerid, you.hints]) };
    req('get_function',param,function(info) {
      var groupc = $('group_content');
      if ( !groupc ) { throw 'HTML Element with id group_content not found.'; }
      else {
        groupc.insert({after : info.output});
        eventloader( groupc.up() ); //refresh events
        groupc.remove();
        refresh_subcount('group'); //refresh count
      }
    });
  })
}
//same for removing group
function action_less_group(i, t) {
  action_more_group(i, t, '--');
}

//--------
//PAGES

//directly to show page
function action_show_generic(i, t) {
  update_here( { page:'show' } );
  do_action(i, t, 'goto');
}

//directly to verb page
function action_verb_generic(i, t) {
  update_here( { page:'verb' } );
  action_goto_generic(i, t);
}

//--------
//OTHER

//make a query here
function action_query_generic(id, type) {
  if( !id ) { debug.warning('id is not defined in action_query_generic'); }
  if( !type ) { debug.warning('type is not defined in action_query_generic'); }
  do_shutter(1);

  //if id is 'wordform'
  //this will update id with wordids of form if id is 'wordform'
  //it will show an error and return 0 if no word is selected
  //it will return false and nothing else if id isn't 'wordform'
  if(here.page == 'show' || here.page == 'add') { id = 'wordform'; } // fix - 04.04.2012
  var iswordform = formfield2id(id , 'wordid[]');
  if( iswordform === false ) { //no wordform: just query here
    var params = {allmarked: 1};
    params[type+'id']=firstelement(id);   
  } else if( iswordform === null ) { return false; }  //wordform is empty 
  else {                                          //wordform has checked words
    //check if all words are checked with the allmarked checkbock //fix - 04.04.2012
    if( $('check_allmarked').checked ) {
      params = {allmarked: 1}; // query all if checked
    } else {
      var params = {'wordid[]': iswordform}; // query selected otherwise
    }
  }

  //prepare request
  rvar.create('cract','create_active', here, function(info) {
    document.location.href = path(2,{ queryid: info.savedid });
  }); 
  //append params from above and send
  rvar.cract.sendreq(params);
}

//load location to add a word
function action_addword_generic(id, type) {
  if( !id ) { debug.warning('id is not defined in action_addword_generic'); }
  if( !type ) { debug.warning('type is not defined in action_addword_generic'); }
  var params = {keyoption: 'add'};
  params[ type+'id' ] = id;
  location.href=path(params);
}

function action_export_generic(id, type) {
  location.href=path({keyoption: 'export'});
}

//////////
//INPUT KEYNAVIGATION

//13 Enter: submit with enter generic
function input_keydown_13_generic(i,t,action) { send_input(i,t,action,13); }
//9 tab will do the same here
function input_keydown_9_generic(i,t,action) { send_input(i,t,action,9); } 
  
//27 esc: will close
function input_keydown_27_generic(i,t,action) { close_input_generic(i,t,action,27); }

//16 shift: modifier
function input_keydown_16_generic() { local.current.keymodshift=true; }
function input_keyup_16_generic() { local.current.keymodshift=false; }

//////////
//SEND EDIT

function send_edit_generic(id, type, key) {
  try {
    var input=$('input_edit'); //this field should contain the new name
    if(input) {
      var params = {};
      params[type+'id'] = id;
      params['new'+type] = input.value;
      req('edit_'+type,params, after_send, {id:id,type:type,key:key,action:'edit'});
    }  
  } catch(err) { msg('[send_edit_generic]:'+err); errnum=1; }
}

//Send Edit for word
send_edit_wordfore = 
send_edit_wordfirst =
send_edit_group =
send_edit_sentence =
send_edit_wordclass = function(id, type, key) {
  var input=$('input_edit'); //this field should contain the new name
  if(input) {

    //validate string
    if( type == 'wordfirst' || type == 'wordfore' ) {
      if( validstr( input.value ) ) { do_info( la.err_invalidsyntax ); return false; }
      else { close_info(); }
    }

    var params = {wordid:firstelement(id)};
    params['new'+type] = input.value;
    req('edit_word',params, after_send, {id:id, type:type, action:'edit', key:key});
  }
}

//edit verb (kword)
function send_edit_kword(id, type, key) {
  if( !id ) { debug.warning('id is not defined in send_edit_generic'); }
  try {
    var input=$('input_edit'); //this field should contain the new name
    if(input) {
      req('add_verb',{wordid:id[0], personid:id[1], formid:id[2], newkword:input.value}, after_send, {id:id,type:type,action:'edit', key:key});
    }
  } catch(err) { msg('[send_edit_kword]:'+err); errnum=1; }
}

//edit grouplock
function send_edit_grouplock(id, type, key) {
  if( !id ) { debug.warning('id is not defined in send_edit_grouplock'); }
  var input=$('input_edit'); //this field should contain the new name
  if(input) {
    //define array new grouplock [null, ..., value];
    var index = parseFloat(id)-2;
    var newgrouplock = [];
    for(var i=0; i<index; i++) { newgrouplock.push(null); }
    newgrouplock[ index ] = input.value;
    //send request
    var params = {registerid: here.registerid, 'newgrouplock[]':newgrouplock};
    req('edit_register',params, after_send, {id:id,type:type,action:'edit',key:key});
  }
}

//////////
//AFTER SEND EDIT

//update after editing
//newname is optional.
function after_send_edit_13_generic(info, params, id, type, newname) {
  try {
    if(newname==null) { newname=params['new'+type] }
    //update all elements with this item
    $$('.'+type+'_'+id).each( function(item) { item.update(newname) } );
    //hide input and restore replaced element
    close_action('edit',type);
    //delete local data
    clear_data(type);
  } catch(err) { msg('[after_send_edit_13_generic]:'+err); errnum=1; }
}

//wordclass
function after_send_edit_13_wordclass(info,params,id,t) {
  after_send_edit_13_generic(null,null,id,t,la.classname[params.newwordclass]);
}

//register is updated with enter
function after_send_edit_13_register(info,p,id,t) {
  after_send_edit_13_generic(null,null,id,t,info.registername);
}

//verb (kword)
function after_send_edit_13_kword(info,params,id,type) {
  try {
    //update all elements with this item
    $$('.'+type+'_v'+id[0]+'_p'+id[1]+'_f'+id[2]).each( function(item) { item.update(params.newkword) } );
    //hide input and restore replaced element
    close_action('edit',type);    
    //delete local data
    clear_data(type);
  }catch(err) { msg('[end_edit_after_13_kword]:'+err); errnum=1; }
}

//submitted with tab
//tthis is optional
function after_send_edit_9_generic(info,params,id,type,tthis) {
  try {
    //first do the same as with enter
    after_send(info,params,{key:'13',action:'edit',id:id,type:type});
    //find next similar element (tab_)
    if(!tthis) { tthis=$$('.tab_'+type+'_'+id); }
    if( tthis.length != 0 ) {
      var next = $$('[class^="tab_"]');
      //check if shift is pressed
      if(local.current.keymodshift) { var mod=-1 } 
      else{ var mod=1; }
      //if element is found execute ondblclick there
      nextobj=next[next.indexOf(tthis[0])+mod];
      if(nextobj) { nextobj.ondblclick(); }
    }
    
  }catch(err) { msg('[after_send_edit_9_generic]:'+err); errnum=1; }
}

//verb (kword)
function after_send_edit_9_kword(info,params,id,type) {
  after_send_edit_9_generic(info,params,id,type,$$('.tab_'+type+'_v'+id[0]+'_p'+id[1]+'_f'+id[2]))
}

//change group (special for af and ar)
function after_send_edit_13_group(info,params,id,type) {
  if(typeof params.newgroup != 'integer') {
    var newname = la['a_'+params.newgroup];
  } else {
    var newname = params.newgroup;
  }
  after_send_edit_13_generic(info, params, id, type, newname);
}

//changed grouplock
function after_send_edit_13_grouplock(info, params, id, type) {
  after_send_edit_13_generic(info, params, id, type, params['newgrouplock[]'][parseFloat(id)-2]);
}

//////////
//SEND ADD

function send_add_generic(id, type, key) {
  try {
    var input=$('input_add'); //this field should contain the new name
    if(input) {
      var params = {};
      params['registerid'] = here.registerid; //usefull in most cases
      params['new'+type] = input.value;
      req('create_'+type ,params, after_send, {id:id,type:type, action:'add',key:key});
    }  
  } catch(err) { msg('[send_edit_generic]:'+err); errnum=1; }
}

//tag
function send_add_tag(id,type) {
  try {
    var input=$('input_add'); //this field should contain the new name
    if(input) {
      var params = {};
      params['registerid'] = here.registerid;
      params['wordid[]'] = id;
      params['newtag'] = input.value;
      req('add_tag',params, after_send, {id:id,type:type, action:'add', key:13});
    }  
  } catch(err) { msg('[send_edit_generic]:'+err); errnum=1; }
}

//////////
//AFTER SEND ADD

//update after adding tag
function after_send_add_13_tag(info,params, id) {
  try {
    //id is wordid
    //hide input and restore replaced element
    close_action('add','tag');
    //remove each tag from list, that allready is there
    for(var i=info.tagid.length-1; i>=0; i--) {
      if( $('tag_span_'+id+'_'+info.tagid[i]) ) {
        info.tagid.splice(i,1);
        info.tags.splice(i,1);
      }
    }
    if( info.tagid.length == 0 ) { return false; } //if nothing is left
    //load html element for tag
    var nparams = get_function_writeone('tag');
    nparams['json']=1;
    nparams['parameters'] = Object.toJSON([id, info.tagid, info.tags]); 
    req('get_function', nparams, function(i) { appender(i.output, 'tag', $('tag_'+id), 'top') }); //append each tag
  } catch(err) { msg('[after_send_add_13_tag]:'+err); errnum=1; }
}

//update after adding something with enter
// parameters and appenderplus are optional
function after_send_add_13_generic(info,params, id, type, parameters) {
  try {
    //load html element for tag
    var nparams = get_function_writeone(type);
    //appender default: add at bottom of list with class <type>_list
    //appenderplus = [ $$('.'+type+'_list')[0], 'bottom']; }
    if(parameters == null) {
      //simulate a list
      nparams['parameters'] = Object.toJSON([{id: info.newid, 'name':info['newname'], count:info.count}]);
      nparams['json'] = 1;
    } else {
      nparams['parameters[]'] = parameters;
    }
    req('get_function', nparams, function(i,p,s) { appender(i.output, s[0], s[1], s[2]); }, [ type, $$('.'+type+'_list')[0], 'bottom'] );
    //hide input and restore replaced element
    close_action('add', type);
  } catch(err) { msg('[after_send_add_13_generic]:'+err); errnum=1; }
}

//after a request form was sent
//parameters is optional
function after_send_add_0_generic(info,params, id, type, parameters) {
  try {
    //the same as with enter
    after_send_add_13_generic(info,params, id, type, parameters);
    close_popup();
  } catch(err) { msg('[after_send_add_0_generic]:'+err); errnum=1; }
}

//after adding a person.
function after_send_add_0_person(info,params, id, type) {
  var nparams = get_function_writeone(type);
  nparams['parameters'] = Object.toJSON([{id: info.newid, 'name':info['newname'], count:info.count}]);
  nparams['json'] = 1;
  req('get_function',nparams, function(i) { appender(i.output, type, $$('.'+type+'_list')[0], 'bottom') } );
  //close popup
  close_popup();  
}

//after adding a person.
function after_send_add_0_form(info,params, id, type) {
  after_send_add_0_person(info,params, id, type);  
}

//////////
//LINKS

//link to remove a filter
function link_all_generic(id, type) {
  return '<li class="menulink link" onclick="do_action(null,\''+ type +'\',\'goto\')">'+la['all']+'</li>';    
}

//link to show something
function link_show_generic(id, type) {
  return '<li class="menulink link" onclick="do_action(\''+id+'\',\''+type+'\',\'goto\')">'+la.show+'</li>'; 
}

//link to show group
function link_show_wordclass(id) {
  return link_show_generic( id[1], 'wordclass' );
}

//link to show group
function link_show_group(id) {
  return link_show_generic( id[1], 'group' );
}


//link to the option page
function link_options_generic(id, type) {
  return '<li class="menulink link" onclick="update_here({'+type+'id:\''+id+'\'}); do_action(\'edit\',\'keyoption\',\'goto\')">'+la['options']+'</li>';
}

//link to user settings page
function link_settings_user() {
  return '<li class="menulink link" onclick="do_action(\'settings\',\'keyoption\',\'force_goto\')">'+la['settings']+'</li>';
}

//logout
function link_logout_generic() {
  return '<li class="menulink link" onclick="do_action(\'logout\',\'keyoption\',\'force_goto\')">'+la['logout']+'</li>';
}

//import
function link_import_generic() {
  return '<li class="menulink link" onclick="do_action(\'import\',\'keyoption\',\'goto\')">'+la['import']+'</li>';
}

//create link to ad a word
function link_addword_generic() {
  return '<li class="link menulink link_addword" onclick="do_action(false,false,\'addword\')">'+la['addword']+'</li>';
}

//A Link to go to the location (sounds usefull)
function link_goto_generic(id, type, namevalue) {
  return '<li class="menulink link" onclick="do_action(\''+id+'\',\''+type+'\',\'goto\')">'+namevalue+'</li>';
}

//link to the taglist
function link_taglist_tag() {
  return '<li class="menulink link" onclick="do_action(\'taglist\',\'keyoption\',\'goto\')">'+la['taglist']+'</li>';
}

//link to words without tag or save or whatever
function link_without_generic(id,type) {
  return '<li class="menulink link" onclick="do_action(\''+type+'\',\'without\',\'goto\')">'+la['without'+type]+'</li>';
}

//link to verbtable
function link_verbtable_word(id) {
  return '<li class="menulink link" onclick="do_action(\''+id+'\',\'verb\',\'goto\')">'+la.verb+'</li>';
}

//links to speededit wordclass
function link_list_edit_wordclass(id) {
  var val='';
  for(var j=0;j<6; j++) {
    val += "<li class='menulink link' onclick='action_speededit_wordclass(["+id+"],\"wordclass\","+j+")'>"+la.classname[j]+"</li>";
  } 
  return val;
}

//link to help
function link_help(anchor, force, text) {
  text = text || '';  
  if( you.hints==1 || force==1) {
    return '<span class="link helplink h_'+anchor+'" onmouseover="help_load(\''+anchor+'\')" onclick="help_toggle();">'+text+'</span>';
  }
}

//////////
//CLOSE

//Closes an editable make everything back to normal.
//delay and where are optional.
function close_input_generic(delay, action) {
  var input = $$('.close_'+action)[0];
  //quit if there is no object to remove
  if(!input) { return false; }
  //
  if(delay!=null) { //undefined or 0
    local.current.close_input_delay = setTimeout(function() { close_input_generic(null, action) }, delay);
  } else {
    //define element
    where = input.next();
    //remove element
    Element.show(where).previous().addClassName('input_closed').remove();
    //stop timeout
    clearTimeout(local.current.close_input_delay);
  }
}

//edit is an action. close_action() will call this 
function close_edit_generic(delay) { close_input_generic(delay, 'edit'); }
function close_add_generic(delay) { close_input_generic(delay, 'add'); }

//////////
//CLEAR

//clear data from local.data
function clear_data(type) {
  if( type === undefined ) {
    delete local.data;
    local.data = {};
  } else {
    if( typeof type == 'string' ) {
      type = [ type ];
    } else if ( typeof type != 'object' ) {
      throw '[clear_data]: "type" must be string or array!';
    }
    for( var i=0; i<type.length; i++ ) {
      delete local.data[ type[i] ];
    }
  }
} 

//////////
// UPDATE PAGE
//////////

//helper
function update_information() {
  close_info();
  var func = switcher( 'update_information', here.page );
  if(func) { window[ func ](); }
}

//update the whole page without reloading it (ajax)
function update_page(page) {
  //update here
  update_here({page: page});

  //don't search
  if(here.page != 'search')  {
    update_here( {searchid : null} );
  }

  //update hash
  update_hash();
  //update navigator
  update_navigator();

  //update info
  update_information();

  //update current page
  var func2 = switcher( 'update', page );
  if(func2) { window[ func2 ](); }

  //change class of content div (changing background color)
  $('content').className = 'page_'+page;
}

//load navigator with current information in here
function update_navigator() {
  var nparams = {};
  nparams['location'] = 'php/menu.php';
  nparams['function'] = 'navigator';
  //pass here as json
  nparams['parameters'] = '['+ Object.toJSON( here ) +']';
  nparams['json'] = 1;
  //load navigator and append it
  req('get_function', nparams, function(info) {
    eventloader( $('headerpath').update( info.output ) );
  });
}

//////////
// FORM
//////////

//Loads a form dialog before function. (more flexible)
//reqvar: name of a prepared request in rvar
//params: a formparam element
function request_form(reqvar,params) {
  var errnum=0;
  var ret="<form id='askform' action='#' onsubmit='if(checkrform()) { return rvar."+reqvar+".sendreq($(\"askform\").serialize(true)); } else { return false; }'>";
    ret+='<span class="titel reqformtitel">'+la[params.title]+":</span>";
    if(params.error) {//show error on top
      ret+='<div class="reqformerr">'+la[params.error]+"</div>";
    }
    ret+='<ul class="inputlist">';
      try {
        for(var i in params.input) {
          var n = params.input[i] //shortcut
          var ttype=n.type?n.type:'text';
          var tid=n.id?n.id:n.name;
          var tneed=n.needed==1?' class="form_need" ':'';
          ret+="<li><label for='"+tid+"'>"+la[n.label]+":</label><input type='"+ttype+"' name='"+n.name+"' id='"+tid+"' value='"+n.value+"' "+tneed+"></li>";
        }
      }catch(err) { msg('[request_form_0]:'+err); errnum=1; }
      try {
        for(var l in params.select) {
          var m = params.select[l]; //shortcut
          var tid=m.id?m.id:name;
          ret+="<li><label for='"+tid+"'>"+la[m.label]+":</label><select name='"+m.name+"' size='1' id='"+tid+"'>";
          for(var j in m.option) {
            var sel=j==m.selected?"selected='selected'":"";
            ret+="<option value='"+j+"' "+sel+">"+m.option[j]+"</option>";
          }
          ret+="</select></li>";
        }
      }catch(err) { msg('[request_form_1]:'+err); errnum=2; }
    ret+='<ul class="inputlist">';
    ret+="<input type='submit' value='"+la[params.submittext]+"'>";
    ret+="<input type='button' value='"+la[params.canceltext]+"' onclick='close_popup()'/>";  
  ret+='</form>';
  if(errnum==0) {
    //show in popup div
    $('popup').update( ret );
    do_popup();
    //focus first input
    $('askform')[tid].focus();
  }
}
//checks a generated request form
function checkrform() {
  try { 
    var ret=true;
    var i=0;
    var npts=$$('input.form_need');
    while(input=npts[i]) {  //walks through all inputs that are necessary
      if(input.value=='') { 
        input.focus();
        ret=false;
        break; 
      }
      i++;
    }
    return ret;
  } catch (err) { msg('[checkrform]:'+err); return false; }
}

//////////
//DISPLAY TOOLS
//////////

//////////
// SHUTTER
function do_shutter( loadbar ) {
  $('shutter').clonePosition( $('content') , {setTop: false} ).show();
  $('loadbar')[(loadbar?'show':'hide')]();
}

function close_shutter() {
  $('shutter').hide();  
}

//////////
// INLINE-INFO
// show notification on top
function do_info(msg) {
  $('info').show().down('span').update(msg);
}

// @msg : only close notification if it displays this message.
//      : empty msg closes all notifications
function close_info(msg) {
  if( !msg || $('info').down('span').innerHTML == msg ) {
    $('info').hide().down('span').update();
  }
}

//////////
// POPUP

//opens the popup
function do_popup() {
  var popup = $('popup');
  //shutter
  do_shutter();
  //show & size
  /*popup.setStyle({ 
    //display : 'block',
    height : 'auto',
    width : 'auto'
  });*/
  popup.removeClassName('hide');
  //if popup is to big resize it
  var height = document.body.clientHeight;
  var width = document.body.clientWidth;
  var popheight = popup.clientHeight;
  var popwidth = popup.clientWidth;
  var hlim = Math.round(height*0.8);
  if(popheight > hlim) { 
    popheight = hlim; 
    popup.setStyle({ height : hlim+"px" });
  }
  //center
  popup.setStyle({ 
    top : Math.round(height / 2 - popheight / 2)+"px",
    left : Math.round(width / 2 - popwidth / 2)+"px"
  });

}

//closes popup
function close_popup() {
  try {
    close_shutter();
    var popup = $('popup')
    popup.addClassName('hide');
    //popup.hide();    //hide
    popup.update();   //empty
  }catch(err) {}
}

//////////
// DROPDOWN

//shows a dropdown at mouse position
function load_dropdown_mouse(value) {
  hold_dropdown(); //make the dropdown stay
  var dropdown=$('dropdown');
  //fill
  dropdown.update( value );
  //position
  position_dropdown();
  //refresh hover events
  eventloader( dropdown );
  //show
  dropdown.show();
}

//shows a dropdown next to an object
function load_dropdown(underid, value) {
  hold_dropdown(); //make dropdown stay
  if( !value ) { return false; }

  //position
  var position = getposition($(underid));
  var left = position[0];
  var top = position[1] + $(underid).offsetHeight+2;
  
  var dropdown=$('dropdown');
  //fill and position
  dropdown.update( value );
  dropdown.setStyle({
    top : top+'px',
    left : left+'px'
  });
  //refresh hover events
  eventloader( dropdown );
  //show
  dropdown.show();
}

function position_dropdown() {
  var dropdown=$('dropdown');
  //getdimensions
  var view = document.viewport.getDimensions();
  var ddim = dropdown.getDimensions();
  //position (stay in window)
  var top = 0;
  if(view.height >= local.mouseY + ddim.height) { //under mouse
    top = local.mouseY;
  } else if(0 < local.mouseY - ddim.height) { //over mouse
    top = local.mouseY-ddim.height;
  }else { //20120409 - fix : on top of the page because its very large
    top = 5;
  }
  dropdown.setStyle({ 
    top : top+'px' ,
    left : (view.width<local.mouseX+ddim.width ? local.mouseX-ddim.width : local.mouseX)+'px' 
  });
}

//closes dropdown
function hide_dropdown(delay) {
  if(!$('dropdown')) { return false; } //quit if no dropdown there to hide
  if( delay==null ) { delay=400; } //default delay is 400ms
  local.close_dropdown_timer = window.setTimeout("$('dropdown').hide()", delay); //wait to hide dropdown
}

//stops closing dropdown
function hold_dropdown() {
  window.clearTimeout(local.close_dropdown_timer);
}

//////////
// STATUS

//Shows the Status
function showstatus(where) {
  var sttsbr=$('statusbar');
  if(sttsbr.innerHTML != '') { //don't show empty status
    //position
    position= getposition($(where));
    var left= position[0];
    var top= position[1] + $(where).offsetHeight+2;
    sttsbr.setStyle({
      top : top+'px',
      left : left+'px'
    })
    //show
    sttsbr.show();
  }
}
//closes statusbar
function hidestatus(delay) {
  if( delay==null ) { delay=200; }
  local.close_status_timer = window.setTimeout("$('statusbar').hide()", delay);
}
//stops closing statusbar
function holdstatus() {
  window.clearTimeout(local.close_status_timer);
}

////////
// DEV
////////

//multiple checking
function multicheck(id) {
  if(local.mouseisdown==1) {
    checkthisbox(id);
  }
}
