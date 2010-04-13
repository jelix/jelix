<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang}" lang="{$lang}">
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
    <title>Installation Wizard</title>

    <style type="text/css">{literal}
    body {font-family: verdana, sans-serif;}

    ul.checkresults {
        border:3px solid black;
        margin: 2em;
        padding:1em;
        list-style-type:none;
    }
    ul.checkresults li { margin:0; padding:5px; border-top:1px solid black; }
    li.checkerror   { background-color:#ff6666;}
    li.checkok      { background-color:#a4ffa9;}
    li.checkwarning { background-color:#ffbc8f;}
    
    .error  { background-color:#ff6666;}
    {/literal}</style>

</head>

<body >
    <h1>{@maintitle@}</h1>

    <div id="main">
      <form action="install.php" {if $enctype}enctype="{$enctype}"{/if} method="post">
        <div>
          <input type="hidden" name="step" value="{$stepname}" />
          <input type="hidden" name="doprocess" value="1" />
        </div>
        <div id="content">
        {$MAIN}
        </div>
        <div id="buttons">
          {if $previous}
            <button name="previous" onclick="location.href='install.php?step={$previous}';return false;">{@previousLabel@|eschtml}</button>
          {/if}
          {if $next}
            <button type="submit">{@nextLabel@|eschtml}</button>
          {/if}
        </div>
      </form>
    </div>

</body>
</html>