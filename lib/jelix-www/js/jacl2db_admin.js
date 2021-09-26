$("document").ready( function () {
    window.searchBar = $("#search-bar");
    if (window.searchBar.length) {
        var searchBarButton = window.searchBar.form().find('button[type=submit]');
        if ($('#search-bar').data('select-on-ac')) {
            window.searchBar.autocomplete({
                source: window.searchBar.data('link'),
                select: function (event, ui) {
                    window.searchBar.val(ui.item.value);
                    $("#item-type").val(ui.item.infos.type);
                    $("#item-id").val(ui.item.infos.id);
                    searchBarButton.removeAttr('disabled');
                    return false;
                }
           });
        } else {
            window.searchBar.autocomplete({
                source: window.searchBar.data('link'),
            });
        }
    }
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
