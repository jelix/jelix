<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Jelix : API Documentation</title>
        <link rel="stylesheet" type="text/css" href="{$subdir}media/page.css" media="screen"/>
	<link rel="stylesheet" type="text/css" href="{$subdir}media/print.css" media="print"/>
        <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'/>
    </head>
    <body>
        <div id="header">

            <div id="introduction">
                <h1><a href="{$subdir}index.html" >Documentation API Reference</a></h1>
            </div>
            <div id="telechargement">


            </div>
            <div>
            </div>
            <div id="chemin">
                {if count($packages) > 1}
                   {assign var="last_p" value=""}
                <span class="field">Packages</span> :
                    {section name=p loop=$packages}
                        {if $last_p != ""} | {/if}
                        <a href="{$packages[p].link}" target="left_bottom">{$packages[p].title}</a>
                        {assign var="last_p" value=$packages[p].title}
                    {/section}
                <br/>
                {/if}

                {if count($ric) >= 1}
                    {assign var="last_ric_name" value=""}
                    {section name=ric loop=$ric}
                        {if $last_ric_name != ""} | {/if}
                        <a href="{$ric[ric].file}" target="right">{$ric[ric].name}</a>
                        {assign var="last_ric_name" value=$ric[ric].name}
                    {/section}
                {/if}

            </div>
    </div>

    <div id="sidemenu">
        <h1><a href="http://jelix.org" target="_top" title="retour sur la page d'accueil du site"><img src="{$subdir}media/logo_jelix_moyen.png" alt="Jelix" /></a></h1>
    </div>

    </body>
</html>
