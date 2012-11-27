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
  var mode = plk.qe.mode();
  if( (mode == '0') || (mode == '2') ) { what = (qa == 0) ? 'wordfirst' : 'wordfore'; }
  else if( (mode == '1') || (mode == '3') ) { what = (qa == 1) ? 'wordfirst' : 'wordfore'; }
  else if( mode == '4' ) { what = (qa == 1) ? 'kword' : false; }

  if( what ) { do_action( plk.qe.goneId(), what, 'edit', where ); }
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
    plk.req('edit_verb',{ verbid: id, newkword:input.value }, after_send, { id:id, type:'kword', action:'edit', key:13 });
  }
}

//after action edit
after_send_edit_13_wordfirst_query =
after_send_edit_13_wordfore_query = 
after_send_edit_13_kword_query = function(info, params, id, type) {
  // 07.04.2012 - Fix : update via query engine
  plk.qe.updateLast();
  close_action('edit',type);
}

//Save query at the end of the query
function querysave(wrong) {
  //rvar.create(
  var savequery = new plk.reqObj(
      'query_save',
      {registerid : plk.here('registerid'), queryid : plk.here('queryid'), wrong : wrong}, 
      close_popup
  );
  show_error_104_query_save(savequery, true); //show form without errormessage
}

//show error if queryname allready exists
function show_error_104_query_save(reqvar, no_error) {
  var qs_params = new plk.formObj('querysave');
  qs_params.addinput('newsavename','name');
  if( no_error !== true ) { qs_params.error = 'err_duplicatesave'; }
  request_form( reqvar, qs_params );
}
