{meta_html assets 'jacl2_admin'}
{meta_html assets 'datatables'}

<h1>{@jacl2db_admin~acl2.rights.management.title@}</h1>


<div id="rights-tabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
    <ul role="tablist" class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
        <li role="tab" tabindex="0" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-tabs-active ui-state-active"
            aria-controls="groups-panel" aria-labelledby="ui-id-1"
            aria-selected="true" aria-expanded="true">
            <a href="#groups-panel" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-1">
                <span>{@jacl2db_admin~acl2.groups.tab@}</span></a></li>
        <li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab"
            aria-labelledby="ui-id-2"
            aria-selected="false" aria-expanded="false">
            <a href="{jurl 'jacl2db_admin~users:index'}"  role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-2">
                <span>{@jacl2db_admin~acl2.users.tab@}</span></a></li>
    </ul>
    <div id="groups-panel"  aria-labelledby="ui-id-1" role="tabpanel"
         class="ui-tabs-panel ui-corner-bottom ui-widget-content"
         aria-hidden="false">

    {ifacl2 'acl.group.create'}
        <p>
            <a class="ui-button ui-state-default" href="{jurl 'jacl2db_admin~groups:create'}">{@jacl2db_admin~acl2.create.group@}</a>

            <a href="{jurl 'jacl2db_admin~groups:allrights'}">{@jacl2db_admin~acl2.groups.rights.view.list@}</a>

        </p>
    {else}
        <p>
            <a href="{jurl 'jacl2db_admin~groups:allrights'}">{@jacl2db_admin~acl2.groups.rights.view.list@}</a>
        </p>
    {/ifacl2}

        {ifacl2 'acl.group.modify'}
        <template id="form-edit-name">
            <div class="cell-view">
                <span class="cell-value"></span>
                <button class="cell-btn-edit-name cell-btn-edit ui-icon ui-icon-pencil"></button>
            </div>
            <form class="cell-form" action="{jurl 'jacl2db_admin~groups:changename', array('group'=>'--group--')}">
                <input type="text" class="cell-input" name="name">
                <button class="cell-save">save</button>
                <button class="cell-cancel" type="button">cancel</button>
            </form>
        </template>

        <template id="form-edit-type">
            <div class="cell-view">
                <span class="cell-value" data-check-label="{@jacl2db_admin~acl2.group.rights.yes@}" data-uncheck-label=""></span>
                <button class="cell-btn-edit-type cell-btn-edit ui-icon ui-icon-pencil"></button>
            </div>
            <form class="cell-form" action="{jurl 'jacl2db_admin~groups:setdefault', array('group'=>'--group--')}">
                <input type="checkbox" class="cell-input" name="isdefault">
                <button class="cell-save">save</button>
                <button class="cell-cancel" type="button">cancel</button>
            </form>
        </template>
            <template id="group-item-links">
                <a href="" class="group-rights-link ui-button">{@jacl2db_admin~acl2.rights.link@}</a>
                {ifacl2 'acl.group.delete'}
                <button type="button" class="group-delete-link ui-button"
                        data-confirm-message="{@jacl2db_admin~acl2.delete.button.confirm.label@}"
                >{@jacl2db_admin~acl2.delete.button@}</button>
                {/ifacl2}
            </template>
        {else}
            <template id="form-edit-name">
                <div class="cell-view">
                    <span class="cell-value"></span>
                </div>
            </template>
            <template id="form-edit-type">
                <div class="cell-view">
                    <span class="cell-value" data-check-label="{@jacl2db_admin~acl2.group.rights.yes@}" data-uncheck-label=""></span>
                </div>
            </template>
            <template id="group-item-links">
                <a href="" class="group-rights-link ui-button">{@jacl2db_admin~acl2.rights.link@}</a>
            </template>
        {/ifacl2}


        <table id="groups-list"
               data-processing="true"
               data-server-side="true"
               data-page-length="15"
               data-length-menu="[ 10, 15, 20, 50, 80, 100 ]"
               data-jelix-url="{jurl 'jacl2db_admin~groups:groupsList' }">
            <thead>
            <tr>
                <!--<th data-searchable="false" data-data="details" data-orderable="false" data-type="html"></th>-->
                <th data-searchable="true" data-data="id">{@jacl2db_admin~acl2.col.groups.id@}</th>
                <th data-searchable="true" data-data="name">{@jacl2db_admin~acl2.col.groups.name@}</th>
                <th data-searchable="false" data-data="nb_users">{@jacl2db_admin~acl2.col.groups.users_number@}</th>
                <th data-searchable="false" data-data="grouptype">{@jacl2db_admin~acl2.col.groups.default@}</th>
                <th data-data="links" data-orderable="false" data-type="html"></th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
        <p>{@jacl2db_admin~acl2.group.setdefault.help@}</p>

    </div>
    <div
</div>

