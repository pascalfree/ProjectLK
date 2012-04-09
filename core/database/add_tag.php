<?php 
//////////////////////////////////////
/* NAME: add_tag
/* PARAMS: 
 - registerid
 - newtag: name of tag
 - (wordid[array] OR /global/ AND allmarked=1) 
/* RETURN: 
 - counttag : number of tags added
 - count : number of tags added to the table  
 - tags : Array of tagnames
 - tagid : Array of tagids
/* DESCRIPTION: Adds a tag to a word or to an array of words.
/* VERSION: 19.04.2011
////////////////////////////////////*/

  $go->necessary('registerid','newtag',array('wordid','allmarked')); //Error 2xx

  if($go->good()) {
    //forbidden characters / Verbotene Zeichen
    $forbidden= array('"', '#', '+');
    $newtag=str_replace($forbidden, '', $newtag);

    //every word is marked: get them by global parameters
    if($allmarked) { 
       $wordid=NULL;
       $params=getglobal();
       $params['nolimit']=1;
       $words=request('get_word', $params);
       if( $words['errnum']!=0 ) { $go->error(400,$words['errnum'].': '.$words['errname']); } //Error 400
       $wordid=$words['id'];
    }
  }

  if($go->good()) { //Check for every word if it belongs to the user
    if(!is_array($wordid)) { $wordid=array($wordid); }
    $len=count($wordid);
    for($i=0;$i<$len;$i++) {
      //checkuser
      $chquery="SELECT id FROM lk_words WHERE id='".$wordid[$i]."' AND userid='".$userid."'";
      $check_user=$go->query($chquery,1);
      if($check_user['count']==0) {
        $go->error(100); //Error 100
        break;
      }
    }
  }

  if($go->good()) {
    ////add tags
    //build query
    $tags_arr=explode(',', $newtag);
    $query="INSERT IGNORE INTO lk_tags (wordid, tagid) VALUES ";
    $ctags=count($tags_arr);		
    for($i=0;$i<$ctags;$i++) {  //for every tag
      $thistag=trim($tags_arr[$i]);
      if($thistag!='' && $thistag!=NULL) { //eliminate doubled commas
        //Check if tag already exists
        $interquery="SELECT id FROM lk_taglist WHERE name='".$thistag."' AND userid='".$userid."' AND registerid='".$registerid."'";
        $check_tag=$go->query($interquery,2);
        if($check_tag['count']>0) { 
          $tagid=intval( $check_tag['result']['id'][0] );     
        } else { //If not: create new one
          $interquery="INSERT INTO lk_taglist (userid, registerid, name) 
                       VALUES ('".$userid."', '".$registerid."', '".$thistag."') ";
          $create_tag=$go->query($interquery,3);
          $tagid=$create_tag['id'];	
        }
        //adds tag and tagid to the list of added tags
        $tagid_arr[$i]=$tagid; 
        $tags_arr[$i]=$thistag;
        //make list to add in query
        for($j=0;$j<$len;$j++) {
          $query .= "('".$wordid[$j]."', '".$tagid."')";
          if( $j!=$len-1 ) { $query .=" , "; }
        }
        if( $i!=$ctags-1 ) { $query .=" , "; }
      }
    }
    if($go->good()) {
      //Execute
      $add_tags=$go->query($query,4); //Error 304
    }  
  } 
  
  $return=array('counttag' => count($tagid_arr), //number of added tags
                'countword' => count($wordid),   //number of words
                'count' => $add_tags['count'],  //total number of assignments tag->word
                'wordid' => $wordid,            //ids of words
                'tags' => $tags_arr,            //names of tags as array
                'tagid' => $tagid_arr);         //ids of tags as array
?>
