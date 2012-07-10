//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: query.js
//core
//description: Engine and global functions for queries.
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////

//////////
//UPDATES:
// 04.04.2012 - Fix : "Viele (Menschen)" didn't let "Viele" be a correct answer
//////////

//////////
//Notes:
// This query engine works for every mode. 
// See documentation for information about these modes.
//////////

//////////
//content:
//  Engine
//  Check Result
//  Other
//////////

//////////
//ENGINE
//////////
// Big Engine that does everything  during a query
// Does all the interaction with the database
// updates the gui with some simple rules

//This saves the elements for speedup (see function get_el) 
var q_el= {};

//The one and only engine
var qe={
  started:0,
  ////Execute
  //Start a query, prepare everything
  start: function(force) {
    if(this.started === 1 && force !== 1) { return false; } //only start once
    this.started = 1;
    this.gone= {}; //previous word
    this.that= {}; //this word
    this.come= {}; //next word
    //load information from database, go to refreshqinfo.
    req('get_query',{queryid:here.queryid}, function(i) { qe.refreshqinfo(i); });
  },

  refreshqinfo: function(info) {
    try{
      //save information returned from database
      this.mode=info.mode;
      this.total=parseFloat(info.total);
      this.done=parseFloat(info.done);
      this.correct=parseFloat(info.correct);
      this.wrong=info.wrong;
      if(info.wrong.id==null) { this.wrong={id: [], answer: {}}; } //Fix: Set Array instead of Object
      this.querylastid=this.that.id;
      this.that= {};
      this.come= {};
      if(info.word) {
        this.that.word=info.word.question[0];
        this.that.answer=info.word.answer[0];
        this.that.id=info.word.id[0];
        this.that.id=info.word.id[0];
        this.come.word=info.word.question[1];
        this.come.answer=info.word.answer[1];
        this.come.id=info.word.id[1];
      }
      //prepare and fill the gui
      this.inidisp();
      this.display(); 
    } catch(err) { errg('[query-01]'+err); }
  },

  //send an answer
  send: function() {
    try{
      this.hisanswer=clean($('q_answer').value);
      this.result=wordcheck(this.hisanswer, this.that.answer);
      this.done+=1;
      this.correct+=parseFloat(this.result);
      //Update Database, get next word, go to setnext
      req('update_active',
        {queryid:here.queryid, 
        result: this.result, 
        wordid: this.that.id, 
        nextid: this.come.id, 
        answer: this.hisanswer, 
        uncorr: 0},
        function(info) { qe.setnext(info,1); }
      );
    } catch(err) { errg('[query-02]'+err); }
    return false;
  },

  //update last answer
  //if a word is edited during query, update the information
  updatelast: function() {
    try{
      req('get_query',
        {queryid:here.queryid, 
        wordid:this.gone.id}, 
        function(i) { qe.updategone(i) }
      );
    } catch(err) { errg('[query-021]'+err); }
  },

  updategone: function(info) {
    //save updated information
    this.gone.word=info.word.question?info.word.question[0]:null;
    this.gone.answer=info.word.answer?info.word.answer[0]:null;
    //update information on screen
    this.display(1);
  },

  //skip a question
  skip: function() {
    try{
      req('update_active',
        {queryid:here.queryid,
        wordid: this.that.id, 
        nextid: this.come.id, 
        uncorr: 1},
        function(info) { qe.setnext(info,0,1) }
      );
    } catch(err) { errg('[query-03]'+err); }
    return false;
  },

  //load next question
  setnext: function(info,disp,skipped) {  //Fix: Skipped checks if entry is skipped (not wrong).
    try{
      //Save next word loaded from database
      //Update the local variables
      this.gone.word=this.that.word;
      this.gone.answer=this.that.answer;
      this.gone.id=this.that.id;
      this.that.word=this.come.word;
      this.that.answer=this.come.answer;
      this.that.id=this.come.id;
      if(info.word!=null ) {
        this.gone.group=info.oldgroup;
        this.come.word=info.word.question?info.word.question[0]:null;
        this.come.answer=info.word.answer?info.word.answer[0]:null;
        this.come.id=info.word.id?info.word.id[0]:null;
      }
      if(this.result==0 && skipped!=1) { 
        this.wrong.id[this.wrong.id.length]=this.gone.id;
        this.wrong.answer[this.gone.id]=this.hisanswer; 
      }
      //refresh display
      this.display(disp);
    } catch(err) { errg('[query-04]'+err); }
  },

  //show result of query (refresh display completely)
  showres: function() {
    this.inidisp();
    this.display();
  },

  //Change mode (to what?)
  chmod: function(what) {
    req('change_mode',{chwhat: what, queryid:here.queryid},function() { qe.start(1) }) //restart after that
  },

  //Get direction of query
  // is used to tell the user what mode is used.
  getquerydir: function(link) {
    if(link==null) {link=' &gt; ';}
    var mode=this.mode;
    if(mode==0 || mode==2) {
      return la.lang+link+la.fore;
    } else if(mode==1 || mode==3) {
      return la.fore+link+la.lang;
    } else if(mode==4) {
      return '';
    }
  },

  //Correct an answer
  correction: function() {
    //qe.updatelast(); //Update last word (if edited) //fix : to late. call when edited
    req('correction',
      {corrid:this.gone.id, 
      queryid:here.queryid,
      ngroup:this.gone.group, 
      gword:this.gone.word, 
      gans:this.gone.answer},
      function(i,params) { qe.correctit(params); }
    );
  },
  correctit: function(params) {
    //Fix: sometimes these variable goes to null. This fixes it by passing it with the params and saving it again.
    this.gone.word = params.gword;
    this.gone.answer = params.gans;
    //Reset wrong answer and set to correct.
    this.wrong.id.splice(this.wrong.id.length-1,1);
    delete this.wrong.answer[this.gone.id]; 
    this.correct+=1;
    this.result=1;
    this.display(1);
  },

  ////Display
  //refresh display
  display: function(showres)  {
    try{
      this.hidem(); //hide elements where necessary
      //update values of some elements
      write('done',this.done);
      write('correct',this.correct);
      write('correct',this.correct);
      write('thisword',this.that.word);
      write('lastword',this.gone.word);
      write('lastresult',this.gone.answer);
      write('lastanswer',this.hisanswer);
      //make the input back to blank
      var qans=$('q_answer');
      if(qans) {
        qans.value='';
        if(!Prototype.Browser.IE) { qans.focus(); } //Oh No! It's IE.
      }
    } catch(err) { errg('[query-05.0]'+err); }
    try {
      //Fix: hide skipbutton when useless.
      if(this.total<=this.done+1) { $($('queryform').skipbutton).disable(); }
      else { $($('queryform').skipbutton).enable(); }

      //delete-11-04-14: if(this.total>this.done) { write('thisword',this.that.word); }

      //resultdiv
      if (showres==1) {
        if(this.result) { write('result',la.corr); } else { write('result',la.wrong); }
        $('resultdiv').style.display = "block";
      } else { $('resultdiv').style.display = "none"; }
    } catch(err) { errg('[query-05.1]'+err); }
  },

  //initialize display
  inidisp: function() {
    try{
      //is it still runing or finished? show right elements
      if(this.total<=this.done) { withall('run',0); withall('fin',1); }
      else { withall('run',1); withall('fin',0); }

      //initialize some elements
      write('total',this.total);
      var percent=Math.round(this.correct/this.total*1000000)/10000;
      write('percent',percent.toString());
      write('qquestion',la.qquestion[this.mode]);
      write('qanswer',la.qanswer[this.mode]); 
      //show link to wrong answeres if there are some:
      if(this.wrong.id.length!=0) {
        write('wrong',la.wrongansws+': <a href="javascript: void(0)" onclick="javascript: qe.showwrong()">'+la.show+'</a>');
      } else { write('wrong',''); }
      //Show Links to change mode
      write('modeinfo',la.querymode+': '+la.modeinfo[this.mode]+' <a href="javascript: void(0)" id="chquerymodebutton" class="s -m0 -m1 -m4" onclick="javascript: qe.chmod(0)">'+la.change+'</a>');
      write('modeinfodir',this.getquerydir()+' <a href="javascript: void(0)" id="chquerydirbutton" class="s -m4" onclick="javascript: qe.chmod(1)">'+la.change+'</a>');
      //fix: force reloading elements
      clean_el();

      //hide some elements
      this.hidem();
    } catch(err) { errg('[query-06]'+err); }
  },

  //shows wrong answers
  showwrong: function() {
    //only if there are words answered wrong:
    if(this.wrong.id.length>0) {
      if(this.mode==4) { this.getnames(); } //Get some verb information to fill in table
      //get those wrong answered words and do dispwrong
      else { 
        req('get_word',
          {'wordid[]': this.wrong.id, nolimit:1}, 
          function(i) { qe.dispwrong(i); }
        ); 
      }
    }
  },

  //Put all those wrong answers to a wonderful table.
  dispwrong: function(info) {
    try{
      //TODO: Here could be a switch, for if the gui wants to make this table itself.

      var out='<table id="wordlist">';
      if(this.mode==4) {
        out+='<tr class="tabhead"><td>'+la['person']+'</td><td>'+la['form']+'</td><td>'+la['verb']+'</td><td>'+la['wrongansw']+'</td></tr>';
        for(i=0;i<info.count;i++) {
          out+='<tr>';
            out+='<td>'+this.personnames[info.personid[i]]+'</td>';
            out+='<td>'+this.formnames[info.formid[i]]+'</td>';
            out+='<td>'+info.kword[i]+'</td>';
            out+='<td>'+this.wrong.answer[info.id[i]]+'</td>';
          out+='</tr>';
        }        
      } else {
        out+='<tr class="tabhead"><td>'+la['lang']+'</td><td>'+la['fore']+'</td><td>'+la['wrongansw']+'</td></tr>';
        for(i=0;i<info.count;i++) {
          out+='<tr>';
            out+='<td>'+info.wordfirst[i]+'</td>';
            out+='<td>'+info.wordfore[i]+'</td>';
            out+='<td>'+this.wrong.answer[info.id[i]]+'</td>';
          out+='</tr>';
        }
      }
      out+='</table>';
      if(typeof(gui_wrong)=='function') { gui_wrong(out); } //Let the gui do the rest.
    } catch(err) { errg('[query-08]'+err); }
  },

  //gets names of forms and persons if not already loaded
  //needed to show wrong answered verbsqueries
  getnames: function() {
    if(this.formnames==null) { 
      req('get_form',{'registerid':here.registerid}, function(info) { 
        qe.save('form',info); 
        qe.getnames() 
      }); 
    } else if(this.personnames==null) { 
      req('get_person',{'registerid':here.registerid},function(info) { 
        qe.save('person',info); 
        qe.getnames()
      });
    } else { 
      req('get_verb',{'id[]': this.wrong.id, struc:1}, function(i) {
        qe.dispwrong(i)
      }); 
    }
  },

  //Helper function just needed to save informations of verbs.
  save: function(what, info) {
    this[what+'names'] = {};
    for(i=0;i<info.count;i++) {
      this[what+'names'][info['id'][i]]=info['name'][i];
    }
  },  

  //hides elements depending on keywords
  hidem: function() {
    try{
      //hide if every answer was correct
      var i;
      var w=get_el('.-w');
      for(i=0;i<w.length;++i) { this.correct==this.done?w[i].hide():w[i].show(); }
      //show if answer was correct
      var r=get_el('.r');
      for(i=0;i<r.length;++i) { this.result==0?r[i].hide():r[i].show(); }
      //show if answer was incorrect
      var rr=get_el('.-r');
      for(i=0;i<rr.length;++i) { this.result?rr[i].hide():rr[i].show(); }
      //show if every question has been answerd
      var f=get_el('.f');
      for(i=0;i<f.length;++i) { this.total>this.done?f[i].hide():f[i].show(); }
      //hide if every question has been answerd
      var ff=get_el('.-f');      
      for(i=0;i<ff.length;++i) { this.total<=this.done?ff[i].hide():ff[i].show(); }
      //show only if no question has been answerd yet
      var s=get_el('.s');
      for(i=0;i<s.length;++i) { this.done===0?s[i].show():s[i].hide(); }
      //Hide if mode is x
      var m=get_el('.-m'+this.mode,1);  
      for(i=0;i<m.length;++i) { m[i].hide(); }
    } catch(err) { errg('[query-09]'+err); }
  }
}

//////////
//CHECK RESULT
//////////

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

//Matches worda with wordb - not commutative
function wordcheck(worda,wordb) {
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
      if(check.stringcheck(trim(wordar[k]),trim(wordbr[j]))) { res=1; break; }
    }
    if(res==0) { break; }
  }
  return res;
}

//////////
//OTHER
//////////
//Some Helper Functions

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

//changes from uppercase to lowercase and vise versa
function changeCase(strch) {
  var res=strch.toUpperCase();
  if(res==strch) { res=strch.toLowerCase(); }
  return res;
}

//Cleans an input: removes whitespace
function clean(word) {
  word=word.replace(/\s+/g,' ');
  word=word.replace(/^ +/,'');
  word=word.replace(/ +$/,'');
  return word;
}
