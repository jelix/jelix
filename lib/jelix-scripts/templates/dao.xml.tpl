<?xml version="1.0" encoding="iso-8859-1"?>
<dao xmlns="http://jelix.org/ns/dao/1.0">
   <datasources>
      <table name="{$table}" realname="{$table}">
         {$primarykeys}
      </table>
   </datasources>
   <record>
       {$properties}
      <!--<property name="" fieldname="" datatype="string/int/float/autoincrement/date"
      pk="yes"
      required="yes"
      maxlength="" minlength="" regexp=""
      sequence=""
      updatemotif="" insertmotif="" selectmotif=""
      />-->
   </record>
   <!--<methods>
      <method name="findByStage" type="select/selectfirst/delete/update/php">
          <parameters>
            <parameter name="" />
          </parameters>
          <values>
            <value property="" value="" />
          </values>
          <conditions logic="and/or">
            <eq property="" value="" />
          </conditions>
          <order>
            <orderitem property="" way="asc/desc" />
          </order>
          <limit offset="" count=""/>
          <body><![CDATA[
          ]]></body>
      </method>
   </methods>-->
</dao>
