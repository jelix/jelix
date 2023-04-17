
 <h2 id="userAgent"></h2>

 <div id="qunit"></div>
 <div id="qunit-fixture"></div>
 

 <hr />
 <p>Test form, don't use it.</p>
 
 <form action="/index.php" method="post" id="jf" enctype="multipart/form-data">
<table class="jforms-table" border="0"><tr><td colspan="2"><fieldset><legend>Your identity</legend>
<table class="jforms-table-group" border="0">
<tr><th scope="row"><label class="jforms-label jforms-required" for="jf_nom">Your name</label></th>
    <td><input type="text" name="nom" id="jf_nom" class=" jforms-required" value=""></td></tr>

<tr><th scope="row"><span class="jforms-label jforms-required">You are </span></th>
    <td><span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="jf_sexe_0" value="h" class=" jforms-required"><label for="jf_sexe_0">a man</label></span>
    <span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="jf_sexe_1" value="f" class=" jforms-required"><label for="jf_sexe_1">a woman</label></span>
    <span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="jf_sexe_2" value="no" class=" jforms-required"><label for="jf_sexe_2">you don't know</label></span>
    </td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_mail">Your email</label></th>
    <td><input type="text" name="mail" id="jf_mail" value=""></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_geek" title="Check the box if you are a geek">Are you a geek ?</label></th>
    <td><input type="checkbox" name="geek" id="jf_geek" title="Check the box if you are a geek" value="1"><span class="jforms-help">A geek is a person which is extremely keen on computer science</span></td></tr>

</table></fieldset></td></tr>


<tr><th scope="row"><label class="jforms-label" for="jf_conf">Select one value</label></th>
  <td><select name="conf" id="jf_conf" size="1">
        <option value="" selected="selected">-- choices --</option>
        <option value="foo">foovalue</option>
        <option value="bar">barvalue</option>
        <option value="name">laurent</option>
        <option value="engine">jelix</option>
        <option value="browser">firefox</option>
        <option value="33">456ghjk</option>
        <option value="test">33</option>
        <option value="ddd">ffff</option>
  </select></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_home">You leave at</label></th>
<td><select name="home" id="jf_home" size="4">
<option value="pa">Paris</option>
<option value="ma">Marseille</option>
<option value="ly">Lyon</option>
<option value="br">Brest</option>
<option value="li">Lille</option>
<option value="bo">Bordeaux</option>
</select></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_address">Your address</label></th>
<td><textarea name="address" id="jf_address" rows="5" cols="40"></textarea></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_description">Description in html</label></th>
 <td><textarea name="description" id="jf_description" rows="5" cols="40"></textarea></td></tr>

<tr><th scope="row"><span class="jforms-label jforms-required">You have </span></th>
<td><span class="jforms-chkbox jforms-ctl-objets">
<input type="checkbox" name="objets[]" id="jf_objets_0" value="maison" class=" jforms-required"><label for="jf_objets_0">a house</label></span>
<span class="jforms-chkbox jforms-ctl-objets"><input type="checkbox" name="objets[]" id="jf_objets_1" value="voiture" class=" jforms-required"><label for="jf_objets_1">a car</label></span>
<span class="jforms-chkbox jforms-ctl-objets"><input type="checkbox" name="objets[]" id="jf_objets_2" value="bateau" class=" jforms-required"><label for="jf_objets_2">a boat</label></span>
<span class="jforms-chkbox jforms-ctl-objets"><input type="checkbox" name="objets[]" id="jf_objets_3" value="assiette" class=" jforms-required"><label for="jf_objets_3">a plate</label></span></td>
</tr>

<tr><th scope="row"><span class="jforms-label">Your birthday</span></th>
<td><select id="jf_datenaissance_month" name="datenaissance[month]">
<option value="">Month</option>
<option value="01">January</option>
<option value="02">February</option>
<option value="03">March</option>
<option value="04">April</option>
<option value="05">May</option>
<option value="06">June</option>
<option value="07">July</option>
<option value="08">August</option>
<option value="09">September</option>
<option value="10">October</option>
<option value="11">November</option>
<option value="12">December</option>
</select>

<select id="jf_datenaissance_day" name="datenaissance[day]">
    <option value="">Day</option><option value="01">01</option>
    <option value="02">02</option><option value="03">03</option><option value="04">04</option>
    <option value="05">05</option><option value="06">06</option><option value="07">07</option>
    <option value="08">08</option><option value="09">09</option><option value="10">10</option>
    <option value="11">11</option><option value="12">12</option><option value="13">13</option>
    <option value="14">14</option><option value="15">15</option><option value="16">16</option>
    <option value="17">17</option><option value="18">18</option><option value="19">19</option>
    <option value="20">20</option><option value="21">21</option><option value="22">22</option>
    <option value="23">23</option><option value="24">24</option><option value="25">25</option>
    <option value="26">26</option><option value="27">27</option><option value="28">28</option>
    <option value="29">29</option><option value="30">30</option><option value="31">31</option>
</select>

<input type="text" size="4" maxlength="4" id="jf_datenaissance_year" name="datenaissance[year]" value=""></td></tr>



<tr><th scope="row"><span class="jforms-label">Next meeting</span></th>
<td>
  <select id="jf_meeting_month" name="meeting[month]">
    <option value="">Month</option>
    <option value="01">January</option>
    <option value="02">February</option>
    <option value="03">March</option>
    <option value="04">April</option>
    <option value="05">May</option>
    <option value="06">June</option>
    <option value="07">July</option>
    <option value="08">August</option>
    <option value="09">September</option>
    <option value="10">October</option>
    <option value="11">November</option>
    <option value="12">December</option>
  </select>
  <select id="jf_meeting_day" name="meeting[day]">
    <option value="">Day</option><option value="01">01</option><option value="02">02</option>
    <option value="03">03</option><option value="04">04</option><option value="05">05</option>
    <option value="06">06</option><option value="07">07</option><option value="08">08</option>
    <option value="09">09</option><option value="10">10</option><option value="11">11</option>
    <option value="12">12</option><option value="13">13</option><option value="14">14</option>
    <option value="15">15</option><option value="16">16</option><option value="17">17</option>
    <option value="18">18</option><option value="19">19</option><option value="20">20</option>
    <option value="21">21</option><option value="22">22</option><option value="23">23</option>
    <option value="24">24</option><option value="25">25</option><option value="26">26</option>
    <option value="27">27</option><option value="28">28</option><option value="29">29</option>
    <option value="30">30</option><option value="31">31</option>
  </select>
  <input type="text" size="4" maxlength="4" id="jf_meeting_year" name="meeting[year]" value=""/>
  
  <select id="jf_meeting_hour" name="meeting[hour]">
    <option value="">Hour</option><option value="00">00</option>
    <option value="01">01</option><option value="02">02</option>
    <option value="03">03</option><option value="04">04</option>
    <option value="05">05</option><option value="06">06</option>
    <option value="07">07</option><option value="08">08</option>
    <option value="09">09</option><option value="10">10</option>
    <option value="11">11</option><option value="12">12</option>
    <option value="13">13</option><option value="14">14</option>
    <option value="15">15</option><option value="16">16</option><option value="17">17</option>
    <option value="18">18</option><option value="19">19</option><option value="20">20</option>
    <option value="21">21</option><option value="22">22</option><option value="23">23</option>
  </select>
  
  <select id="jf_meeting_minutes" name="meeting[minutes]">
    <option value="">Minutes</option><option value="00">00</option><option value="01">01</option>
    <option value="02">02</option><option value="03">03</option><option value="04">04</option>
    <option value="05">05</option><option value="06">06</option><option value="07">07</option>
    <option value="08">08</option><option value="09">09</option><option value="10">10</option>
    <option value="11">11</option><option value="12">12</option><option value="13">13</option>
    <option value="14">14</option><option value="15">15</option><option value="16">16</option>
    <option value="17">17</option><option value="18">18</option><option value="19">19</option>
    <option value="20">20</option><option value="21">21</option><option value="22">22</option>
    <option value="23">23</option><option value="24">24</option><option value="25">25</option>
    <option value="26">26</option><option value="27">27</option><option value="28">28</option>
    <option value="29">29</option><option value="30">30</option><option value="31">31</option>
    <option value="32">32</option><option value="33">33</option><option value="34">34</option>
    <option value="35">35</option><option value="36">36</option><option value="37">37</option>
    <option value="38">38</option><option value="39">39</option><option value="40">40</option>
    <option value="41">41</option><option value="42">42</option><option value="43">43</option>
    <option value="44">44</option><option value="45">45</option><option value="46">46</option>
    <option value="47">47</option><option value="48">48</option><option value="49">49</option>
    <option value="50">50</option><option value="51">51</option><option value="52">52</option>
    <option value="53">53</option><option value="54">54</option><option value="55">55</option>
    <option value="56">56</option><option value="57">57</option><option value="58">58</option>
    <option value="59">59</option></select>
  
  <input type="hidden" id="jf_meeting_seconds" name="meeting[seconds]" value=""/>
</td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_pwd">A password</label></th>
<td><input type="password" name="pwd" id="jf_pwd" value=""></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_pwd_confirm">Type again the password to confirm</label></th>
<td><input type="password" name="pwd_confirm" id="jf_pwd_confirm" value=""></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_image">Your photo (gif/png &lt; 50ko)</label></th>
<td><input type="hidden" name="MAX_FILE_SIZE" value="50000"><input type="file" name="image" id="jf_image" value=""></td></tr>

<tr><th scope="row"><label class="jforms-label jforms-required" for="jf_unwanted">This field shouldn't appear, it is deactivated</label></th>
<td><input type="text" name="unwanted" id="jf_unwanted" class=" jforms-required" value=""></td></tr>

<tr><th scope="row"><label class="jforms-label" for="jf_task">Task status</label></th>
<td><ul class="jforms-choice jforms-ctl-task" >

<li><label><input type="radio" name="task" id="jf_task_0" value="new" onclick="jFormsJQ.getForm('jforms_jelix_tests_formtest').getControl('task').activate('new')">New</label></li>

<li><label><input type="radio" name="task" id="jf_task_1" value="assigned" onclick="jFormsJQ.getForm('jforms_jelix_tests_formtest').getControl('task').activate('assigned')">Assigned</label>
<span class="jforms-item-controls"><label class="jforms-label jforms-required" for="jf_assignee">assignee name</label>
<input type="text" name="assignee" id="jf_assignee" class=" jforms-required" value=""></span>
</li>

<li><label><input type="radio" name="task" id="jf_task_2" value="closed" onclick="jFormsJQ.getForm('jforms_jelix_tests_formtest').getControl('task').activate('closed')">Closed</label>
<span class="jforms-item-controls"><label class="jforms-label" for="jf_task-done">Status</label>
<select name="task-done" id="jf_task-done" size="1">
<option value="" selected="selected"></option><option value="done">Done</option>
<option value="cancelled">Cancelled</option><option value="later">Later</option></select></span>
</li>
</ul>
</td></tr>

<tr><th scope="row"><label class="jforms-label jforms-required" for="jf_cap">Antispam filter</label></th>
<td><span class="jforms-captcha-question">How much do 5 plus 3</span> <input type="text" name="cap" id="jf_cap" class=" jforms-required" value=""></td></tr></table>

<div class="jforms-submit-buttons">
    <button type="reset" name="cancel" id="jf_cancel" class="jforms-reset">Cancel</button>
<input type="submit" name="valid" id="jf_valid_svg" class="jforms-submit" value="Save"/>
<input type="submit" name="valid" id="jf_valid_prev" class="jforms-submit" value="Preview"/>

</div>
{literal}
<script type="text/javascript">
//<![CDATA[
/*

c = new jFormsJQControlEmail('mail', 'Your email');
c.errInvalid='"Your email" field is invalid';
jFormsJQ.tForm.addControl(c);

c = new jFormsJQControlString('conf', 'Select one value');
c.errInvalid='"Select one value" field is invalid';
jFormsJQ.tForm.addControl(c);

c = new jFormsJQControlString('home', 'You leave at');
c.errInvalid='"You leave at" field is invalid';
jFormsJQ.tForm.addControl(c);

c = new jFormsJQControlString('address', 'Your address');
c.errInvalid='"Your address" field is invalid';
jFormsJQ.tForm.addControl(c);

c = new jFormsJQControlString('description', 'Description in html');
c.errInvalid='"Description in html" field is invalid';
jFormsJQ.tForm.addControl(c);
jelix_ckeditor_ckdefault("jf_description","jforms_jelix_tests_formtest");

c = new jFormsJQControlString('objets[]', 'You have ');
c.required = true;
c.errRequired='"You have " field is required';
c.errInvalid='"You have " field is invalid';
jFormsJQ.tForm.addControl(c);

c = new jFormsJQControlDate('datenaissance', 'Your birthday');
c.multiFields = true;
c.errInvalid='"Your birthday" field is invalid';
jelix_datepicker_default(c,'en_US','/');
jFormsJQ.tForm.addControl(c);



c = new jFormsJQControlString('image', 'Your photo (gif/png < 50ko)');
c.errInvalid='The file should be a gif or png image, under 50000 octets';
jFormsJQ.tForm.addControl(c);
c = new jFormsJQControlDatetime('unwanted', 'This field shouldn\'t appear, it is deactivated');
c.required = true;
c.errRequired='"This field shouldn\'t appear, it is deactivated" field is required';
c.errInvalid='"This field shouldn\'t appear, it is deactivated" field is invalid';
jFormsJQ.tForm.addControl(c);
c = new jFormsJQControlChoice('task', 'Task status');
c.errInvalid='"Task status" field is invalid';
jFormsJQ.tForm.addControl(c);
c2 = c;
c2.items['new']=[];
c = new jFormsJQControlString('assignee', 'assignee name');
c.required = true;
c.errRequired='"assignee name" field is required';
c.errInvalid='"assignee name" field is invalid';
c2.addControl(c, 'assigned');
c = new jFormsJQControlString('task-done', 'Status');
c.errInvalid='"Status" field is invalid';
c2.addControl(c, 'closed');
c2.activate('');
c = new jFormsJQControlString('cap', 'Antispam filter');
c.required = true;
c.errRequired='"Antispam filter" field is required';
c.errInvalid='"Antispam filter" field is invalid';
jFormsJQ.tForm.addControl(c);

})();*/
//]]>
</script>{/literal}</form> 

  <form action="/index.php" method="post" id="jf2">
    
  </form>
 
