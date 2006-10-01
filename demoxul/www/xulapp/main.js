


function CmdxQuit(){
    if(confirm("Étes vous sûr de vouloir quitter l'application ?"))
          window.location.href="/index?module=auth&action=login_out";
}





var gMainController = {
    _commands : {'cmdx_quit':true},
    supportsCommand : function (cmd) {
        return (cmd in this._commands);
    },
    isCommandEnabled : function (cmd) {
        return this._commands[cmd];
    },
    doCommand : function (cmd) {
        if(cmd == 'cmdx_quit'){
            if(window.prompter.confirm("Quitter","Étes vous sûr de vouloir quitter l'application ?"))
                window.location.href="/index?module=auth&action=login_out";
        }
    },
    onEvent : function (eventName) { }
}



var gXulAppOnLoadDone = false;
function XulAppOnLoad(){
  // pour le bug du load qui se propage au fenêtre parentes..
  if(gXulAppOnLoadDone) return;
  gXulAppOnLoadDone = true;

}

document.addEventListener("load", XulAppOnLoad, false);

//window.controllers.appendController(gMainController);



function  OpenAppli(url){
    var content = document.getElementById('content');
    if(content.getAttribute('src') == url){
        content.setAttribute('src','');
        content.setAttribute('src',url);
    }else{
        content.setAttribute('src',url);
    }
}

