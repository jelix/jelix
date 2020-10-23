$("document").ready( function () {
    window.searchBar = $("#search-bar");
    if (window.searchBar.length) {
        if ($('#search-bar').data('select-on-ac')) {
            window.searchBar.autocomplete({
                source: window.searchBar.data('link'),
                select: function (event, ui) {
                    window.searchBar.val(ui.item.value.login);
                    $("#type-hidden").val(ui.item.value.type);
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
            };
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