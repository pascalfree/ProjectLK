//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: queryl.js
//GUI: default
//description: functions specific to the query page
//Author: David Glenck
//Licence: GNU General Public Licence (see license.txt in Mainfolder)
//Update: 07.04.2012
//////////////////////////////

//find out how the answers in the query should be edited
function queryedit(where,qa) {
  //decode type from mode and qa
  var what;
  if( (qe.mode == '0') || (qe.mode == '2') ) { what = (qa == 0) ? 'wordfirst' : 'wordfore'; }
  else if( (qe.mode == '1') || (qe.mode == '3') ) { what = (qa == 1) ? 'wordfirst' : 'wordfore'; }
  else if( qe.mode == '4' ) { what = (qa == 1) ? 'kword' : false; }

  if( what ) { do_action( qe.gone.id, what, 'edit', where ); }
}

//action edit
action_edit_wordfirst_query = 
action_edit_wordfore_query = 
action_edit_kword_query = function(id, type, where) {
  action_edit_generic(id, type, where, false, where.innerHTML); //07.04.2012 fix : pass content as parameter
}

//send edit verb (kword) //Overwrite
function send_edit_kword_query(id) {
  var input=$('input_edit'); //this field should contain the new name
  if(input) {
    req('edit_verb',{ verbid: id, newkword:input.value }, after_send, { id:id, type:'kword', action:'edit', key:13 });
  }
}

//after action edit
after_send_edit_13_wordfirst_query =
after_send_edit_13_wordfore_query = 
after_send_edit_13_kword_query = function(info, params, id, type) {
  // 07.04.2012 - Fix : update via query engine
  qe.updatelast();
  close_action('edit',type);
}

//Save query at the end of the query
function querysave(wrong) {
  rvar.create(
      'savequery',
      'query_save',
      {registerid : here.registerid, queryid : here.queryid, wrong : wrong}, 
      close_popup
  );
  show_error_104_query_save(true); //show form without errormessage
}

//show error if queryname allready exists
function show_error_104_query_save(no_error) {
  var qs_params = new formparam('querysave');
  qs_params.addinput('newsavename','name');
  if( no_error !== true ) { qs_params.error = 'err_duplicatesave'; }
  request_form('savequery',qs_params);
}
