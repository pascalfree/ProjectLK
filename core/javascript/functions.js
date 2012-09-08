//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: functions.js
//core
//description: global functions for all themes.
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////

//////////
//CONTENT:
//  Global
//  GUI Interaction
//  Hash
//  Here
//  Database
//  Check String
//  Utility
//////////

//////////
// GLOBAL
//////////

//Store local Information
var local = {};

//Initialization onLoad
function init() {
  if(here.page === 'query') { qe.start(); } //starts engine for queries
}
document.observe("dom:loaded", init);

//////////
// GUI INTERACTION
//////////
//gui can decide how to to show these outputs

//Sends an errorstring to the gui or alerts.
function errg(out) {
  if( typeof gui_error === 'function' ) { gui_error(out); }
  else { alert(out); }
}

function reqerr(info) {
  if( typeof gui_request_error === 'function' ) { gui_request_error( info ); }
  else { errg(info.errnum+': '+info.errname+ ' - '+info.lastquery); }
}

//Called when a request is started. num is the statuscode
//see function req
function ajaxcatch(num) {
  if( typeof gui_ajaxcatch === 'function' ) { gui_ajaxcatch(num); }
}

//////////
// HASH
//////////
// functions to deal with url hash

//check for changes in the url hash
function onHashChange() {
  //if browser supports onhashchange listen to event
  var dmode = document.documentMode;
  if( 'onhashchange' in window && ( dmode === undefined || dmode > 7 ) ) {
    window.onhashchange = onHashChange;
  } else { //else recheck in a second
    window.setTimeout('onHashChange()', 1000);
  }

  //first load hash if empty
  if(local.hash === undefined) {
    update_hash(); 
  }
  //if changed
  if( local.hash !== location.hash && location.hash !=="" ) { // hash has changed and isn't empty
    local.hash = location.hash;
    load_hash();
  }
  //simulate backlink witch makes hash empty:
  //local.hash_original is the hash of the page which was loaded freshly
  if( location.hash === "" && local.hash !== local.hash_original ) {
    local.hash = local.hash_original;
    load_hash();
  }
}

//loads the hash to here
function load_hash() {
  clear_here(['registerid','groupid','saveid','tagid','verbid','personid','formid','withoutid','page','searchid']);
  update_here( ('{'+local.hash.slice(1)+'}').evalJSON() );
  here.searchid = unescape( here.searchid ); //this is a string

  //pass to gui
  if( typeof gui_load_hash === 'function' ) { gui_load_hash(); }
}

//updates url hash from variable here
function update_hash() {
  //go through these ids in here
  var loc = ['registerid','groupid','saveid','tagid','wordclassid','wordid','personid','formid','withoutid','page'];
  var fill = {}; //object to fill
  var i;
  for( i=0; i<loc.length; i++ ) {
    if( here[loc[i]] !== null && here[loc[i]] !== undefined ) { 
      fill[loc[i]] = here[loc[i]];
    }
  }
  //escape searchid
  if( here.searchid !== null ) { 
    fill.searchid = escape( here.searchid );
  }  

  var newhash = '#'+Object.toJSON(fill).slice(1,-1); 
  //this is the fresh loaded page (witch has no hash)
  if(newhash === local.hash_original) {
    location.hash = '';
  } else if(local.hash !== undefined) { //write to url if not fresh loaded
    location.hash = newhash; 
  } else { //if url is fresh save this as original
    local.hash_original = newhash;
  }
  //on next change url will also change
  local.hash = newhash; //otherwise onHashChange will detect change and load it a second time
}

//////////
// HERE
//////////
// functions to handle the global varibale here

//updates variable here with param
function update_here( param ) {
  try {
    //walk through
    var i;
    for(i in param ) {
      //assert
      if( typeof here[i] === 'undefined' ) { throw "can't create new values in 'here'. Tried to create entry for: "+i ; }

      //do it
      here[i]=param[i];
      //special cases:
      if(i === 'tagid' && here.withoutid === 'tag') { here.withoutid = null; }
      if(i === 'saveid' && here.withoutid === 'save') { here.withoutid = null; }
      if(i === 'withoutid' && param[i] === 'tag') { here.tagid = null; }
      if(i === 'withoutid' && param[i] === 'save') { here.saveid = null; }
      if(i === 'page') { here.keyoption = param[i]; } //make page to keyoption
      if(i === 'searchid') { here.searchtext = null; } //also remove searchtext
      //clear more if registerid is changed
      if(i === 'registerid') {
        //remove all useless locations
        clear_here(['saveid','tagid','verbid','personid','formid']);
      }
      //pass to gui
      if( typeof gui_refresh_here === 'function' ) {
        gui_refresh_here(i);
      }
    }
  } catch(err) {  //catch error here
    errg('[update_here] ERROR: '+err);
  }
}

//remove some ids from "here"
function clear_here( ids ) {
  //make array
  if( typeof ids === 'string' ) { ids = [ ids ]; }
  // break if its something else
  if( typeof ids !== 'object' ) { throw '[clear_here] parameter ids must be string or array.'; }
  //set null if not undefined
  var i;
  for( i=0; i<ids.length; i++ ) {
    if(here[ ids[i] ] !== undefined ) {
      here[ ids[i] ] = null;
    }
  }
}

//////////
// DATABASE
//////////
// Tools for database interaction, espacially requests

//Requests information from DB
function req( functionname, params, action, passthrough ) {
  try {
    //Prepare parameters
    ajaxcatch(0);
    if( typeof params === 'string' ) {
      params=params.toQueryParams();
    }
    params.req = 1; //Add req = 1 to parameters
    params.response = 1; //Add response = 1 to parameters

    //check if a ante-function is defined and execute
    if( typeof action === 'object' ) {
      if( typeof action[0] === 'function' ) {
        action[0](params, passthrough);
      //} else if( typeof action[0]  === 'string' ) {
        //eval(action[0]);
      } else { throw "action must be of type 'function'"; }
    }
  }catch(err) {  //catch error here
    errg('[req] Error: '+la.err_ajax+' : '+err);
  }
  try {  //Send via prototype ajax.request
    new Ajax.Request(
      functionname+'.php', {
        parameters: params,
        onCreate: function() { ajaxcatch(1); },
        onUninitialized: function() { ajaxcatch(2); },
        onLoading: function() { ajaxcatch(3); },
        onLoaded: function() { ajaxcatch(4); },
        onInteractive: function() { ajaxcatch(5); },
        onComplete: function() { ajaxcatch(6); },
        onSuccess: function(transport) {
          ajaxcatch(7);
          //Get returned information
          var info = transport.responseText.evalJSON(true);
          //Check for errors
          if(info.errnum !== 0) { reqerr(info); }
          else {
            //Execute post-function
            if( typeof action === 'object' ) {
              if( typeof action[1] === 'function' ) {
                action[1](info,params,passthrough);
              //} else if( typeof action[1] === 'string' ) {
                //eval(action[1]);
              } else { throw "action must be of type 'function'"; }
            } else {
              if( typeof action === 'function' ) {
                action(info,params,passthrough);
              //} else if( typeof action === 'string' ) {
                //eval(action);
              } else { throw "action must be of type 'function'"; }
            }
          }	
          ajaxcatch(8);
        },
        onFailure: function() { errg("[req] Fatal Error: "+responseText); }
      } 
    );
  } catch(err2) {
    errg('[req] Error: '+la.err_ajax+' : '+err2);
  }
  return false;
}

//request variable to store request before sending
var rvar= { 
  create: function(storename, functionname, params, action, passthrough) {
    
    delete(this[storename]);
    this[storename]= { //define methods
      sendreq: sendreq, addparams: addparams
    };
    if(functionname) { this[storename].funct = functionname; }
    if( typeof params === 'object' ) { this[storename].params = Object.clone(params); }
    else { this[storename].params = params; }
    if(action) { this[storename].action = action; }
    if(passthrough) { this[storename].passthrough = passthrough; }
  }
};

//Adds parameters to a request variable (rvar.addparams(..))
function addparams(params) {
  //Get params of string
  if( typeof params === 'string' ) {
    params = params.toQueryParams();
  }   
  //Or copy array
  if(!this.params) { this.params = {}; }
  var names;
  for(names in params) {
    this.params[names] = params[names];
  }
}

//Sends request from a request variable
//-> rvar.sendreq([params])
function sendreq(params) {
  //last chance to add parameters
  if( params !== undefined ) {
    this.addparams( params );
  }
  //send
  req(this.funct,this.params,this.action, this.passthrough);
  return false;
}

//////////
// CHECK STRING
//////////
//Functions to validate syntax words before sending.

//validates the syntax of an entry
//returns an error id:
  // 1: opening and closing brackets not equal
  // 2: brackets in brackets
  // 3: only opening brackets or closing ones
  // 4-6: the same for square brackets
function validstr(str) {
  err=0;
  //check round brackets ( )
  br_open=str.match(/\(/g);
  br_close=str.match(/\)/g);
  br_pair=str.match(/\([^\(\)]*\)/g);
  if(br_open!==null && br_close!==null && br_pair!==null) {
    if(br_open.length!==br_close.length) { err=1; }
    else if(br_open.length!==br_pair.length) { err=2; }
  } else if(br_open!==null || br_close!==null || br_pair!==null) { err=3; }
  //check squared bracket [ ]
  br_open=str.match(/\[/g);
  br_close=str.match(/\]/g);
  br_pair=str.match(/\[[^\[\]]*\]/g);
  if(br_open!==null && br_close!==null && br_pair!==null) {
    if(br_open.length!==br_close.length) { err=4; }
    else if(br_open.length!==br_pair.length) { err=5; }
  } else if(br_open!==null || br_close!==null || br_pair!==null) { err=6; }
  return err; 
}

//finds the position of an error in syntax of an entry
function errorpos(str) {
  i=0; maxl=str.length;
  br=0; sbr=0; 
  err=-1;
  while(i<maxl) {
    if(str[i]==='(') { br++; }
    if(br>1) { err=i; }
    if(str[i]===')') { br--; }
    if(br<0) { err=i; }
    if(str[i]==='[') { sbr++; }
    if(sbr>1) { err=i; }
    if(str[i]===']') { sbr--; }
    if(sbr<0) { err=i; }
    if(err!==-1) { break; }
    i++;
  }
  return err;
}

//////////
// UTILITY
//////////

//creates the current path like the php function
//level: how deep to write the path from 'here', optional
//change: change specific value , optional
function path(setlevel, params) {
  //define the content of the path in right order.
  var pathparams={type:['','registerid','groupid','saveid', 'tagid','wordclassid','wordid','personid','formid','searchid','queryid','keyoption'], 
        prefix:['','','','save/','tag/','wordclass/','word/','person/','form/','search/','query/','']};

  var level = pathparams.type.length;
  var change=0; //no changes
  //read arguments //typeof null == 'object' would be true
  if( typeof setlevel === 'object' && setlevel !== undefined) { change = setlevel; }
  else if(setlevel !== undefined) { level = setlevel; }
  if( typeof params === 'object' && params !== undefined) { change = params; }

  var pathval='';
  //add username
  if( 0 < pathparams.type.length && you.id !== null) {
    pathval += you['name'] + '/';
  }
  //go through an fill
  var i;
  for (i = 1; i < pathparams.type.length; i++) {
    //get information of parameters or global here.
    var use = null;
    if( change[i] !== undefined ) { use = change[i]; }
    else if ( change[pathparams.type[i]] !== undefined ) { use = change[pathparams.type[i]]; }
    else if ( here[pathparams.type[i]] !== null && i<=level ) { use = here[pathparams.type[i]]; }
    if ( use !== null ) {
      //write path
      pathval += pathparams.prefix[i]+use+'/';
    }
  }
  return URL+pathval;
}

//Creates an Object to save parameters for a form
/*/ !Default values:
  params.input[].name
                .label  : name
                .id     : (the same as name)
                .type   : text
                .value  : ""
                .needed : true;
  params.select[].name
                 .label : name
                 .id  : (the same as name)
                 .option[].value 
                          .text
// !Usage
//Start with this
-> params.formparam(...)

//And fill it up:
-> params.addinput(vname[, label, value, needed])
vname: name of input
label: text value of label
value: value of input
needed: 1 if necessary, 0 if optional.

-> params.addselect(vname[, label])
vname: name of input
label: text value of label

-> params.select[vname].addoption(value)
value: {value: text, value: text, ...}
*/
function formparam(title, submittxt, canceltxt) {
  this.title=title;
  this.submittext=submittxt||'create'; 
  this.canceltext=canceltxt||'cancel'; 
  this.input={};
  this.select={};  
  this.addinput= function(vname, labeltxt, valuetxt, need) {
    this.input[vname]={};
    this.input[vname].name=vname;
    this.input[vname].label=labeltxt||'name'; 
    this.input[vname].id=vname;
    this.input[vname].type='text';
    this.input[vname].value=valuetxt||'';
    this.input[vname].needed=need===0?0:1;
  };
  this.addselect= function(vname, labeltxt) {
    this.select[vname]={};
    this.select[vname].name=vname;
    this.select[vname].label=labeltxt||'name'; 
    this.select[vname].id=vname;
    this.select[vname].selected=0;
    this.select[vname].option={};
    this.select[vname].addoption=function(value) { //value: {value: text,value: text}
      var val;
      for(val in value) { this.option[val]=value[val]; }
    };
  };
}

//Trims whitespaces left anid right of a string
function trim(string) {
  while(string[0]===' ') { string=string.substr(1); }
  lim=0;
  while(string[string.length-1]===' ') { 
    string=string.substr(0,string.length-1); 
    lim++;
    if(lim>1000) { break; }  //miximum 1000 whitespaces. 
  }
  return string;
}

//fill array with one value
function fill_array(value, len) {
  var ret = [];
  var i;
  for( i=0; i<len; i++ ) {
    ret.push(value);
  }
  return ret;
}

//DEBUGGING
var debug = {
  warning: function(msg) {
    if(DEBUG && typeof console !== 'undefined') { //FIX: IE != 'undefined'
      console.log('WARNING in Function "'+arguments.callee.caller.name+'":\n'+msg);    
    }
  }
}
