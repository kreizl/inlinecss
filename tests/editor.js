// JavaScript Document

/**
 * Save differences in document
 * Every change in document create Diff object.
 * History can work thath the every step back or forward will only load from diff object
 **/
document.addEventListener('DOMContentLoaded', function(){
  console.log('DOM Loaded');
  var e = document.getElementById("editor"),
      diff = {
        'timestamp':null,
        'content':null,
        'position':null,
      },
      diffs = [];

  textLength = e.textLength;
  console.log(textLength);
  e.addEventListener('select', function(e){
    console.log(e, this.selectionStart, this.selectionEnd);
  });

  e.addEventListener('keyup', function(e){

    //console.log({'o':this, 's':this.value.substr(this.textLength-1, 20)});
    console.log(this.selectionStart, this.selectionEnd);
  });

  function registerDiff(document, ev)
  {
    /*diffs.push({
      'timestamp' : Date.now(),
      'content' : document.
      'position' : document.
    });*/
  }

});