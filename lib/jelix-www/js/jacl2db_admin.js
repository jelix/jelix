
class CellEditor {

    /**
     *
     * @param {jQuery} table
     */
    constructor(table)
    {
        this.table = table
        this.currentCell = null;
    }

    get form() {
        if (this.currentCell) {
            return this.currentCell.find('.cell-form');
        }
        return null;
    }

    get view() {
        if (this.currentCell) {
            return this.currentCell.find('.cell-view');
        }
        return null;
    }

    enableEditor(btn)
    {
        this.disableCurrentEditor();
        this.currentCell = btn.parents('td');
        this.view.hide();
        this.form.show();
    }

    disableCurrentEditor() {
        if (this.currentCell) {
            this.form.hide();
            this.view.show();
            this.currentCell = null;
        }
    }

    sendForm(datatable)
    {
        let cell = this.currentCell;
        if (!cell) {
            return false;
        }
        let form = this.form;
        let formData = new FormData(form[0]);
        let input = cell.find('.cell-input');
        let me = this;
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            enctype: 'application/x-www-form-urlencoded',
            data: formData,
            processData: false,
            contentType: false,
            error: function(e){ alert('Error during the save: ' +e.responseText) },
            success: function(msg){
                let cellValue = $(cell).find('.cell-value');
                let newval = input.val();

                if (input.attr('type') == 'checkbox') {

                    if(input.prop('checked')) {
                        newval = 1;
                    }
                    else {
                        newval = 0;
                    }
                }

                let dtcell = datatable.cell(cell);
                dtcell.data(newval);
            }
        });

        return true;
    }

    getCellHtml(tpl, data, row)
    {
        let cell = tpl.cloneNode(true);
        let cellValue = $(cell).find('.cell-value');
        let label = data;
        let input = $(cell).find('.cell-input');

        if (cellValue.attr('data-check-label')) {
            label = data == 1?
                cellValue.attr('data-check-label'):
                cellValue.attr('data-uncheck-label');
            if (data == 1) {
                input.attr('checked', 'true');
            }
            else {
                input.removeAttr('checked');
            }
        }
        else {
            input.attr('value', data);
        }
        cellValue.text(label);

        $(cell).find('.cell-id').attr('value', row.id);


        let div = document.createElement("div");
        div.appendChild(cell);
        return div.innerHTML;
    }
}




$("document").ready( function () {

    if ($('#rights-edit select').length) {
        $('#rights-edit select[id$=".view"]').change(function () {
            var root = this.id.substr(0, this.id.length - 5);
            if (!window.hasOwnProperty('oldConf'))
                window.oldConf = [];
            if (window.oldConf[root] === 'undefined') {
                window.oldConf[root] = [];
            }
            var selectList = $('#rights-edit select[id^="' + root + '"]');
            for (element in selectList) {
                if (element === 'length')
                    break ;
                var select = selectList[element];
                if (select.id === root + '.view')
                    continue ;
                var selectedOption = $(this).children('option:selected').val();
                if (selectedOption === 'n') {
                    window.oldConf[select.id.substr(root.length + 1)] = $(select).val();
                    $(select).val("n");
                } else if (selectedOption === 'y' && window.oldConf[select.id.substr(root.length + 1)] !== 'undefined') {
                    $(select).val(window.oldConf[select.id.substr(root.length + 1)]);
                }
            }
        });
        var selectList = $('#rights-edit select[id$=".view"]');
        for (element in selectList) {
            if (element === 'length')
                break ;
            var root = selectList[element].id.substr(0, selectList[element].id.length - 5);
            $('#rights-edit select[id^="' + root + '"]').data("root", root);
            $('#rights-edit select[id^="' + root + '"]').change(function() {
                var root = $(this).data("root");
                if (this.id !== root + '.view' && $('#rights-edit select[id="' + root + '.view"]').children('option:selected').val() !== 'y') {
                    $('#rights-edit select[id="' + root + '.view"]').val('y')
                }
            });
        }
    }

    document.querySelectorAll('#rights-list select').forEach(setColorToSelect);

    var rightsTable = document.getElementById('rights-list');
    if (rightsTable) {
        rightsTable.addEventListener('change', function(event) {
            setColorToSelect(event.target);
            var rightResult = event.target.value;
            if (event.target.classList.contains('user-right-authorization')) {
                var hasYes = false;
                var hasForbidden = false;
                var tdList = event.target.parentNode.parentNode.querySelectorAll('td[data-right]');
                tdList.forEach(function(td) {
                    var grpRight =  td.getAttribute('data-right');
                    if (grpRight == 'y') {
                        hasYes = true;
                    }
                    else if (grpRight == 'n') {
                        hasForbidden = true;
                    }
                });

                if (rightResult != 'n') {
                    if (hasForbidden) {
                        rightResult = 'n';
                    }
                    else if (hasYes) {
                        rightResult = 'y'
                    }
                }
                var imgResult = event.target.parentNode.parentNode.querySelector('td.rights-result img');
                var labelResult, imgResultUri;
                if (rightResult == 'y') {
                    labelResult = rightsTable.getAttribute('data-yes-title');
                    imgResultUri = rightsTable.getAttribute('data-yes-img');
                }
                else {
                    labelResult = rightsTable.getAttribute('data-no-title');
                    imgResultUri = rightsTable.getAttribute('data-no-img');
                }
                imgResult.setAttribute('src', imgResultUri);
                imgResult.setAttribute('alt', labelResult);
                imgResult.setAttribute('title', labelResult);
            }
        })
    }

    var cellListEditor;
    var groupList = $('#groups-list');
    if (groupList.length) {
        cellListEditor = new CellEditor(groupList);

        var tplNameEdit = document.getElementById('form-edit-name').content;
        var tplTypeEdit = document.getElementById('form-edit-type').content;
        var tplLinks = document.getElementById('group-item-links').content;

        var dt = groupList.DataTable({
            "language" : DatatablesTranslations,
            "ajax": {
                "url":   groupList.data('jelixUrl'),
                "data": function ( d ) {
                    delete d.columns;
                }
            },
            "columns": [
                /*{
                    data: "details",
                    searchable: false,
                    orderable: false,
                    type: "html"
                },*/
                {
                    data: "id",
                },
                {
                    data: "name",
                    render: function (data, type, row) {
                        if (type === 'display') {

                            return cellListEditor.getCellHtml(tplNameEdit, data, row);
                        }

                        return data;
                    },
                },
                {
                    data: "nb_users",
                },
                {
                    data: "grouptype",
                    render: function (data, type, row) {
                        if (type === 'display') {
                            let tpl = tplTypeEdit;
                            if (row.id == '__anonymous') {
                                tpl = tplTypeEdit.cloneNode(true)
                                $(tpl).find('.cell-form').remove();
                                $(tpl).find('.cell-btn-edit-type').remove();
                            }
                            return cellListEditor.getCellHtml(tpl, data, row);
                        }
                        return data;
                    },
                },
                {
                    data: "links",
                    render: function(data, type, row) {
                        if (type === 'display') {
                            let content = tplLinks.cloneNode(true);
                            $(content).find('.group-rights-link').attr('href', data.rights);
                            $(content).find('.group-delete-link').attr('data-url', data.delete);

                            let div = document.createElement("div");
                            div.appendChild(content);
                            return div.innerHTML;
                        }
                        return data;
                    }
                },

            ]
        });

        dt.on( 'preDraw', function () {
            cellListEditor.disableCurrentEditor();
        });

        //var detailRows = [];
        var groupListBody = $('#groups-list tbody');
        /*groupListBody.on('click', 'tr td:first-child', function () {
            var tr = $(this).closest('tr');
            var row = dt.row(tr);
            var idx = detailRows.indexOf(tr.attr('id'));

            if (row.child.isShown()) {
                tr.removeClass('details');
                row.child.hide();

                // Remove from the 'open' array
                detailRows.splice(idx, 1);
            } else {
                tr.addClass('details');
                var content = document.getElementById('group-details').content.cloneNode(true);
                row.child(content).show();

                // Add to the 'open' array
                if (idx === -1) {
                    detailRows.push(tr.attr('id'));
                }
            }
        });*/

        groupListBody.on('click', 'td .cell-btn-edit', function() {
            cellListEditor.enableEditor($(this))
        });

        groupListBody.on('click', 'td .cell-cancel', function() {
            cellListEditor.disableCurrentEditor();
        });

        groupListBody.on('submit', 'td .cell-form', function(ev)
        {
            ev.preventDefault();
            ev.stopPropagation();
            cellListEditor.sendForm(dt);
        });

        groupListBody.on('click', 'td .group-delete-link', function() {

            cellListEditor.disableCurrentEditor();
            if (window.confirm(this.dataset.confirmMessage)) {
                $.ajax({
                    type: 'GET',
                    url: this.dataset.url,
                    error: function(e){ alert('Error during the save: ' +e.responseText) },
                    success: function(msg){
                        if (msg.result == 'error') {
                            window.alert(msg.message);
                        }
                        else {
                            dt.draw();
                        }
                    }
                });
            }
        });

    }

    var usersList = $('#users-list');
    if (usersList.length) {
        usersList.DataTable({
            "language" : DatatablesTranslations,
            "ajax": {
                "url":   $('#users-list').data('jelixUrl'),
                "data": function ( d ) {
                    d = $.extend( {}, d, {
                        "grpid": $('#user-list-group').val()
                    } );
                    delete d.columns;
                    return d;
                }
            }
        });
        var userGroupSelector = document.getElementById('user-group-selector').content.cloneNode(true);
        var container = usersList.DataTable().table().container();
        $(container).find('.dataTables_filter').after(userGroupSelector);
        $(container).find('.user-list-group').on('change',function (event) {
            usersList.DataTable().draw();
        });
    }


    var grpNameCreate = $("#create-group #grp_name");
    if (grpNameCreate.length) {
        var grpIdCreate =  $("#create-group #grp_id");
        grpNameCreate.on('change', function(){
            if (grpIdCreate.val().length === 0) {
                var id = grpNameCreate.val();
                id = id.replace(' ', '_');
                id = id.replace(/[^a-zA-Z0-9_]/g, '');
                grpIdCreate.val(id);
            }
        });
    }
});

function hideSelect()
{
    if ($("#type-list").children('option:selected').val() === "user") {
        $("#hideField").show();
    } else {
        $("#hideField").hide();
    }
}

function showColumn()
{
    selected = $("groupSelector").children("option:selected");
    if (!selected.length) {
        return ;
    }
    $($("."+(selected.val()))).show();
    selected.remove();
    $("#group-head").attr('colspan', parseInt($("#group-head").attr('colspan')) + 1);
    $(".subjects-groups").attr('colspan', parseInt($(".subjects-groups").attr('colspan')) + 1);
    $("#foot-col").attr('colspan', parseInt($("#foot-col").attr('colspan')) + 1);
    if (!$("#groupSelector").children("option:selected").length) {
        $(".colhide").hide();
        $(".colblank").hide();
        $("#div-group-selector").hide();
    }
}


function setColorToSelect(select) {
    var val = select.value;
    if (val == '') {
        select.classList.add('right-no');
        select.classList.remove('right-forbidden')
        select.classList.remove('right-yes')
    }
    else if (val == 'n') {
        select.classList.add('right-forbidden');
        select.classList.remove('right-no')
        select.classList.remove('right-yes')

    }
    else {
        select.classList.add('right-yes');
        select.classList.remove('right-forbidden')
        select.classList.remove('right-no')
    }
}
