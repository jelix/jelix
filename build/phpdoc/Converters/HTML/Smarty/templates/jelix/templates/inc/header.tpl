<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
	<title>{$title}</title>
	<link rel="stylesheet" type="text/css" href="{$subdir}media/page.css" media="screen"/>
	<link rel="stylesheet" type="text/css" href="{$subdir}media/print.css" media="print"/>
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
            <span class="bcsep">&raquo;</span> Package <strong>{$package}</strong>
            {if $subpackage neq ""}
            <span class="bcsep">&raquo;</span> {$subpackage} 
            {if $current neq ""}
            &middot; {$current}
            {/if}
            {/if}
            [ <a href="{$subdir}classtrees_{$package}.html">Class Tree</a> ]
            [ <a href="{$subdir}elementindex_{$package}.html">Index</a> ]
            [ <a href="{$subdir}elementindex.html">All elements</a> ]
        </div>
    </div>
    
    

{if !$hasel}{assign var="hasel" value=false}{/if}
{if $eltype == 'class' && $is_interface}{assign var="eltype" value="interface"}{/if}

<div id="main">
	{if $hasel}
	<h1>{$eltype|capitalize}: {$class_name}</h1>
        
	{/if}
