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
        <table id="groups-list"
               data-processing="true"
               data-server-side="true"
               data-page-length="20"
               data-length-menu="[ 10, 20, 50, 80, 100 ]"
               data-jelix-url="{jurl 'jacl2db_admin~groups:groupsList' }">
            <thead>
            <tr>
                <th data-searchable="true" data-data="name">{@jacl2db_admin~acl2.col.groups.name@}</th>
                <th data-data="links" data-orderable="false" data-type="html"></th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div
</div>

