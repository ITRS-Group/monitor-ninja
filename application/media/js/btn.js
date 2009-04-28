var btn = {
    init : function() {
        if (!document.getElementById || !document.createElement || !document.appendChild) return false;
        as = btn.getElementsByClassName('btn(.*)');
        for (i=0; i<as.length; i++) {
            if ( as[i].tagName == "INPUT" && ( as[i].type.toLowerCase() == "submit" || as[i].type.toLowerCase() == "button" ) ) {
                var a1 = document.createElement("a");
                a1.appendChild(document.createTextNode(as[i].value));
                a1.className = as[i].className;
                a1.id = as[i].id;
                as[i] = as[i].parentNode.replaceChild(a1, as[i]);
                as[i] = a1;
                as[i].style.cursor = "pointer";
            }
            else if (as[i].tagName == "A") {
                var tt = as[i].childNodes;
            }
            else { return false };
            var i1 = document.createElement('i');
            var i2 = document.createElement('i');
            var s1 = document.createElement('span');
            var s2 = document.createElement('span');
            s1.appendChild(i1);
            s1.appendChild(s2);
            while (as[i].firstChild) {
              s1.appendChild(as[i].firstChild);
            }
            as[i].appendChild(s1);
            as[i] = as[i].insertBefore(i2, s1);
        }
        // The following lines submits the form if the button id is "submit_btn"
        btn.addEvent(document.getElementById('submit_btn'),'click',function() {
            var form = btn.findForm(this);
            form.submit();
        });
        // The following lines resets the form if the button id is "reset_btn"
        btn.addEvent(document.getElementById('reset_btn'),'click',function() {
            var form = btn.findForm(this);
            form.reset();
        });
    },
    findForm : function(f) {
        while(f.tagName != "FORM") {
            f = f.parentNode;
        }
        return f;
    },
    addEvent : function(obj, type, fn) {
        if (obj.addEventListener) {
            obj.addEventListener(type, fn, false);
        }
        else if (obj.attachEvent) {
            obj["e"+type+fn] = fn;
            obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
            obj.attachEvent("on"+type, obj[type+fn]);
        }
    },
    getElementsByClassName : function(className, tag, elm) {
        var testClass = new RegExp("(^|\s)" + className + "(\s|$)");
        var tag = tag || "*";
        var elm = elm || document;
        var elements = (tag == "*" && elm.all)? elm.all : elm.getElementsByTagName(tag);
        var returnElements = [];
        var current;
        var length = elements.length;
        for(var i=0; i<length; i++){
            current = elements[i];
            if(testClass.test(current.className)){
                returnElements.push(current);
            }
        }
        return returnElements;
    }
}

btn.addEvent(window,'load', function() { btn.init();} );