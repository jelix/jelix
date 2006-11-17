
function  OpenAppli(url){
    var content = document.getElementById('content');
    if(content.getAttribute('src') == url){
        content.setAttribute('src','');
        content.setAttribute('src',url);
    }else{
        content.setAttribute('src',url);
    }
}

