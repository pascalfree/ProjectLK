<?php
  $go->necessary('searchtext');

  //prepare
  if($go->good()) {
    $param['searchtext']=$searchtext;
    $param['registerid']='*';
  }

  //get registers
  if($go->good()) {
    $get['register']=request('get_register', $param );
  }
  //get tags
  if($go->good()) {
    $get['tag']=request('get_tag', $param );
  }
  //get save
  if($go->good()) {
    $get['save']=request('get_save', $param );
  }
  //get form
  if($go->good()) {
    $get['form']=request('get_form', $param );
  }
  //get person
  if($go->good()) {
    $get['person']=request('get_person', $param );
  }
  //get verb
  if($go->good()) {
    $param['struc'] = 1;
    $get['verb']=request('get_verb', $param );
  }
  //get words
  if($go->good()) {
    $param['gettags'] = 0; //no tags
    $param['nolimit'] = 1; // no limit
    $param['searchtext'] = 'like: '.$searchtext;
    $get['word']=request('get_word', $param );
  }


  //return  
  if($go->good()) {
    $return=$get;
  }
?>
