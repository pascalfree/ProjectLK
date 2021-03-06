<?php
//////////////////////////////////////
/* NAME: search
/* PARAMS: arg_
 - searchtext : the search string
/* RETURN: 
  // see get_....php for details
 - register
 - tag
 - save
 - form
 - person
 - verb
 - word
/* DESCRIPTION: search for everything
/* AUTHOR: David Glenck
/* LICENSE: GNU GPL v3
/* VERSION: 19.04.2012
////////////////////////////////////*/

  $go->necessary('searchtext');

  //prepare
  if($go->good()) {
    $param['searchtext'] = $arg_searchtext;
    $param['registerid'] = '*';
  }

  //get registers
  if($go->good()) {
    $get['register'] = plk_request('get_register', $param );
  }
  //get tags
  if($go->good()) {
    $get['tag'] = plk_request('get_tag', $param );
  }
  //get save
  if($go->good()) {
    $get['save'] = plk_request('get_save', $param );
  }
  //get form
  if($go->good()) {
    $get['form'] = plk_request('get_form', $param );
  }
  //get person
  if($go->good()) {
    $get['person'] = plk_request('get_person', $param );
  }
  //get verb
  if($go->good()) {
    $param['struc'] = 1;
    $get['verb'] = plk_request('get_verb', $param );
  }
  //get words
  if($go->good()) {
    $param['gettags'] = 0; // no tags
    $param['nolimit'] = 1; // no limit
    $param['searchtext'] = 'like: '.$arg_searchtext;
    $get['word'] = plk_request('get_word', $param );
  }


  //return  
  if($go->good()) {
    $return = $get;
  }
?>
