var win = function (window) {return document.getElementById(window)};
 function hideAutGou()
  {
    document.getElementById('autBodyLoad').style.display = 'block';
    document.getElementById('autBody').style.display = 'none';
    autScrGo();
  }
 var GlobAutClass = function () 
 {
 this.marginTop = 0;
 this.Height = 0;
 this.marginLeft = 0;
 this.Width = 0;
 this.autObj = win('autGlob');
 this.autObj.body = win('autBody');
 this.autExtObj = win('techwork_over');
 }
 
 GlobAutClass.prototype = {
 displayAut: function () {
 with (this.autObj.style) {
 visibility = 'hidden';
 marginTop = 0 + 'px';
 marginLeft = 0 + 'px';
 display = 'block';
 width = 300 + 'px';
 this.h = this.autObj.clientHeight; 
 this.w = this.autObj.clientWidth;
 width = 0 + 'px';
 height = 0 + 'px';
 visibility = 'visible';
 this.changeWidth(3);
 }
 with (this.autExtObj.style) {display = 'block'; visibility = 'visible';}
 },
 
 changeHeight: function (y) 
{
 this.Height +=y;
 this.autObj.style.height = this.Height + 'px';
 this.autObj.style.marginTop = -Math.round(this.Height/2) + 'px';
 var tObj = this;
 var tFunc = function () {tObj.changeHeight(4)};
 if (this.Height < this.h) setTimeout(tFunc, 0); else {
 this.autObj.style.height = this.h + 'px';
 this.Height = 0;}
 },
 
 changeWidth: function (x) 
{
 this.Width +=x;
 this.autObj.style.width = this.Width + 'px';
 this.autObj.style.marginLeft = -Math.round(this.Width/2) + 'px';
 var tObj = this;
 var tFunc = function () {tObj.changeWidth(4)};
 if (this.Width <= this.w) setTimeout(tFunc, 0); else {
 this.autObj.style.width = this.w + 'px';
 this.changeHeight(3);
 this.Width = 0;}
 },
 
 hideAut: function () {
 this.autObj.style.display = 'none';
 this.autExtObj.style.display = 'none'; 
 visibility.style.display = 'hidden';
 } 
 }
 
 AutGo = new GlobAutClass();