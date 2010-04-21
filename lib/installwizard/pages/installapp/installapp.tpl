
<ul class="checkresults">
  {foreach $messages as $msg}
  <li class="check{$msg[0]}">{$msg[1]}</li>
  {/foreach}
</ul>