//////////
//IMPORT
//////////

//enable or disable all checkbuttons with a class
function ableall(classname,enable) {
  var i=0;
  var npts=$$('input.im_'+classname);
  while(npts[i]) {
    npts[i][enable==1?'enable':'disable']();
    i++;
  }
  i=0;
  var flds=$$('fieldset.im_'+classname);
  while(flds[i]) {
    flds[i][enable==1?'show':'hide']();
    i++;
  }
}
