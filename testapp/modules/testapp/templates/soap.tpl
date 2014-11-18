<h1>getServerDate()</h1>
<p>{$getServerDate}</p>

<h1>hello('sylvain')</h1>
<p>{$hello}</p>

<h1>concatString('Hi ! ', 'Sylvain', 'How are you ?')</h1>
<p>{$concatString}</p>


<h1>concatArray(array('Hi ! ', 'Sylvain', 'How are you ?'))</h1>
<p>{$concatArray}</p>


<h1>concatAssociativeArray(array('arg1'=>'Hi ! ', 'arg2'=>'Sylvain', 'arg3'=>'How are you ?'))</h1>
<p>{$concatAssociativeArray}</p>


<h1>returnAssociativeArray()</h1>
<p>
arg1 : {$returnAssociativeArray['arg1']}<br/>
arg2 : {$returnAssociativeArray['arg2']}<br/>
arg3 : {$returnAssociativeArray['arg3']}<br/>
</p>

<h1>returnObject()</h1>
<p>
Name : {$returnObject->name}<br/>
FirstName : {$returnObject->firstName}<br/>
City : {$returnObject->city}<br/>
</p>

<h1>receiveObject($returnObject)</h1>
<p>
Name : {$receiveObject->name}<br/>
FirstName : {$receiveObject->firstName}<br/>
City : {$receiveObject->city}<br/>
</p>


<h1>returnObjects()</h1>
<p>
{foreach $returnObjects as $object}
	<strong>Object</strong><br/>
	Name : {$object->name}<br/>
	FirstName : {$object->firstName}<br/>
	City : {$object->city}<br/>
{/foreach}
</p>

<h1>returnAssociativeArrayOfObjects()</h1>
<p>
{for $i=1;$i<=3;$i++}
	<strong>$returnAssociativeArrayOfObjects['arg{$i}']</strong><br/>
	Name : {$returnAssociativeArrayOfObjects['arg'.$i]->name}<br/>
	FirstName : {$returnAssociativeArrayOfObjects['arg'.$i]->firstName}<br/>
	City : {$returnAssociativeArrayOfObjects['arg'.$i]->city}<br/>
{/for}
</p>
<pre>
    {$returnAssociativeArrayOfObjectsEXPORT|eschtml}
</pre>

<h1>returnObjectBis()</h1>
<p>Msg : {$returnObjectBis->msg}<br/>
Name : {$returnObjectBis->test->name}<br/>
FirstName : {$returnObjectBis->test->firstName}<br/>
City : {$returnObjectBis->test->city}<br/>
</p>

<h1>returnCircularReference()</h1>
<p>
{foreach $returnCircularReference as $object}
	<strong>{$object->msg}</strong><br/>
	$object->test->msg : {$object->test->msg}<br/>
	$object->test->test->msg : {$object->test->test->msg}<br/>
{/foreach}
</p>

<p><a href="{jurl 'main:index'}">Retour au sommaire</a></p>

