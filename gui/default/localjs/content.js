///////////////////////////
// functions in (mainly) static content

//////////
//INITIALIZE
//////////

function initializer_content() {
  //login
  var login_form = $('login_form');
  if( login_form ) { login_form.onsubmit = function() { return validate_login_form(); }; }

  //nregister
  var nregister_form = $('nregister_form');
  if( nregister_form ) { nregister_form.onsubmit = function() { return validate_nregister_form(); }; }

  //forgot password
  var forgot_form = $('forgot_form');
  if( forgot_form ) { forgot_form.onsubmit = function() { return validate_forgot_form(); }; }
}

//////////
//VALIDATE
//////////

//login
function validate_login_form(pform) {
  var form = pform || $('login_form');
  if ( !form ) { return false; }

  if ( !form.username.value ) {
    do_info(plk.la.err_237); //missing username
    return false;
  } else if( !form.password.value ) {
    do_info(plk.la.err_234); //missing password
    return false;
  }
  
  //everything's ok
  return true;
}

//nregister
function validate_nregister_form() {
  var form = $('nregister_form');
  if( !form ) { return false; }

  if ( !form.email.value ) {
    do_info(plk.la.err_238); //missing email
    return false;
  } else if( !form.passwordrepeat.value ) {
    do_info(plk.la.err_236); //missing password
    return false;
  } else if( form.passwordrepeat.value !== form.password.value ) {
    do_info(plk.la.err_password); //passwords not matching
    return false;    
  } else if( !form.accept.checked ) {
    do_info(plk.la.err_accept); //must accept agreements
    return false;  
  }

  //also check username and password
  return validate_login_form( form );
}

//forgot password
function validate_forgot_form() {
  var form = $('forgot_form');
  if( !form ) { return false; }

  if ( !form.email.value && !form.username.value ) {
    do_info(plk.la.err_200); //missing something
    return false;
  } 
  
  return true;
}

