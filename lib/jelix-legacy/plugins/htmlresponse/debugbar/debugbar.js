var jxdb = {
    plugins: {},
    init: function(event) {
        for (var i in jxdb.plugins)
            jxdb.plugins[i].init();
    },
    me : function () { return document.getElementById('jxdb');},
    close : function() { document.getElementById('jxdb').style.display="none";},
    selectTab : function(tabPanelId) {

        let panel = document.getElementById(tabPanelId);
        var close = !panel.classList.contains('jxdb-tabpanel-displayed');
        this.hideTab();
        if (close) {
            this.me().classList.add('jxdb-tabpanels-open');
            panel.classList.add('jxdb-tabpanel-displayed');
        }
    },
    hideTab :  function () {
        let panels = document.getElementById('jxdb-tabpanels')
        for (let elt of panels.children) {
            elt.classList.remove('jxdb-tabpanel-displayed');
        }
        this.me().classList.remove('jxdb-tabpanels-open');
    },
    moveTo: function(side) {
        let cssClass = this.me().classList;
        let newPos = 'jxdb-position-'+side;
        if (newPos != 'jxdb-position-r') {
            cssClass.remove('jxdb-position-r');
        }
        if (newPos != 'jxdb-position-c') {
            cssClass.remove('jxdb-position-c');
        }
        if (newPos != 'jxdb-position-l') {
            cssClass.remove('jxdb-position-l');
        }
        cssClass.add('jxdb-position-'+side);
        this.createCookie('jxdebugbarpos', side);
    },
    createCookie: function(name,value) {
        var date = new Date();
        date.setTime(date.getTime()+(7*24*60*60*1000));
        document.cookie = name+"="+value+"; expires="+date.toGMTString()+"; path=/";
    },
    toggleDetails : function(anchor) {
        var item = anchor.parentNode.parentNode;
        item.classList.toggle("jxdb-opened");
    }
};
if (window.addEventListener)
    window.addEventListener("load", jxdb.init, false);