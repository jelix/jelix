
function displayStatusMessage(str){
    var st = document.getElementById('statusmessage').value=str;
}


function OpenAppli(url){
    displayStatusMessage(url);
    var mainContent = document.getElementById('mainContent');
    if(mainContent.getAttribute('src') == url){
        mainContent.setAttribute('src','');
        mainContent.setAttribute('src',url);
    }else{
        mainContent.setAttribute('src',url);
    }
}

function XulAppOnLoad(ev){
  // pour le bug du load qui se propage au fenêtre parentes..
  if(ev.target != document)
    return;
}

document.addEventListener("load", XulAppOnLoad, false);

function CmdxQuit(){
    if(confirm("Étes vous sûr de vouloir quitter l'application ?"))
          window.location.href= gUrlQuit;
}
