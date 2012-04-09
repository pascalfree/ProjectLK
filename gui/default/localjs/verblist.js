//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: show.js
//GUI: default
//description: Javascript for showing lists of verbs (showverblist.php)
//Author: David Glenck
//Licence: GNU General Public Licence (see license.txt in Mainfolder)
//////////////////////////////

//////////
//EVENTS
//////////

function eventloader_showverblist() {
  //dis-/activate all checkboxes
  $$('input[type="checkbox"][name^="all"]').each( function(item) {
    item.onclick=function() { 
      do_action(this.checked?1:0, this.readAttribute('name').substr(3),'checkall')
    };
  });
}

//////////
//ACTIONS
//////////

//query selected from lists
function action_query_verb() {
  //serialize the form
  var param = $('verbqueryform').serialize(true);
  //validate form
  if( !param.allverb && !param['verbid[]'] ) { msg(la.err_210); return 0; }
  if( !param.allform && !param['formid[]'] ) { msg(la.err_207); return 0; }
  if( !param.allperson && !param['personid[]'] ) { msg(la.err_208); return 0; }

  //add registerid to param
  param['registerid'] = here.registerid;

  req('create_active_verb', param, function(info) {
    if( info.count == 0 ) { msg(la.err_250); return 0; }
    document.location.href = path(2,{ queryid: info.savedid });
  });
}

function action_checkall_generic() {
  //id is true or false -> check or uncheck
  $$('input[name="'+local.current.type+'id[]"]').each(function(item) {
    item.checked = local.current.id != 0;
  });
}

//////////
//PERSON REARRANGE
//////////

//select a person
function rep_start(pid) {
  local.rep = {};
  local.rep.selected=pid;
  local.rep.startorder = {};
  for(i=0;i<$('personlist').childNodes.length;i++) {
    local.rep.startorder[Element.identify($('personlist').childNodes[i])]=i;
  }
}

//stop rearranging
function rep_end() {
  if(local.rep) {
    if(local.rep.selected!=null) {
      var same=true;
      for(i=0;i<$('personlist').childNodes.length;i++) {
        if(local.rep.startorder[Element.identify($('personlist').childNodes[i])]!=i) { same=false; }
      } 
      if(!same) { update_order(); }
      local.rep=null;
    }
  }
}

//Change positions
function rep_change(tid) {
  if(local.rep) {
    if(local.rep.selected!=null) {
      if(local.rep.selected!=tid) {
        var prsnlst=$('personlist');
        var pthis=$('person_'+tid);
        var psel=$('person_'+local.rep.selected);
        var list=Object.values(prsnlst.childNodes);
        posnew=list.indexOf(pthis);
        posold=list.indexOf(psel);
        if(posnew>posold) { where=pthis.nextSibling; }
        else{ where=pthis; }
        movenode=psel.cloneNode(true);
        prsnlst.removeChild(psel);
        prsnlst.insertBefore(movenode,where);
      }
    }
  }
}

//if something has changed update to database
function update_order() {
  var prsnlst=$('personlist').childNodes;
  for(i=0;i<prsnlst.length;i++) {
    var perid=Element.identify(prsnlst[i]);
    var perid=perid.replace('person_','');
    req('edit_person',{personid:perid,neworder:i});
  }
}
