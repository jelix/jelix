<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
    <title>Installation Wizard</title>

    <style type="text/css">{literal}
#includeraw ../jelix-www/design/jelix.css
   #buttons { margin: 0 auto; width: 924px; text-align:center}
    {/literal}</style>

</head>

<body >
    <h1 class="apptitle">{$appname} <br/><span class="welcome">{@maintitle@}</span></h1>

    <div id="main">
      <form action="install.php" {if $enctype}enctype="{$enctype}"{/if} method="post">
        <div>
          <input type="hidden" name="step" value="{$stepname}" />
          <input type="hidden" name="doprocess" value="1" />
        </div>
        <div id="page">
          <div class="block">
            <h2>{$title|eschtml}</h2>
            <div class="blockcontent">
            {if $messageHeader}<div id="contentheader">{@$messageHeader@|eschtml}</div>{/if}
            {$MAIN}
            {if $messageFooter}<div id="contentFooter">{@$messageFooter@|eschtml}</div>{/if}
            </div>
          </div>
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