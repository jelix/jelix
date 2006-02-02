<?php

class URLSurlsig implements jIUrlSignificantHandler {

    // exemple de handler.
    // ici les traitements sont simples, c'est juste pour montrer le principe

    // on peut utiliser le même handler pour plusieurs actions
    // il suffit de tester les parametres de l'objet url

    function parse($url){
        if(preg_match("/^\/withhandler\/(.*)\/(.*)$/",$url->pathInfo,$match)){

            $url->pathInfo='';
            $url->setParam('first',$match[1]);
            $url->setParam('second',$match[2]);
            return true;
        }else
            return false;
    }

    function create($url){

        $f=$url->getParam('first');
        $s=$url->getParam('second');

        $url->pathInfo = "/withhandler/$f/$s";

        $url->delParam('first');
        $url->delParam('second');
    }
}

?>