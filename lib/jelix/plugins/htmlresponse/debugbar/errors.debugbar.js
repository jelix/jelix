jxdb.plugins.errors = {
    init: function() {
    },
    toggleError : function(anchor) {
        var item = anchor.parentNode.parentNode;
        var cssclass = item.getAttribute('class');
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