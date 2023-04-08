<script type="text/javascript" id="script-form"
        src="{$j_basepath}tests/sampleajaxform.js"
        data-url-show-form="{jurl 'testapp~sampleform:showajaxform'}"></script>

<h1>A sample Ajax form</h1>
<p>By clicking on the button, you'll see a form generated and
managed by jforms, and displayed via an ajax request.</p>

<button onclick="loadForm()">Load the form</button>

<div id="theform">
    
</div>
<div id="result">

</div>