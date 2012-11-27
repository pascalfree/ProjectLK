//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: query.js
//core
//description: Engine for queries (reviews).
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////

//////////
//Notes:
// This query engine works for every mode. 
// See documentation for information about these modes.
//////////

//////////
//ENGINE
//////////
// Big Engine that does everything  during a query
// Does all the interaction with the database
// updates the gui with some simple rules

/*/ DOC ////////////////////////////
These functions can only be used on prepared pages.
See doc (Query Page) for more information

plk.qe.start( force )
 force: if 1, forces restart of query engine
  qe is initialized. this is usualy called after the 
  page (DOM) is loaded on the query page

plk.qe.send()
  Checks the current answer (and sends it to the server)
  returns false in any case (use for onsubmit)

plk.qe.updateLast()
  Update the words that are displayed in the result section
  (in case a word is edited during the query)
  
plk.qe.skip()
  skips current question, loads next one
  skipped questions will reappear later in the query

plk.qe.showResult()
  shows result summary of the query (if query is finished or aborted)

plk.qe.changeMode( new_mode )
  changes mode of query (in which direction to translate and stay in group or not)
  Not all changes are possible. Only possible at the beginning of a query

plk.qe.correction()
  sets result of last question to "correct"

plk.qe.showWrong()
  shows a popup with a list of all wrong answered questions

plk.qe.mode()
  returns the current mode of the query
  ( edit via plk.qe.changeMode( new_mode ) )

plk.qe.goneId()
  returns id of last answered word
//////////////////////////////////*/

var plk = plk || {};

//The one and only engine
plk.qe = (function() {
  var obj = {};

  // PRIVATE //
  var started = 0;
  var gone, that, come, wrong; //word objects
  var private_mode, total, done, correct; //counting integers
  var formnames = null, personnames = null; //names
  var userAnswer, result; //others
  //This saves the elements for speedup (see function get_el) 
  var q_el= {};

    //helpers
    //saves elements to array and loads them
    //This is supposed for speed up. Elements dont have to be loaded via $$ every time.
    function get_el(element, force) {
      if(!q_el[element] || force==1) { q_el[element]=$$(element); }
      return q_el[element];
    }

    //clean all cached elements //force reload
    function clean_el() {
      q_el = {}
    }

    //shows or hides elements
    function withall(elements,bool) {
      var el=get_el('.q_'+elements);
      for(i=0;i<el.length;i++)  { (bool==1)?el[i].show():el[i].hide(); }
    }

    //write in all elements
    function write(elements,text) {
      var el=get_el('.q_'+elements);
      for (i in el) { el[i].innerHTML=text; }
    }

  //load next question
  var setNext = function(info,disp,skipped) {  //Fix: Skipped checks if entry is skipped (not wrong).
    try{
      //Save next word loaded from database
      //Update the local variables
      gone.word = that.word;
      gone.answer = that.answer;
      gone.id = that.id;
      that.word = come.word;
      that.answer = come.answer;
      that.id = come.id;
      if( info.word != null ) {
        gone.group = info.oldgroup;
        come.word = info.word.question?info.word.question[0]:null;
        come.answer = info.word.answer?info.word.answer[0]:null;
        come.id = info.word.id?info.word.id[0]:null;
      }
      if(result==0 && skipped!=1) { 
        wrong.id.push( gone.id );
        wrong.answer[gone.id] = userAnswer; 
      }
      //refresh display
      display(disp);
    } catch(err) { plk.PRIVATE.errg('[query-04]'+err); }
  }

  //Get direction of query
  // is used to tell the user what mode is used.
  var getQueryDir = function(link) {
    if(link == null) {link = ' &gt; ';}
    if(private_mode==0 || private_mode==2) {
      return plk.la.lang + link + plk.la.fore;
    } else if(private_mode==1 || private_mode==3) {
      return plk.la.fore + link + plk.la.lang;
    } else if(private_mode==4) {
      return '';
    }
  }

  ////Display
  //refresh display
  var display = function(showres)  {
    try{
      hidem(); //hide elements where necessary
      //update values of some elements
      write('done', done);
      write('correct', correct);
      write('thisword', that.word);
      write('lastword', gone.word);
      write('lastresult', gone.answer);
      write('lastanswer', userAnswer);
      //make the input back to blank
      var qans=$('q_answer');
      if(qans) {
        qans.value='';
        if(!Prototype.Browser.IE) { qans.focus(); } //Oh No! It's IE.
      }
    } catch(err) { plk.PRIVATE.errg('[query-05.0]'+err); }
    try {
      //Fix: hide skipbutton when useless.
      if( total <= done+1) { $($('queryform').skipbutton).disable(); }
      else { $($('queryform').skipbutton).enable(); }

      //delete-11-04-14: if(this.total>this.done) { write('thisword',this.that.word); }

      //resultdiv
      if ( showres == 1 ) {
        if( result ) { write('result', plk.la.corr); } 
        else { write('result',plk.la.wrong); }
        $('resultdiv').style.display = "block";
      } else { $('resultdiv').style.display = "none"; }
    } catch(err) { plk.PRIVATE.errg('[query-05.1]'+err); }
  }

  //initialize display
  var inidisp = function() {
    try{
      //is it still runing or finished? show right elements
      if( total <= done ) { withall('run',0); withall('fin',1); }
      else { withall('run',1); withall('fin',0); }

      //initialize some elements
      write('total', total);
      var percent=Math.round( correct/total*1000000 )/10000;
      write('percent', percent.toString());
      write('qquestion', plk.la.qquestion[private_mode]);
      write('qanswer', plk.la.qanswer[private_mode]); 
      //show link to wrong answeres if there are some:
      if(wrong.id.length!=0) {
        write('wrong',plk.la.wrongansws+': <a href="javascript: void(0)" onclick="javascript: plk.qe.showWrong()">'+plk.la.show+'</a>');
      } else { write('wrong',''); }
      //Show Links to change mode
      write('modeinfo',plk.la.querymode+': '+plk.la.modeinfo[private_mode]+' <a href="javascript: void(0)" id="chquerymodebutton" class="s -m0 -m1 -m4" onclick="javascript: plk.qe.changeMode(0)">'+plk.la.change+'</a>');
      write('modeinfodir', getQueryDir() +' <a href="javascript: void(0)" id="chquerydirbutton" class="s -m4" onclick="javascript: plk.qe.changeMode(1)">'+plk.la.change+'</a>');
      //fix: force reloading elements
      clean_el();

      //hide some elements
      hidem();
    } catch(err) { plk.PRIVATE.errg('[query-06]'+err); }
  }

  //Put all those wrong answers to a wonderful table.
  var dispwrong = function(info) {
    try{
      //TODO: Here could be a switch, for if the gui wants to make this table itself.

      var out='<table id="wordlist">';
      if( private_mode==4 ) {
        out+='<tr class="tabhead"><td>'+plk.la['person']+'</td><td>'+plk.la['form']+'</td><td>'+plk.la['sverb']+'</td><td>'+plk.la['wrongansw']+'</td></tr>';
        for(i=0;i<info.count;i++) {
          out+='<tr>';
            out+='<td>'+personnames[info.personid[i]]+'</td>';
            out+='<td>'+formnames[info.formid[i]]+'</td>';
            out+='<td>'+info.kword[i]+'</td>';
            out+='<td>'+wrong.answer[info.id[i]]+'</td>';
          out+='</tr>';
        }        
      } else {
        out+='<tr class="tabhead"><td>'+plk.la['lang']+'</td><td>'+plk.la['fore']+'</td><td>'+plk.la['wrongansw']+'</td></tr>';
        for(i=0;i<info.count;i++) {
          out+='<tr>';
            out+='<td>'+info.wordfirst[i]+'</td>';
            out+='<td>'+info.wordfore[i]+'</td>';
            out+='<td>'+wrong.answer[info.id[i]]+'</td>';
          out+='</tr>';
        }
      }
      out+='</table>';
      if(typeof(gui_wrong)=='function') { gui_wrong(out); } //Let the gui do the rest.
    } catch(err) { plk.PRIVATE.errg('[query-08]'+err); }
  }

  //gets names of forms and persons if not already loaded
  //needed to show wrong answered verbsqueries
  var getnames = function() {
    function save( info ) {
      var res = {};
      for(i=0;i<info.count;i++) {
        res[info['id'][i]]=info['name'][i];
      }
      return res;
    }

    if(formnames == null) { 
      plk.req('get_form',{'registerid':plk.here('registerid')}, function(info) { 
        formnames = save(info); 
        getnames();
      }); 
    } else if(personnames==null) { 
      plk.req('get_person',{'registerid':plk.here('registerid')},function(info) { 
        personnames = save(info); 
        getnames();
      });
    } else { 
      plk.req('get_verb',{'id[]': wrong.id, struc:1}, function(i) {
        dispwrong(i);
      }); 
    }
  }

  //hides elements depending on keywords
  var hidem = function() {
    try{
      //hide if every answer was correct
      var i;
      var w=get_el('.-w');
      for(i=0;i<w.length;++i) { correct == done?w[i].hide():w[i].show(); }
      //show if answer was correct
      var r=get_el('.r');
      for(i=0;i<r.length;++i) { result == 0?r[i].hide():r[i].show(); }
      //show if answer was incorrect
      var rr=get_el('.-r');
      for(i=0;i<rr.length;++i) { result ?rr[i].hide():rr[i].show(); }
      //show if every question has been answerd
      var f=get_el('.f');
      for(i=0;i<f.length;++i) { total > done?f[i].hide():f[i].show(); }
      //hide if every question has been answerd
      var ff=get_el('.-f');      
      for(i=0;i<ff.length;++i) { total <= done?ff[i].hide():ff[i].show(); }
      //show only if no question has been answerd yet
      var s=get_el('.s');
      for(i=0;i<s.length;++i) { done===0?s[i].show():s[i].hide(); }
      //Hide if mode is x
      var m=get_el('.-m'+private_mode,1);  
      for(i=0;i<m.length;++i) { m[i].hide(); }
    } catch(err) { plk.PRIVATE.errg('[query-09]'+err); }
  }

  // PUBLIC //

  ////Execute
  //Start a query, prepare everything
  obj.start = function(force) {
    if(started === 1 && force !== 1) { return false; } //only start once
    started = 1;
    gone= {}; //previous word
    that= {}; //this word
    come= {}; //next word
    //load information from database.
    plk.req('get_query', plk.here('queryid','') , function(info) { 
      try{
      //save information returned from database
      private_mode = info.mode;
      total = parseInt( info.total );
      done = parseInt( info.done );
      correct = parseInt( info.correct );
      wrong = info.wrong;
      if(info.wrong.id == null) { wrong={id: [], answer: {}}; } //Fix: Set Array instead of Object
      that = {};
      come = {};
      if(info.word) {
        that.word = info.word.question[0];
        that.answer = info.word.answer[0];
        that.id = info.word.id[0];
        come.word = info.word.question[1];
        come.answer = info.word.answer[1];
        come.id = info.word.id[1];
      }
      //prepare and fill the gui
      inidisp();
      display(); 
    } catch(err) { plk.PRIVATE.errg('[query-01]'+err); }
    });
  }

  //send an answer
  obj.send = function() {
    try{
      userAnswer = plk.util.clean($('q_answer').value);
      result = plk.word.isCorrect(userAnswer, that.answer);
      done += 1;
      correct += parseFloat(result);
      //Update Database, get next word, go to setnext
      plk.req('update_active',
        {queryid: plk.here('queryid'), 
        result: result, 
        wordid: that.id, 
        nextid: come.id, 
        answer: userAnswer, 
        uncorr: 0},
        function(info) { setNext(info,1); }
      );
    } catch(err) { plk.PRIVATE.errg('[query-02]'+err); }
    return false;
  }

  //update last answer
  //if a word is edited during query, update the information
  obj.updateLast = function() {
    try{
      plk.req('get_query',
        { 
          queryid : plk.here('queryid'), 
          wordid : gone.id 
        }, 
        function(info) { 
          //save updated information
          gone.word = info.word.question?info.word.question[0]:null;
          gone.answer = info.word.answer?info.word.answer[0]:null;
          //update information on screen
          display(1);
        }
      );
    } catch(err) { plk.PRIVATE.errg('[query-021]'+err); }
  }

  //skip a question
  obj.skip = function() {
    try{
      plk.req('update_active', 
        {
          queryid: plk.here('queryid'),
          wordid: that.id, 
          nextid: come.id, 
          uncorr: 1
        },
        function(info) { setNext(info,0,1) }
      );
    } catch(err) { plk.PRIVATE.errg('[query-03]'+err); }
    return false;
  }

  //show result of query (refresh display completely)
  obj.showResult = function() {
    inidisp();
    display();
  }

  //Change mode (to what?)
  obj.changeMode = function(what) {
    plk.req('change_mode',{chwhat: what, queryid: plk.here('queryid')},function() { obj.start(1) }) //restart after that
  }

  //Correct an answer
  obj.correction = function() {
    //qe.updatelast(); //Update last word (if edited) //fix : to late. call when edited
    plk.req('correction',
      {
        corrid: gone.id, 
        queryid: plk.here('queryid'),
        ngroup: gone.group, 
        gword: gone.word, 
        gans: gone.answer
      },
      function(i,params) { 
        //Fix: sometimes these variables go to null. This fixes it by passing it with the params and saving it again.
        gone.word = params.gword;
        gone.answer = params.gans;
        //Reset wrong answer and set to correct.
        wrong.id.splice( wrong.id.length-1, 1);
        delete wrong.answer[ gone.id ]; 
        correct += 1;
        result = 1;
        display(1);
      }
    );
  }

  //shows wrong answers
  obj.showWrong = function() {
    //only if there are words answered wrong:
    if( wrong.id.length > 0 ) {
      if( private_mode == 4 ) { getnames(); } //Get some verb information to fill in table
      //get those wrong answered words and do dispwrong
      else { 
        plk.req('get_word',
          { 'wordid[]': wrong.id, nolimit:1 }, 
          function(i) { dispwrong(i); }
        ); 
      }
    }
  }

  // accessors
  obj.mode = function() {
    return private_mode;
  }
  
  obj.goneId = function() {
    return gone.id;
  }
  
  return obj;
})();
