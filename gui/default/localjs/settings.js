//////////
//LOAD

function change_settings() {
  //shutter
  do_shutter(1);

  //serialize
  var settings = $('settings_form').serialize(true);

  //send
  plk.req('change_options', settings, function(info,params) {

    if( info.errnum == 0 ) {
      //notify
      //do_info(plk.la.info_changedsettings);
      //reload
      location.reload();
    } else {
      //error occured
      if( plk.la[ 'err_' + info.errnum ] ) {
        do_info( plk.la[ 'err_' + info.errnum ] );
      }
    }

  });  
  
}

function change_password() {
  //shutter
  do_shutter(1);

  //serialize
  var password = $('password_form').serialize(true);

  //evaluate
  var passerr;
  if( !password.newpassword ) { passerr = plk.la.err_234; }
  else if( password.newpassword != password.checkpassword ) { passerr = plk.la.err_password; }

  //notify  
  if( passerr ) { 
    do_info( passerr ); 
    close_shutter();
    return false; 
  } //stop

  //send if no error
  plk.req('change_password', password, function(info,params) {

    if( info.success != 1 ) { //nothing changed
      do_info( plk.la.err_wrongpass );
      $('password_form').oldpassword.focus();
    } else if( info.errnum == 0 ) {
      //notify
      do_info(plk.la.info_settings_changed);
    } else {
      //error occured
      if( plk.la[ 'err_'+info.errnum ] ) {
        do_info( plk.la[ 'err_'+info.errnum ] );
      }
    }
  
    close_shutter();

  });
}
