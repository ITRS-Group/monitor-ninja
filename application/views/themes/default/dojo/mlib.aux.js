
if (!window.mlib) {
	window.mlib = {};
}

var u = mlib;

if (!Array.prototype.remove) {
	
	// Array Remove - By John Resig (MIT Licensed)
	
	Array.prototype.remove = function(from, to) {
	  var rest = this.slice((to || from) + 1 || this.length);
	  this.length = from < 0 ? this.length + from : from;
	  return this.push.apply(this, rest);
	};

}

if(!String.prototype.trim) {
  
  String.prototype.trim = function () {
    return this.replace(/^\s+|\s+$/g,'');
  };

}