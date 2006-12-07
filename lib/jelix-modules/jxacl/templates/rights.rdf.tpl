<RDF xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
    xmlns="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:r="http://jelix.org/ns/rights#"  >


<Bag RDF:about="urn:data:row">
{foreach $datas as $subject}
    <li>
      <Seq RDF:about="http://jelix.org/ns/rights/{$subject->id_aclsbj}">
       {foreach $subject->rights as $r}
        <li>
            <Description r:id_aclsbj="{$subject->id_aclsbj}" r:id_aclvalgrp="{$subject->id_aclvalgrp}" 
                r:value="{$r->value}" r:id_aclres="{$r->id_aclres}" r:enabled="{$r->enabled}">
                <r:label>{$r->label|escxml}</r:label>
            </Description>
        </li>
       {/foreach}
      </Seq>
    </li>
{/foreach}
</Bag>


{foreach $datas as $subject}
 <Description RDF:about="http://jelix.org/ns/rights/{$subject->id_aclsbj}"
    r:id_aclsbj="{$subject->id_aclsbj}" r:id_aclvalgrp="{$subject->id_aclvalgrp}"> 
  <r:label>{$subject->label|escxml}</r:label>
</Description>
{/foreach}

</RDF>