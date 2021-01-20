var browser = {
  chrome: false,
  mozilla: false,
  opera: false,
  msie: false,
  safari: false
};
var sUsrAg = navigator.userAgent;
if(sUsrAg.indexOf("Chrome") > -1) {
  browser.chrome = true;
} else if (sUsrAg.indexOf("Safari") > -1) {
  browser.safari = true;
} else if (sUsrAg.indexOf("Opera") > -1) {
  browser.opera = true;
} else if (sUsrAg.indexOf("Firefox") > -1) {
  browser.mozilla = true;
} else if (sUsrAg.indexOf("MSIE") > -1) {
  browser.msie = true;
}