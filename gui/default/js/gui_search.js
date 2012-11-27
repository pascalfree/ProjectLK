//////////
//SEARCHBAR & AUTOSEARCH
//////////

//////////
// GLOBAL VARIABLES

//autosearch cache
local.as = {};
local.asgot = {};
local.aselect = -1;

//a list of available types as a hash
//for search function
local.types = {
register: plk.la.register,
group: plk.la.group,
tag: plk.la.tag,
save: plk.la.savepoint,
word: plk.la.word,
wordclass: plk.la.wordclass,
without: plk.la.without,
verb: plk.la.sverb,
form: plk.la.form,
person: plk.la.person,
'search': plk.la['search']
};

//////////
// HELPER

// s_earch in f_ind and add span with cssclass
function highlight_match(s, f, cssclass) {
  //position where match begins
  var ttpos=f.search( new RegExp(s,"i") );
  //if matching, highlight the part
  if(ttpos!=-1) { 
    return f.substr(0,ttpos) + '<span class="'+cssclass+'">' + f.substr(ttpos,s.length) + '</span>' + f.substr(ttpos+s.length); }

  //return input if no matching
  else { return f; }
}

//hides autosearch
function hide_autosearch() {
  $('autosearcher').hide();
}

//shows searchfield
function show_search() {
  //hide link to search
  $('searchclick').hide(); 
  //show form to search
  var searchform = $('searchform');
  searchform.setStyle({ display:'inline'});
  searchform.searchtext.focus();
}

//hides searchfield
function hide_search(delay) {
  if(!$('searchform')) { return false; }
  if( delay==null ) { delay=400; } //default delay is 400ms
  local.close_search_timer = window.setTimeout(function() {
    $('searchform').hide(); 
    $('searchclick').setStyle({ display:'inline'}); 
    hide_autosearch();
  },delay);
}

//stops closing dropdown
function hold_search() {
  window.clearTimeout( local.close_search_timer );
}

function reset_search() {
  //hide autosearch
  hide_autosearch();
  //reset autosearch pointer position
  local.aselect = -1;
  //clear searchfield
  $('searchtext').value = '';
}

//Navigation throug results
function auto_hl_down() {
  autochoose(1);
  auto_highlight(local.aselect);
}

function auto_hl_up() {
  autochoose(-1);
  auto_highlight(local.aselect);
}

//controlls the range of search results
function autochoose(plus) {
  if(childs=$('autolist')) {
    local.aselect+=plus;
    var childs=$('autolist').childNodes;
    if(local.aselect<-1) { local.aselect=-1; }
    if(local.aselect>childs.length-1) { local.aselect=childs.length-1; }
  }
}

//highlights results from autosearch
function auto_highlight(id) {
  if(childs=$('autolist')) {
    var childs=$('autolist').childNodes;
    for(var i=0;i<childs.length;i++) {
      if(i==id || childs[i]==id) { 
        Element.writeAttribute(childs[i],'class','list_marked'); 
        $('searchtext').value=childs[i].title;
      }
      else { Element.writeAttribute(childs[i],'class','list_none'); }
    }
  }
}

//////////
//FIND

//submits the searchbar
function search() {
  try {
    if(!local.data.register.count) { return false; }

    plk.here.clear('searchid'); //don't use this
    var st = $('searchtext').value; //shortcut
    
    var srchfrm=$('searchform');

    if(st!='') {
      var found = 0; //if nothing is found local -> load searchpage

      //Search local data
      if( st.indexOf(local.types['search']+':') != 0 ) { //"search: " will force searching
        //get type if type is there
        var type = st.split(':')[0].strip()
        type = Object.keys( local.types )[ Object.values( local.types ).indexOf( type ) ]; //get type of plk.la[type]
        if( type !== undefined ) { //if found type take away from searchtext
          st = st.substr( st.indexOf(':')+1 ).strip();
        }
        //if register search register
        var register = st.split('/')[0].strip();
        var index = local.data.register['name'].indexOf( register );
        if( index != -1 ) { //found register
          //set here to this register
          plk.here.set( { "registerid": local.data.register['id'][index] } );  
          //remove from rest of searchtext
          st = st.substr( st.indexOf('/')+1 ).strip();
        }

        if( st && (st != register || index == -1) ) { // more than just a register in searchbox
          //special case: group
          if( type == 'group' && isFinite( parseInt( st ) ) ) {
            found = 1;
            do_action(parseInt( st ), 'group', 'goto');
            reset_search();
          }
          //all other cases
          var h = local.as[ local.current.subtext ][type];
          if( h && h.count ) {
            if( type == 'word' ) { //word is special
              var s = [ h['wordfirst'], h['wordfore'], h['sentence'] ];
            } else if( type == 'verb' ) {
              var s = [ h.kword ];
            } else {
              var s = [ h['name'] ];
            }
            for( var j=0; j<s.length; j++ ) {
              //if(!s) { break; }
              var key;
              //search until entry with matching register is found
              var k=0;
              do { 
                key = s[j].indexOf( st , k );
                k = key + 1;
              } while( h['registerid'][key] != plk.here('registerid') && key != -1 );
              
              if( key != -1 ) { //entry found
                found = 1; 
                //go to found location
                if(type == 'verb') { var id = h['wordid'][key]; }
                else { id = h['id'][key]; }
                do_action(id, type, 'goto');
                reset_search();
                break;
              }
            }
          }
        } else { // searchtext only contains register
          // here is already updated above
          found = 1; 
          do_action(null,null,'goto');
          reset_search();
        }
      }

      if( found == 0 ) {
        //search globaly
        do_action(st,'search','goto');
        reset_search();
      }
    }
    return false;
  } catch(err) { 
    msg('[search]:'+err+' - st = '+st); 
  }
}

function block_keydown(ev) {
  //block tab
  if ( get_key(ev) == 9) {
    return false;
  } 
  return true;
}

//////////
//GET RESULTS

//Requests search information from Database and saves it locally.
function action_search(ev) {
  try {
    var keypress = true; //return true at the end
    //get key to navigate up and down
    var keynum = get_key(ev);
    if( keynum ==27 ) { //esc
      reset_search();
      hide_search(0);
    } else if(keynum==40 || keynum==9) { //down or tab
      //window.clearTimeout( local.close_search_timer );
      keypress = false; //block other actions with these keys
      auto_hl_down();
    }
    else if(keynum==38) { //up
      keypress = false;
      auto_hl_up(); 
    }
    else if(keynum == 13) {//enter
      //search
      search();
    }
    //load everything that looks like matching
    //-> get results
    else {
      plk.here.set( { searchid : $('searchtext').value } ); //searchtext
      get_searchresult();
    }
    return keypress;
  } catch(err) { 
    msg('[action_search]:'+err+''); 
  }
}

function get_searchresult() {
  try {
    //need names of registers to display:
    if( !local.data["register"] ) {
      plk.req('get_register',[], function(i) { local.data.register = i; });
    }

    var st = plk.here('searchid'); //shortcut

    //don't load results if searchstring is to small
    if(st.length < 3) { 
      proceed_searchresult();
      return 0;
    }

    //else load results
    local.current.subtext = st.substr(0,3); //take first 3 letters
    var subt = local.current.subtext //shortcut
    //local.current.subsearch = subt; //save for later
    //create object if not existing
    if(!local.as[subt]) { local.as[subt] = {}; }

    if( !local.asgot[subt] ) { //not loaded yet
      local.asgot[subt]=true;

      //load all with names
      local.as[subt]['type'] = { count: 11, 'name': Object.values(local.types), id: Object.keys(local.types) };
      local.as[subt]['group'] = { count:2, registerid: plk.util.fillArray(plk.here('registerid'),2), 'name': [plk.la.af, plk.la.ar], id : [ 'af', 'ar' ] };
      local.as[subt]['without'] = { count: 2, registerid: plk.util.fillArray(plk.here('registerid'),2), 'name': [plk.la.tag, plk.la.savepoint], id: ['tag' , 'save'] };
      local.as[subt]['wordclass'] = { count:6, registerid: plk.util.fillArray(plk.here('registerid'),6), 'name': plk.la.classname, id : [0,1,2,3,4,5,6] };

      plk.req('search',{searchtext:subt}, function(info,params) { 
        Object.extend(local.as[ params.searchtext ] , info);
        proceed_searchresult();
      });

    //already loaded -> go on
    } else {
      proceed_searchresult();
    }
  } catch(err) { 
    msg('[get_searchresult]:'+err+''); 
  }
}

//shows the autosearcher
//fresh means that this comes fresh from the database (no local data);
function proceed_searchresult() {
  try {
    var st = plk.here('searchid'); //shortcut
    var subt = local.current.subtext;
    
    //Search in stored results
    var sres=[[],[],[],[],[],[],[],[],[],[],[],[]]; //prepare array with 11 arrays;

    for(var i in local.as[subt]) {
      var h = local.as[subt][i]; //shortcut
      if( !h.count ) { continue; } //0 or not defined
      if( i == 'word' ) { 
        var s = [ 'wordfirst', 'wordfore', 'sentence' ];
      } else if( i == 'verb' ) {
        var s = [ 'kword' ];
      } else {
        var s = [ 'name' ];
      }
     
      for(var j=0;j<s.length;j++) {
        for(var k=0; k<h[s[j]].length; k++) {
          //priority for matches in this register
          var p = 0;
          if( i != 'register' && h['registerid']) {
            if( h['registerid'][k] != plk.here('registerid') ) {
              p = 5;
            }
          }
          //search with priority
          try {
            var probe=h[s[j]][k].toLowerCase();
            if(probe.match('^'+st.toLowerCase()+'$')) {
              sres[0].push( [i, s[j], k] ); //save type, subtype and index 
            } else if(probe.match('^'+st.toLowerCase()+' ')) {
              sres[1+p].push( [i, s[j], k] );                       
            } else if(probe.match(' '+st.toLowerCase()+' ') 
              || probe.match(' '+st.toLowerCase()+'$')) {
              sres[2+p].push( [i, s[j], k] );    
            } else if(probe.match('^'+st.toLowerCase())) {
              sres[3+p].push( [i, s[j], k] );        
            } else if(probe.match(st.toLowerCase())) {
              sres[4+p].push( [i, s[j], k] );
            } else {
              var sectry=st.toLowerCase().split('').join('.?');
              if(probe.match(sectry)) {
                sres[5+p].push( [i, s[j], k] );
              }
            }

          } catch(err) { 
            msg('[proceed_searchresult_1]:'+err+' - st: '+st.inspect()+' | probe: '+probe.inspect()); 
            //find errors: make local to global
            error = {s:s, sres:sres};
          }
        }//end for k
      }//end for j
    }
    //save localy
    local.current.searchresult = [];
    for( var i=0; i<sres.length; i++ ) {
      local.current.searchresult = local.current.searchresult.concat( sres[i] );
    }

    update_searchresult(); //generates output
  } catch(err) { 
    msg('[proceed_searchresult]:'+err+''); 
  }
}

function update_searchresult() {
  try {
    var st = plk.here('searchid'); //shortcut
    var subt = local.current.subtext;
    var result = local.current.searchresult //shortcut

    //switch from show to search
    if(plk.here('page') == 'show') {
      plk.here.set( {page:'search'} );
    }

    //update page
    var func = switcher( 'update_searchresult', plk.here('page') );
    if( func ) { window[ func ](); }

    //if count = 0 -> hide
    if(result.length == 0) { 
      hide_autosearch();
      return 0;
    }
  
    //if searchbar is not active -> stop
    if( $('searchclick').visible() ) { 
      hide_autosearch();      
      return 0; 
    }

    //else show results in autosearch
    //fill list
    var preval = [];
    for ( var j=0; j<result.length; j++ ) {
      var val = '';
      var prefix = '';
      var suffix = '';
      var s = result[j]; //shortcut : s = [type, subtype, index];

      //don't show words -> show words directly in table
      if( plk.here('page') == 'search' && s[0] == 'word' ) { 
        //local.current.search_word.push(s[2]);
        continue;
      }

      //add type as prefix if not type
      if(s[0] != 'type') {
        prefix += local.types[s[0]]+': ';
      }

      //add register as prefix if not here
      if( s[0] != 'register' && local.as[subt][s[0]]['registerid']) {
        var tregisterid = local.as[subt][s[0]]['registerid'][s[2]];
        if( plk.here('registerid') != tregisterid ) {
          prefix += get_name( 'register', tregisterid )+'/ ';
        }
      }

      //highlight matching part
      val += highlight_match(st, local.as[subt][s[0]][s[1]][s[2]], 'markedsearch');

      //add ":" as suffix if this type
      if( s[0] == 'type' ) { suffix += ': ' }

      var ret = '<li onmouseover="auto_highlight(this)" title="'+ prefix + local.as[subt][s[0]][s[1]][s[2]] + suffix + '">';
      ret += prefix + val + suffix;
      ret += '</li>';

      //append to list
      preval.push( ret );
    }
    //join
    var val='<ul id="autolist" onclick="javascript: search()">';
    val += preval.join('')+'</ul>';
    //Display
    position = getposition($('searchtext'));
    var left = position[0];
    var top = position[1] + $('searchtext').offsetHeight;
    var as = $('autosearcher');
    if ( as ) {
      as.update( val );
      as.setStyle({
        top : top+'px',
        left : left+'px',
        width: ($('searchtext').offsetWidth-10)+'px'
      });
      as.show(); 
    }

  } catch(err) { 
    msg('[update_searchresult]:'+err+''); 
    local.error= { s:s };
  }
}
