function onResult(sub){
    if(sub.jsonResponse.result == 'OK')
        //window.open('/','main','chrome');
        window.location.href='/';
    else
        alert('login ou password erroné');


}

function onTheLoad(){
    document.getElementById('login').focus();
}


window.addEventListener('load', onTheLoad ,false);