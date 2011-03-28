var jxdb = {
    plugins: {},
    init: function(event) {
        for (var i in jxdb.plugins)
            jxdb.plugins[i].init();
        var pos = jxdb.readCookie('jxdebugbarpos');
        if (pos) jxdb.moveTo(pos);
    },
    me : function () { return document.getElementById('jxdb');},
    close : function() { document.getElementById('jxdb').style.display="none";},
    selectTab : function(tabPanelId) {

        var close = (document.getElementById(tabPanelId).style.display == 'block');
        this.hideTab();
        /*var tabs = linktab.parentNode.parentNode.childNodes;
        for(var i=0; i <tabs.length; i++) {
            var elt = tabs[i];
            if (elt.nodeType == elt.ELEMENT_NODE) {
                elt.removeAttribute('class');
            }
        }*/
        if (!close) {
            document.getElementById('jxdb-tabpanels').style.display = 'block';
            document.getElementById(tabPanelId).style.display='block';
        }
        /*linktab.parentNode.setAttribute('class', 'jxdb-selected');*/
    },
    hideTab :  function () {
        var panels = document.getElementById('jxdb-tabpanels').childNodes;
        for(var i=0; i < panels.length; i++) {
            var elt = panels[i];
            if (elt.nodeType == elt.ELEMENT_NODE) {
                elt.style.display = 'none';
            }
        }
        document.getElementById('jxdb-tabpanels').style.display = 'none';
    },
    moveTo: function(side) {
        document.getElementById('jxdb').setAttribute('class', 'jxdb-position-'+side);
        this.createCookie('jxdebugbarpos', side);
    },
    createCookie: function(name,value) {
		var date = new Date();
		date.setTime(date.getTime()+(7*24*60*60*1000));
    	document.cookie = name+"="+value+"; expires="+date.toGMTString()+"; path=/";
    },

    readCookie : function(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i].replace(/^\s\s*/, '').replace(/\s\s*$/, '');
            if (c.indexOf(nameEQ) == 0)
                return c.substring(nameEQ.length,c.length);
        }
        return null;
    },
    toggleDetails : function(anchor) {
        var item = anchor.parentNode.parentNode;
        var cssclass = item.getAttribute('class');
		if(cssclass == null)
			cssclass = '';
        if (cssclass.indexOf('jxdb-opened') == -1) {
            item.setAttribute('class', cssclass+" jxdb-opened");
            item.childNodes[3].style.display = 'block';
        }
        else {
            item.setAttribute('class', cssclass.replace("jxdb-opened",''));
            item.childNodes[3].style.display = 'none';
        }
    }
};
if (window.addEventListener)
    window.addEventListener("load", jxdb.init, false);