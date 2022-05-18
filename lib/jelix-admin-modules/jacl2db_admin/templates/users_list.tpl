{meta_html assets 'jacl2_admin'}
{meta_html assets 'datatables'}

<h1>{@jacl2db_admin~acl2.rights.management.title@}</h1>



<div id="rights-tabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
    <ul role="tablist" class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
        <li role="tab" tabindex="0" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab"
            aria-labelledby="ui-id-1"
            aria-selected="false" aria-expanded="false">
            <a href="{jurl 'jacl2db_admin~groups:index'}"  role="presentation"
               tabindex="-1" class="ui-tabs-anchor" id="ui-id-1">
                <span>{@jacl2db_admin~acl2.groups.tab@}</span></a></li>
        <li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-tabs-active ui-state-active"
            aria-controls="users-panel" aria-labelledby="ui-id-2"
            aria-selected="true" aria-expanded="true"
            >
            <a href="#users-panel"  role="presentation" tabindex="-1"
               class="ui-tabs-anchor" id="ui-id-2">
                <span>{@jacl2db_admin~acl2.users.tab@}</span></a></li>
    </ul>
    <div id="users-panel"  aria-labelledby="ui-id-2" role="tabpanel"
         class="ui-tabs-panel ui-corner-bottom ui-widget-content"
         aria-hidden="false">

        <template id="user-group-selector">
            <div class="list-filter-form">
            <label for="user-list-group">{@jacl2db_admin~acl2.filter.group@}</label>
            <select name="grpid" id="user-list-group" class="user-list-group">
                {foreach $groups as $group}
                    <option value="{$group->id_aclgrp}" {if $group->id_aclgrp == $grpid}selected="selected"{/if}>{$group->name}</option>
                {/foreach}
            </select>
            </div>
        </template>

        <table id="users-list"
               data-processing="true"
               data-server-side="true"
               data-page-length="15"
               data-length-menu="[ 10, 15, 20, 50, 80, 100 ]"
               data-jelix-url="{jurl 'jacl2db_admin~users:usersList' }">
            <thead>
            <tr>
                <th data-searchable="true" data-data="name">{@jacl2db_admin~acl2.col.users.name@}</th>
                <th data-orderable="false" data-data="groups">{@jacl2db_admin~acl2.col.groups@}</th>
                <th data-data="links" data-orderable="false" data-type="html"></th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>


