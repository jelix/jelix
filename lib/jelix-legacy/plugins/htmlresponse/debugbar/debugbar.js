var jxdb = {
    plugins: {},
    init: function(event) {
        for (var i in jxdb.plugins)
            jxdb.plugins[i].init();
    },
    me : function () { return document.getElementById('jxdb');},
    close : function() { document.getElementById('jxdb').style.display="none";},
    selectTab : function(tabPanelId) {

        var close = (document.getElementById(tabPanelId).style.display == 'block');
        this.hideTab();
        if (!close) {
            document.getElementById('jxdb-tabpanels').style.display = 'block';
            document.getElementById(tabPanelId).style.display='block';
        }
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