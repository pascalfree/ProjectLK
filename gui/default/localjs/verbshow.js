//helperfunction count (1) elements and get ids (0) of html id
//returns array with both
function read_count_id(matchstr,idlen) {
  var id = [];
  var count=$$(matchstr).each(function(item) {
    id.push(item.identify().slice(idlen));
  }).length;
  return {id:id, count:count};
}

//function to add form in table view of verbtable
//this OVERWRITES the function from guifunctions.js
function after_send_add_0_form_showverb(info,params) {
  try {
    if(here.formid == null) { //do nothing if table is of one form
      var nparams = {};
      nparams['location'] = 'php/wordtable.php';
      nparams['function'] = 'wt_entry';
      nparams['json'] = 1;

      //add title first for each added form //form is always a column
      for(var j = 0; j < info.count; j++) {
        nparams['parameters'] = '['+info.newid[j]+',"'+info.newname[j]+'","form",null,"form_'+info.newid[j]+'_remove",["show","hr","edit","delete"]]';
        req('get_function',nparams, function(info) {
          appender(info.output, 'form', $$(".tabhead")[0], "bottom");
        }); 
      }

      //determine location. table of person and of verbs have form as column
      if(here.wordid != null) { //wordid is king
        //count lines and get personid from <tr> id
        var person = read_count_id('[id^="tr_person_"]', 10);
        //each added form
        for(var j = 0; j < info.count; j++) {
          //walk through lines
          for(var i = 0; i < person['count']; i++) {
            nparams['parameters'] = '["v'+here.wordid+'_p'+person.id[i]+'_f'+info.newid[j]+'","","kword",['+here.wordid+','+person.id[i]+','+info.newid[j]+'],"form_'+info.newid[j]+'_remove",["edit"]]';
            //load html and append at the end of every line
            req('get_function',nparams,function(info,p,s) {
              appender(info.output, 'kword', $$("[id^='tr_person']")[s], "bottom")
            }, i);
          }
        }

      } else if(here.personid != null) { //personid is king
        //count lines and get personid from <tr> id
        var verb = read_count_id('[id^="tr_verb_"]', 8);
        //walk through lines
        for(var i = 0; i < verb['count']; i++) {
          nparams['parameters'] = '["v'+verb.id[i]+'_p'+here.personid+'_f'+info.newid+'","","kword",['+verb.id[i]+','+here.personid+','+info.newid+'],"form_'+info.newid+'_remove",["edit"]]';
          //load html and append at the end of every line
          req('get_function',nparams, function(info,p,s) {
            appender(info.output, 'kword', $$("[id^='tr_verb']")[ s ], "bottom");
          }, i);
        }
      }

    }
    //popup hide
    close_popup();
  } catch(err) { msg('[after_send_add_0_form]:'+err); errnum=1; }
}

//function to add person in table view of verbtable
//this OVERWRITES the function from guifunctions.js
function after_send_add_0_person_showverb(info,params) {
  try {
    if(here.person == null) { //do nothing if table is of one person
      var nparams = {};
      nparams['json'] = 1; 

      //determine location.
      //table of verb have it as line
      if(here.wordid != null) { //wordid is king
        nparams['location'] = 'php/verbtable.php';
        nparams['function'] = 'writeverbline';
        //count lines and get personid from <tr> id
        var form=read_count_id('td[id^="form_"]', 5);
        for(var i=0; i<info.count; i++) {
          nparams['parameters'] = Object.toJSON([0 , {id:here.wordid, what:"verb"}, {what:"form", id:form.id, count:form.count}, {what:"person", id:[info.newid[i]], "name":[info.newname[i]]}, null ]);
          //load and append html
          req('get_function',nparams, function(info) {
            appender(info.output, 'person', $("wordlist").down(), "bottom");
          });
        }

      //table of form have person as column 
      } else if(here.formid != null) { //formid is king
        nparams['location'] = 'php/wordtable.php';
        nparams['function'] = 'wt_entry';
        //add title first
        for(var j=0; j<info.count; j++) {
          nparams['parameters'] = '['+info.newid[j]+',"'+info.newname[j]+'","person",null,"person_'+info.newid[j]+'_remove",["show","hr","edit","delete"]]';
          req('get_function',nparams, function(info) {
            appender(info.output, 'kword', $$(".tabhead")[0], "bottom");
          });
        }

        //count lines and get personid from <tr> id
        var verb=read_count_id('[id^="tr_verb_"]', 8);
        //each added person
        for(var j=0; j<info.count; j++) {
          //walk through lines
          for(var i=0; i<verb['count']; i++) {
            nparams['parameters'] = '["v'+verb.id[i]+'_p'+info.newid[j]+'_f'+here.formid+'","","kword",['+verb.id[i]+','+info.newid[j]+','+here.formid+'],"person_'+info.newid[j]+'_remove",["edit"]]';
            //load html and append at the end of every line
            req('get_function',nparams, function(info,p,s) {
              appender(info.output, 'kword', $$("[id^='tr_verb']")[s], "bottom");
            }, i);
          }
        }
      }

    }
    //popup hide
    close_popup();
  } catch(err) { msg('[after_send_add_0_person]:'+err); errnum=1; }
}

//add key up and down event when editing
//38 the up key 
function input_keydown_38_kword() {
  //close_action(); //close old input
  send_input();
}
function after_send_edit_38_kword(info,params) {
  after_send_edit_13_kword(info,params) //same as with enter
  //go to previous line
  //find td index
  var index = local.current.where.up().previousSiblings().size();
  // span -> td -> tr -> previous tr -> td -> span
  var next = local.current.where.up().up().previous();
  if( next ) { next = next.down('td',index).down(); } //don't produce errors
  if( next ) { next.ondblclick(); }
}

//40 the down key 
function input_keydown_40_kword() {
  //close_action(); //close old input
  send_input();
}
function after_send_edit_40_kword(info,params) {
  after_send_edit_13_kword(info,params) //same as with enter
  //go to next line
  //find td index
  var index = local.current.where.up().previousSiblings().size();
  // span -> td -> tr -> next tr -> td -> span
  var next = local.current.where.up().up().next();
  if( next ) { next = next.down('td',index).down(); } //don't produce errors
  if( next ) { next.ondblclick(); }
}
