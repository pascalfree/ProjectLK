<?php
  if($registerid==NULL) { $intoregister=0; }
  if($withouttag==NULL) { $withouttag=array(); }
  if($withoutsave==NULL) { $withoutsave=array(); }

  function transformtags($tags) {
    $ret=NULL;
    foreach($tags as $value) {
      $ret[$value['wordid']][]=$value['tagid'];
    }
    return $ret;
  }

  function transformsave($saves) {
    $ret=NULL;
    foreach($saves as $value) {
      $ret[$value['wordid']][]=$value['saveid'];
    }
    return $ret;
  }

  function transformword($saves) {
    $ret=NULL;
    foreach($saves as $value) {
      $ret[$value['saveid']][]=$value['wordid'];
    }
    return $ret;
  }

  function tagtonames($ids,$names) {
    $ret=NULL;
    if($ids!=NULL) {
      foreach($ids as $id) {
        $ret[]=$names[$id]['name'];
      }
    }
    return $ret;
  }

  $importfile=UPDIR.$userid.'import.xml';
  if(!file_exists($importfile)) { $go->error(105); }
  else {
    $importing=getimport($importfile,1);
    unset($erraff);
    unset($idreplace['register']);

    //Registers
    $added['register']=0;
    if($intoregister!=1) {  //If words aren't importet to new registers
      if(count($registerlist)>0) {  //If any register is selected.
        foreach($importing['registerlist'] as $t) {
          if(in_array($t['id'],$registerlist)) {
            $regadd=request('create_register',array('newregister'=>$t['name'],'groupcount'=>$t['groupcount'],'grouplock'=>$t['grouplock'],'language'=>$t['language'],'time_created'=>$t['time']));
            if($regadd['errors']!='') { $errors=1; $erraff['register'][]=$t['id']; }
            else { $added['register']++; }

	          $idreplace['register'][$t['id']]=$regadd['newid'];
          }
        }
      }
    }

    //Transform
    $wordtags=transformtags($importing['tags']); //variable[wordid]=array(tags)
    $wordsaves=transformsave($importing['saves']); //variable[wordid]=array(tags)

    //Words
    $count=count($importing['wordlist']);
    $added['word']=0;
    foreach($importing['wordlist'] as $tword) { //Walk through
      if($intoregister == 1) { $tregid=$registerid; }  //insert into existing register
      elseif(isset($idreplace['register'][$tword['registerid']])) { $tregid=$idreplace['register'][$tword['registerid']]; }
      else { $tregid=NULL; }
      if($tregid!=NULL) {   //register must exist
        if(($wordtags[$tword['id']]==NULL && in_array($tword['registerid'],$withouttag))
           || array_match($wordtags[$tword['id']],$taglist)) {    //tags must be selected
          if(($wordsaves[$tword['id']]==NULL && in_array($tword['registerid'],$withoutsave))
            || array_match($wordsaves[$tword['id']],$savelist)) {    //save must be selected
            $tags=tagtonames($wordtags[$tword['id']],$importing['taglist']);
            if($tags!=NULL) { $tags=implode(', ',$tags); }
            $params=array('newwordfirst'=>$tword['wordfirst'],
                          'newwordfore'=>$tword['wordfore'],
                          'newgroup'=>$tword['groupid'],
                          'newsentence'=>$tword['sentence'],
                          'newwordclass'=>$tword['wordclassid'],
                          'newtags'=>$tags,
                          'registerid'=>$tregid,
                          'time_created'=>$tword['time'],
                          'force'=>1);
            $wordadd=request('create_word',$params);
            if($wordadd['errors']!='') { $errors=1; $erraff['word'][]=$tword['id']; }
            else { $added['word']++; }

            $idreplace['word'][$tword['id']]=$wordadd['id'];
          }
        }
      }
    }

    //Save
    $added['save']=0;
    if(count($savelist)>0) { //Is any save selected?
      $wordids=transformword($importing['saves']); 
      foreach($importing['savelist'] as $tsave) {
        if(in_array($tsave['id'],$savelist)) {  //Is this save selected?
          unset($nwordid); 
          foreach($wordids[$tsave['id']] as $wordid) {  //Get all wordids from words in this save
            if(isset($idreplace['word'][$wordid])) {
              $nwordid[]=$idreplace['word'][$wordid];
            }
          }   
          if(isset($nwordid)) {    //Are there any wordids?
            if($intoregister==1) { $tregid=$registerid; }
            else { $tregid=$idreplace['register'][$tsave['registerid']]; }
            $saveadd=request('create_save',array('newsave'=>$tsave['name'],'registerid'=>$tregid,'wordid'=>$nwordid, 'time_created'=>$time));
            if($saveadd['errors']!='') { $errors=1; $erraff['save'][]=$tsave['id']; }
            else { $added['save']++; }
          }
        }
      }  
    } 

    //Form
    $added['form']=0;
    if(count($formlist)>0) { //Is any form selected?
      foreach($importing['formlist'] as $tform) {
        if(in_array($tform['id'],$formlist)) {  //Is this form selected?
          if($intoregister==1) { $tregid=$registerid; }  //insert into existing register
          elseif(isset($idreplace['register'][$tform['registerid']])) { $tregid=$idreplace['register'][$tform['registerid']]; }
          else { $tregid=NULL; }
          if($tregid!=NULL) {    //Register exists?
            $formadd=request('create_form',array('newform'=>$tform['name'],'registerid'=>$tregid,'newinfo'=>$tform['info']));
            if($formadd['errors']!='') { $errors=1; $erraff['form'][]=$tform['id']; }
            else { $added['form']++; }

            $idreplace['form'][$tform['id']] = $formadd['newid'][0];
          }
        }
      }  
    } 

    //Person
    $added['person']=0;
    if(count($personlist)>0) { //Is any person selected?
      foreach($importing['personlist'] as $tperson) {
        if(in_array($tperson['id'],$personlist)) {  //Is this Person selected?
          if($intoregister==1) { $tregid=$registerid; }  //insert into existing register
          elseif(isset($idreplace['register'][$tperson['registerid']])) { $tregid=$idreplace['register'][$tperson['registerid']]; }
          else { $tregid=NULL; }
          if($tregid!=NULL) {    //Register exists?
            $personadd=request('create_person',array('newperson'=>$tperson['name'],'registerid'=>$tregid,'neworder'=>$tperson['order']));
            if($personadd['errors']!='') { $errors=1; $erraff['person'][]=$tperson['id']; }
            else { $added['person']++; }

            $idreplace['person'][$tperson['id']] = $personadd['newid'][0];
          }
        }
      }  
    } 

    //Verbs
    $count=count($importing['verblist']);
    $added['verb']=0;
    if($formlist!=NULL && $personlist!=NULL && $added['word']!=NULL) {
      foreach($importing['verblist'] as $tverb) { //Walk through
        if(isset($idreplace['word'][$tverb['wordid']])) {  //word must have been added
          if(in_array($tverb['formid'],$formlist)) {        //form must be selected
            if(in_array($tverb['personid'],$personlist)) {    //person must be selected
              $params=array('wordid'=>$idreplace['word'][$tverb['wordid']],
                            'personid'=>$idreplace['person'][$tverb['personid']],
                            'formid'=>$idreplace['form'][$tverb['formid']],
                            'newverb'=>$tverb['kword'],
                            'newregular'=>$tverb['regular'],
                            'time_created'=>$tverb['time']);
              $verbadd=request('create_verb',$params);
              if($verbadd['errors']!='') { $errors=1; $erraff['verb'][]=$tverb['id']; }
              else { $added['verb']++; }
            }
          }
        }
      }
    }
  }

  $return=array('count'=>$added ,'erraff'=>$erraff);
?>
