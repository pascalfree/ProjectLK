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
//  GUI Interaction
//  Hash
//  Here
//  Database
//  Formobject
//  Check String/word
//  Utility
//////////

//namespace
var plk = plk || {};

//Initialization onLoad
document.observe("dom:loaded", function() {
  if(plk.here('page') === 'query') { plk.qe.start(); } //starts engine for queries
});

//////////
// GUI INTERACTION
//////////

//its not really private but gui code should not mess with this. 
//  Will not cause any bad errors, just makes no sense.
//gui can decide how to to show these outputs

plk.PRIVATE = {
  //Sends an errorstring to the gui or alerts.
  errg : function(out) {
    if( typeof gui_error === 'function' ) { gui_error(out); }
    else { alert(out); }
  },

  reqerr : function(info) {
    if( typeof gui_request_error === 'function' ) { gui_request_error( info ); }
    else { plk.PRIVATE.errg(info.errnum+': '+info.errname+ ' - '+info.lastquery); }
  },

  //Called when a request is started. num is the statuscode
  //see function req
  ajaxCatch: function(num) {
    if( typeof gui_ajaxcatch === 'function' ) { gui_ajaxcatch(num); }
  }
}

//////////
// HASH
//////////
// functions to deal with url hash

/*/ DOC ////////////////////////////
plk.hash.onChange()
  Can only be called once on a page. 
  Will observe the URL hash for changes and call plk.hash.load() if change is detected.

plk.hash.load()
  loads the information from the hash into plk.here

plk.hash.update()
  updates the hash according to the contents of plk.here
//////////////////////////////////*/
plk.hash = (function() {
  var obj = {};
  var lastHash;
  var firstHash;
  var called = 0; //is 1 if onChange has been called

  //check for changes in the url hash
  obj.onChange = function() {
    //if browser supports onhashchange listen to event
    var dmode = document.documentMode;
    if( 'onhashchange' in window && ( dmode === undefined || dmode > 7 ) ) {
      window.onhashchange = obj.onChange;
    } else { //else recheck in a second
      window.setTimeout(function() { obj.onChange() }, 1000);
    }

    //first load hash if empty
    if(lastHash === undefined) {
      obj.update(); 
    }
    //if changed
    if( lastHash !== location.hash && location.hash !=="" ) { // hash has changed and isn't empty
      lastHash = location.hash;
      obj.load();
    }
    //simulate backlink which makes hash empty:
    //firstHash is the hash of the page which was loaded freshly
    if( location.hash === "" && lastHash !== firstHash ) {
      lastHash = firstHash;
      obj.load();
    }
  }

  //loads the hash to here
  obj.load = function() {
    plk.here.clear(['registerid','groupid','saveid','tagid','verbid','personid','formid','withoutid','page','searchid']);
    plk.here.set( ('{'+lastHash.slice(1)+'}').evalJSON() );
    plk.here.set( { 'searchid': unescape( plk.here().searchid ) } )  //this is a string

    //pass to gui
    if( typeof gui_load_hash === 'function' ) { gui_load_hash(); }
  }

  //updates url hash from variable here
  obj.update = function() {
    //go through these ids in here
    var loc = ['registerid','groupid','saveid','tagid','wordclassid','wordid','personid','formid','withoutid','page'];
    var fill = {}; //object to fill
    var i;
    for( i=0; i<loc.length; i++ ) {
      var ll = plk.here(loc[i]);
      if( ll !== null && ll !== undefined ) { 
        fill[loc[i]] = ll;
      }
    }
    //escape searchid
    if( plk.here('searchid') !== null ) { 
      fill.searchid = escape( plk.here('searchid') );
    }  

    var newhash = '#'+Object.toJSON(fill).slice(1,-1); 
    //this is the fresh loaded page (witch has no hash)
    if(newhash === firstHash) {
      location.hash = '';
    } else if(lastHash !== undefined) { //write to url if not fresh loaded
      location.hash = newhash; 
    } else { //if url is fresh save this as original
      firstHash = newhash;
    }
    //on next change url will also change
    lastHash = newhash; //otherwise onHashChange will detect change and load it a second time
  }

  return obj;
})() //end plk.hash

//////////
// HERE
//////////
// functions to handle the global varibale here

/*/ DOC ////////////////////////////
plk.here( key )
OR plk.here()
 key : single string representing a key
       OR multiple string arguments representing keys
       OR array of strings representing keys
return :  object containing 
  Access information about the current location. Equivalent of php $plk_here.
  List of keys:
  url - url of current page without the host
  login - 1 if login was successful
  nregerr - 1 if registration was succesful
  forgot - 1 if "forgot password" mail was successfully sent
  registerid, groupid, tagid, saveid, wordclassid, wordid, formid, personid - id's of current location
  withoutid - value is "tag" or "save", when viewing words without tags or saves
  timerange - timerange in which words should be loaded: [timestamp_start, timestamp_end]
  queryid - id of current query (review)
  searchid - test searched by user
  searchtext - modified searchtext (e.g. to find similar words)
  keyoption - last key in URL or calculated key if no key in url
  page - php file that was loaded without .php
Examples:
  plk.here('registerid') //returns id of current register
  plk.here() // returns object of non-empty values of here: { registerid: 123, groupid: 1, ... }
  plk.here('registerid', 'page') //returns { registerid: 123, page: 'show' }
  plk.here( ['registerid','page'] ) //returns same as above

plk.here.set({ key: value, key2: value2, ... })
  sets keys in plk.here to 'value'. Can not create new keys, only update existing ones.

plk.here.clear([key, key2, ...])
OR plk.here.clear( key )
  removes key form plk.here (can still be set again)

plk.here.path( level, params )
OR plk.here.path( params )
OR plk.here.path()
 level: number - how deep to write the path from 'here', optional
 params: { key: value, key2: value2 } - change specific value (independent from level) , optional
  Equivalent to php $plk_here->path()
  level: 1-userid/2-registerid/3-groupid/4-saveid/
         5-tagid/6-wordclassid/7-wordid/8-personid/9-formid/
         10-searchid/11-queryid/12-keyoption
Examples:
plk.here.path(2) //returns http://HOST/DIRNAME/userid/registerid
plk.here.path(1, {'registerid':3}) //returns http://HOST/DIRNAME/userid/3
//////////////////////////////////*/

plk.here = (function() {
  //all possible members of here must be set. cannot add new ones later.
  var private_here = {
    "url":null,
    "login":null,
    "nregerr":null,
    "forgot":null,
    "registerid":null,
    "groupid":null,
    "tagid":null,
    "saveid":null,
    "wordclassid":null,
    "wordid":null,
    "timerange":null,
    "formid":null,
    "personid":null,
    "withoutid":null,
    "queryid":null,
    "searchid":null,
    "searchtext":null,
    "keyoption":null,
    "page":null
  };

  // plk.here()
  var obj = function( want ) {
    //return multiple values as object
    function getMultiKeys( args ) {
      //only get non-empty value(s) specified in arguments
      var ret = {};
      for( var i=0; i<args.length; ++i ) {
        if( typeof args[i] == 'string' && private_here[ args[i] ] != null ) {
          ret[ args[i] ] = private_here[ args[i] ];
        }
      }
      return ret;
    }

    //get all non-empty values
    if( want === undefined ) {
      var ret = {};
      for( var i in private_here ) {
        if( private_here[i] !== null ) { //non-empty
          ret[i] = private_here[i];
        }
      }
      return ret;
    } else if( typeof want == 'string' ) {
      //get single value
      if( arguments.length == 1 ) {
        return private_here[ want ];
      //get multiple values as object
      } else {
        return getMultiKeys( arguments );
      }
    //get multiple values as object /alternative by array
    } else if( Object.isArray( want ) ) {
      return getMultiKeys( want );
    }
  };

  // void plk.here.set( object param )  updates variable here with param
  obj.set = function( param ) {
    try {
      //walk through
      var i;
      for(i in param ) {
        //assert
        if( typeof private_here[i] === 'undefined' ) { throw "can't create new values in 'here'. Tried to create entry for: "+i; }

        //do it
        private_here[i]=param[i];
        //special cases:
        if(i === 'tagid' && private_here.withoutid === 'tag') { private_here.withoutid = null; }
        if(i === 'saveid' && private_here.withoutid === 'save') { private_here.withoutid = null; }
        if(i === 'withoutid' && param[i] === 'tag') { private_here.tagid = null; }
        if(i === 'withoutid' && param[i] === 'save') { private_here.saveid = null; }
        if(i === 'page') { private_here.keyoption = param[i]; } //make page to keyoption
        if(i === 'searchid') { private_here.searchtext = null; } //also remove searchtext
        //clear more if registerid is changed
        if(i === 'registerid') {
          //remove all useless locations
          obj.clear(['saveid','tagid','verbid','personid','formid']);
        }
        //pass to gui
        if( typeof gui_refresh_here === 'function' ) {
          gui_refresh_here(i);
        }
      }
    } catch(err) {  //catch error here
      plk.PRIVATE.errg('[plk.here.set()] ERROR: '+err);
    }
  },

  //remove some ids from "here"
  // void plk.here.clear( string id )
  // or void plk.here.clear( array ids )
  obj.clear = function( ids ) {
    //make array
    if( typeof ids === 'string' ) { ids = [ ids ]; }
    // break if its something else
    if( !Object.isArray( ids ) ) { throw '[plk.here.clear()] parameter ids must be string or array.'; }
    //set null if not undefined
    var i;
    for( i=0; i<ids.length; i++ ) {
      if(private_here[ ids[i] ] !== undefined ) {
        private_here[ ids[i] ] = null;
      }
    }
  }

  //creates the current path like the php function
  obj.path = function(setlevel, params) {
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
    if( 0 < pathparams.type.length && plk.you.id !== null) {
      pathval += plk.you['name'] + '/';
    }
    //go through an fill
    var i;
    for (i = 1; i < pathparams.type.length; i++) {
      //get information of parameters or global here.
      var use = null;
      if( change[i] !== undefined ) { use = change[i]; }
      else if ( change[pathparams.type[i]] !== undefined ) { use = change[pathparams.type[i]]; }
      else if ( plk.here(pathparams.type[i]) !== null && i<=level ) { use = plk.here(pathparams.type[i]); }
      if ( use !== null ) {
        //write path
        pathval += pathparams.prefix[i]+use+'/';
      }
    }
    return URL+pathval;
  }

  return obj;
})();

//////////
// DATABASE
//////////
// Tools for database interaction, espacially requests

/*/ DOC ////////////////////////////
plk.req( functionname, params, action, passthrough )
 functionname : database function to call (see doc: database functions)
 params : parameters to pass to the database function, as an object or a query string ("a=1&b=hello").
 action : after_functionname
          OR [ before_functionname ]
          OR [ before_functionname, after_functionname ]
          function to call after (or before) database function returns results
 passthrough : object to pass to the function from action as a parameter.
  Equivalent to php $plk_request(...)
  For functionnames and params see doc (database functions)
  action is called with 2 or 3 parameters:
    after_functionname( info, params, passthrough )
    before_functionname( params, passthrough )
  info is an object with the results returned from the database function. 
  params, are the parameters that were sent to the server (for the database function).
  
plk.reqObj( functionname, params, action, passthrough )
  parameters are the same as for plk.req(...).
  This is a constructor for an request object. 
  This object can prepare the request, add additional parameters 
  later and send it via methods.
  plk.reqObj#addparams( params )
   params: object or querystring
    adds params to the list of parameters
  plk.reqObj#send( params )
   params: [optional] calls addparams internally with params.
    sends the request. (calls plk.req())
Examples:
var addregister = new plk.reqObj( 'create_register' );
show_form(); //function that shows an input field to the user
addregister.addparams( get_input() ) //function that gets value of input as object
addregister.send();
//NOTE: the power of plk.reqObj is that it may be passed 
//to show_form() as a parameter and then be sent there.
//////////////////////////////////*/

//Requests information from DB
plk.req = function( functionname, params, action, passthrough ) {
  try {
    //Prepare parameters
    plk.PRIVATE.ajaxCatch(0);
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
    plk.PRIVATE.errg('[req] Error: '+plk.la.err_ajax+' : '+err);
  }
  try {  //Send via prototype ajax.request
    new Ajax.Request(
      functionname+'.php', {
        parameters: params,
        onCreate: function() { plk.PRIVATE.ajaxCatch(1); },
        onUninitialized: function() { plk.PRIVATE.ajaxCatch(2); },
        onLoading: function() { plk.PRIVATE.ajaxCatch(3); },
        onLoaded: function() { plk.PRIVATE.ajaxCatch(4); },
        onInteractive: function() { plk.PRIVATE.ajaxCatch(5); },
        onComplete: function() { plk.PRIVATE.ajaxCatch(6); },
        onSuccess: function(transport) {
          plk.PRIVATE.ajaxCatch(7);
          //Get returned information
          var info = transport.responseText.evalJSON(true);
          //Check for errors
          if(info.errnum !== 0) { plk.PRIVATE.reqerr(info); }
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
          plk.PRIVATE.ajaxCatch(8);
        },
        onFailure: function() { plk.PRIVATE.errg("[req] Fatal Error: "+responseText); }
      } 
    );
  } catch(err2) {
    plk.PRIVATE.errg('[req] Error: '+plk.la.err_ajax+' : '+err2);
  }
  return false;
}

//new reqObj constructor
plk.reqObj = function( functionname, params, action, passthrough ) {
  this.functionname = functionname;
  if( typeof params === 'object' ) { this.params = Object.clone(params); }
  else if( typeof params === 'string' ) { this.params = params.toQueryParams(); }
  else { this.params = {}; }
  this.action = action;
  this.passthrough = passthrough;
}

//Adds parameters to a request variable
plk.reqObj.prototype.addparams = function( params ) {
  //Get params of string
  if( typeof params === 'string' ) {
    params = params.toQueryParams();
  }   
  //Or copy array
  if(!this.params) { this.params = {}; } // first create empty object if inexistent
  var names;
  for(names in params) { // append to params list
    this.params[names] = params[names];
  }
}

//Sends request from a request variable
plk.reqObj.prototype.send = function( params ) {
  //last chance to add parameters
  if( params !== undefined ) {
    this.addparams( params );
  }
  //send
  plk.req(this.functionname, this.params, this.action, this.passthrough);
  return false;
}

//////////
// FORMOBJECT
//////////
//Creates an Object to save parameters for a form

/*/ DOC ////////////////////////////
  plk.formObj creates an object with methods that
  can store and add input fields, buttons and labels.
  A function that converts this object into a real 
  html form is found in the GUI.
  This makes it much simpler to create a form 
  on the fly.

 !Default values:
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
-> var a_form = new formObj(...)

//And fill it up:
-> a_form.addinput(vname[, label, value, needed])
vname: name of input
label: text value of label
value: value of input
needed: 1 if necessary, 0 if optional.

-> a_form.addselect(vname[, label])
vname: name of input
label: text value of label

-> a_form.select[vname].addoption(value)
value: {value: text, value: text, ...}
//////////////////////////////////*/
plk.formObj = function(title, submittxt, canceltxt) {
  this.title=title;
  this.submittext=submittxt||'create'; 
  this.canceltext=canceltxt||'cancel'; 
  this.input={};
  this.select={};
}

plk.formObj.prototype.addinput= function(vname, labeltxt, valuetxt, need) {
  this.input[vname]={};
  this.input[vname].name=vname;
  this.input[vname].label=labeltxt||'name'; 
  this.input[vname].id=vname;
  this.input[vname].type='text';
  this.input[vname].value=valuetxt||'';
  this.input[vname].needed=need===0?0:1;
};

plk.formObj.prototype.addselect= function(vname, labeltxt) {
  this.select[vname]={};
  this.select[vname].name=vname;
  this.select[vname].label=labeltxt||'name'; 
  this.select[vname].id=vname;
  this.select[vname].selected=0;
  this.select[vname].option={};
  this.select[vname].addoption = function(value) { //value: {value: text,value: text}
    var val;
    for(val in value) { this.option[val]=value[val]; }
  };
};

//////////
// CHECK STRING /word
//////////
//Functions to validate syntax words before sending.

/*/ DOC ////////////////////////////
plk.word.validate( word )
 word: the string to check for syntax (invalid brackets)
  checks if word has a valid syntax that can be checked by the query engine.
return: 0 (no error) if word is valid
        1: number of opening and closing brackets not equal
        2: brackets in brackets
        3: only opening brackets or closing ones
        4-6: the same for square brackets

plk.word.errorAt( word )
 word: see above.
  finds and returns the index of the first invalid character of the string word
return: -1 if no error was found (use plk.word.validate(word) if position is irrelevant).
        position of error else

plk.word.isCorrect( word_check, word_match )
 word_check: string to check for correctness
 word_match: string which word_check is matched against
  checks if word_check is a possible solition of word_match (not commutative)
return: 1 if word_check is correct
        0 else  
//////////////////////////////////*/

plk.word = {};

//validates the syntax of an entry
plk.word.validate = function(str) {
  var err=0;
  //check round brackets ( )
  var br_open=str.match(/\(/g);
  var br_close=str.match(/\)/g);
  var br_pair=str.match(/\([^\(\)]*\)/g);
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
plk.word.errorAt = function(str) {
  var i=0;
  var br=0; 
  var sbr=0; 
  var err=-1;
  while(i < str.length) {
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

//Matches worda with wordb - not commutative
plk.word.isCorrect = function(worda,wordb) {
  //04.04.2012 - Fix : "Viele (Menschen)" didn't let "Viele" be a correct answer
  //function to match a stringa with a stringb (recursive)
    //version 9.05.10.2
    function strchk() {
      this.last; //var to save the last char which matched
      this.laststringb = null;
      this.stringcheck = function(stringa, stringb) {
        res = 1; i = 0;
        maxlen = Math.max( stringa.length, stringb.length ); 
        if( this.laststringb == stringb ) { return 0; }  //prevent useless recursion
        else {
          this.laststringb = stringb;
          while( stringa.charAt(i) == stringb.charAt(i) ) { //try until mismatch
           this.last = stringa.charAt(i);
           if( stringb.charAt(i) == '*' ) { i = maxlen; break; }
           i++;
           if( i >= maxlen ) { break; }
          }
          if( i < maxlen ) {                     //find problem of mismatch
            stringa = stringa.substr(i);       //shorten strings to relevant parts
            stringb = stringb.substr(i);
            if( this.last == ' ' && stringb.charAt(0) == ' ' ) {    //if problem is whitespace
              res = this.stringcheck( stringa, stringb.substr(1) );
            } else if( stringb.charAt(0) == '(' || stringb.charAt(0) == '[' ) {   //if problem is a bracket (bracket function)
              res = this.stringcheck( stringa, stringb.substr(1) );  //try the same with no bracket
              if ( res == 0 && stringb.charAt(0) == '(' ) { 
                res = this.stringcheck( stringa, stringb.replace( /\([^\)]+\)/, '' ) );   //try the same without bracket and content
              }                                                               //(bracket function only)
              part = stringb.substr(1);
              while ( res == 0 ) {               //try every part of the content seperatet by a slash (slash function)
                npart = part.replace(/^[^\)\/\]]+\//,'');
                if( part == npart ) { break; }
                res = this.stringcheck( stringa, npart );
                part = npart;
              }
            } else if( stringb.charAt(0) == ' ' && (stringa.charAt(0) == null || stringa.charAt(0) == '')  ) {     //if problem is a space in wordb, when worda is allready over
              res = this.stringcheck( stringa, stringb.substr(1) ); //try the same without the space
            } else if( stringb.charAt(0) == '-' && stringb.charAt(1) == ')' ) {     //if problem is a hyphen before a closing bracket (hyphen function)
              part = stringb.charAt(2).toLowerCase() + stringb.substr(3);
              res = this.stringcheck( stringa, part );  //try with changed case of the first character
            } else if( stringb.charAt(0) == ')' || stringb.charAt(0) == ']' ) { // if problem is a closing bracket just remove it //fix: also ]
              res = this.stringcheck( stringa, stringb.substr(1) );
            } else if( stringb.charAt(0) == '/' ) {     //if problem is a slash remove the rest of the brackets content (slash function)
              res = this.stringcheck( stringa, stringb.replace( /\/[^\)\]]+[\)|\]]/, '' ) );
            } else if( stringb.charAt(0) == '*' || ( stringb.charAt(0) == ' ' && stringb.charAt(1) == '*' ) ) { //if problem is a asterik (asterik function)
              if( stringa.charAt(0) != null && stringa.charAt(0) != '' ) { res=0; }  //if the other string is over here, the match is valid
            } else { res=0; }                //otherwise no luck
          }
          return res;
        }
      }
    }

  //Split possible answers
  var wordar=worda.split(',');
  var wordbr=wordb.split(',');
  var lenk=wordar.length;
  var lenj=wordbr.length;

  //Go through every combination until one matches
  var res;
  for(k=0;k<lenk;k++) {
    res=0;
    for(j=0;j<lenj;j++) {
      var check = new strchk();
      //Use this amazing function to compare
      if( check.stringcheck( plk.util.trim(wordar[k]), plk.util.trim(wordbr[j])) ) { 
        res=1; 
        break; 
      }
    }
    if(res==0) { break; }
  }
  return res;
}

//////////
// UTILITY
//////////

/*/ DOC ////////////////////////////
Some self documenting utility functions
//////////////////////////////////*/

plk.util = {};

//Trims whitespaces left and right of a string
plk.util.trim = function( string ) {
  return string.replace(/ +$/,'').replace(/^ +/,'');
  /*--while(string[0]===' ') { string=string.substr(1); }
  var lim=0;
  while(string[string.length-1]===' ') { 
    string = string.substr(0, string.length-1); 
    lim++;
    if(lim>1000) { break; }  //miximum 1000 whitespaces. 
  }
  return string;*/
}

//trim + removes whitespace
plk.util.clean = function( word ) {
  return plk.util.trim( word.replace(/\s+/g,' ') );
}

//fill array with one value
plk.util.fillArray = function(value, len) {
  var ret = [];
  for( var i=0; i<len; i++ ) {
    ret.push(value);
  }
  return ret;
}

//toggle from uppercase to lowercase and vise versa
plk.util.changeCase = function( strch ) {
  var res = strch.toUpperCase();
  if( res==strch ) { res = strch.toLowerCase(); }
  return res;
}

////////////
// DEBUGGING
////////////

//plk.debug.warning(msg) shows error message in console.log (in browsers which support it)
// only works if DEBUG is set to 1 in config.php
plk.debug = {
  warning: function(msg) {
    if(DEBUG && typeof console !== 'undefined') { //FIX: IE != 'undefined'
      console.log('WARNING in Function "'+arguments.callee.caller.name+'":\n'+msg);    
    }
  }
}
