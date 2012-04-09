<?php
//////////////////////////////
//This file is part of the projectLK
//////////////////////////////
//name: export.php
//core
//description: Loads all userinformation of location to a xml file to download.
//Author: David Glenck
//Licence: GNU General Public Licence (see licence.txt in Mainfolder)
//////////////////////////////


if($you -> id == NULL) {echo 'Export_Error: Missing userid';}
else {
  @ini_set('memory_limit', '32M');  //more Memory for large exports

  //Create a XML file and send to Download
  $filename=P_NAME.P_VERSION.'-'.date("ymdGis");
  header('Content-Type: text/xml');
  header('Content-Disposition: attachment; filename="'.$filename.'.xml"');

  function u($str) { return rawurlencode($str); }  //shortcut

  //Load Data
  $user=request('get_option');
    //regarray:
    $regarray['gettime']=1;
    if($here->registerid!=NULL) { $regarray['registerid']=$here->registerid; }
  $register=request('get_register',$regarray);
  $hereparam=(array) $here;
  $hereparam['getsave']=1;
  $hereparam['gettags']=1;
  $hereparam['nolimit']=1;
  $wordlist=request('get_word',$hereparam);
  $verblist=request('get_verb',array('wordid'=>$wordlist['id'],'struc'=>1));

  //Write XML
  echo '<xmlexport name="'.P_NAME.'" version="'.P_VERSION.'">';
    //User
    echo '<user id="'.$user['id'].'" name="'.u($user['name']).'" password="'.$_SESSION['lk_userpwmd5'].'" email="'.u($user['email']).'" theme="'.$user['theme'].'" gui="'.$user['gui'].'" time="'.$user['time_created'].'"/>';
    //register
    echo '<registerlist>';
      for($i=0;$i<$register['count'];$i++) {
        $reginfo=request('get_reg_info',array('registerid'=>$register['id'][$i]));
        echo '<register id="'.$register['id'][$i].'" name="'.u($register['name'][$i]).'" groupcount="'.$reginfo['groupcount'].'" grouplock="'.$reginfo['grouplock'].'" language="'.$reginfo['language'].'" time="'.$register['time_created'][$i].'"/>';
      }
    echo '</registerlist>';
    //wordlist
    echo '<wordlist>';
      for($i=0;$i<$wordlist['count'];$i++) {
        echo '<word id="'.$wordlist['id'][$i].'" registerid="'.$wordlist['registerid'][$i].'" wordfirst="'.u($wordlist['wordfirst'][$i]).'" wordfore="'.u($wordlist['wordfore'][$i]).'" groupid="'.$wordlist['groupid'][$i].'" sentence="'.u($wordlist['sentence'][$i]).'" wordclassid="'.$wordlist['wordclassid'][$i].'" time="'.$wordlist['time_created'][$i].'"/>';
      }
    echo '</wordlist>';
    //taglist
    echo '<taglist>';
    for($i=0;$i<$register['count'];$i++) {
      $taglist=request('get_tag',array('registerid'=>$register['id'][$i],'wordid'=>$wordlist['id']));
      for($j=0;$j<$taglist['count'];$j++) {
        echo '<tag id="'.$taglist['id'][$j].'" name="'.u($taglist['name'][$j]).'" registerid="'.$register['id'][$i].'"/>';
      }
    }
    echo '</taglist>';
    //tags
    echo '<tags>';
      for($i=0;$i<$wordlist['count'];$i++) {
        for($j=0;$j<$wordlist['tagslist'][$i]['count'];$j++) {
          echo '<tag wordid="'.$wordlist['id'][$i].'" tagid="'.$wordlist['tagslist'][$i]['id'][$j].'"/>';
        }
      }
    echo '</tags>';
    //savelist
    echo '<savelist>';
    for($i=0;$i<$register['count'];$i++) {
      $savelist=request('get_save',array('registerid'=>$register['id'][$i],'wordid'=>$wordlist['id']));
      for($j=0;$j<$savelist['count'];$j++) {
        echo '<save id="'.$savelist['id'][$j].'" name="'.u($savelist['name'][$j]).'" registerid="'.$register['id'][$i].'" time="'.$savelist['time_created'][$j].'"/>';
      }
    }
    echo '</savelist>';
    //saves
    echo '<saves>';
      for($i=0;$i<$wordlist['count'];$i++) {
        for($j=0;$j<$wordlist['savelist'][$i]['count'];$j++) {
          echo '<save wordid="'.$wordlist['id'][$i].'" saveid="'.$wordlist['savelist'][$i]['id'][$j].'"/>';
        }
      }
    echo '</saves>';
    //formlist
    echo '<formlist>';
    for($i=0;$i<$register['count'];$i++) {
      $formlist=request('get_form',array('registerid'=>$register['id'][$i],'wordid'=>$wordlist['id']));
      for($j=0;$j<$formlist['count'];$j++) {
        echo '<form id="'.$formlist['formid'][$j].'" name="'.u($formlist['formname'][$j]).'" registerid="'.$register['id'][$i].'" info="'.$formlist['info'][$j].'"/>';
      }
    }
    echo '</formlist>';
    //personlist
    echo '<personlist>';
    for($i=0;$i<$register['count'];$i++) {
      $personlist=request('get_person',array('registerid'=>$register['id'][$i],'wordid'=>$wordlist['id']));
      for($j=0;$j<$personlist['count'];$j++) {
        echo '<person id="'.$personlist['personid'][$j].'" name="'.u($personlist['personname'][$j]).'" registerid="'.$register['id'][$i].'" order="'.$personlist['order'][$j].'"/>';
      }
    }
    echo '</personlist>';
    //verblist
    echo '<verblist>';
      for($i=0;$i<$verblist['count'];$i++) {
        echo '<verb id="'.$verblist['id'][$i].'" wordid="'.$verblist['wordid'][$i].'" personid="'.$verblist['personid'][$i].'" formid="'.$verblist['formid'][$i].'" kword="'.u($verblist['kword'][$i]).'" regular="'.$verblist['regular'][$i].'" time="'.$verblist['time_created'][$i].'"/>';
      }
    echo '</verblist>';
  echo '</xmlexport>';
}
?>
