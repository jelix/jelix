$("document").ready( function () {
    if ($("#search-bar").length) {
        if ($('#search-bar').data('select-on-ac')) {
            $("#search-bar").autocomplete({
                source: $("#search-bar").data('link'),
                select: function (event, ui) {
                    $("#search-bar").val(ui.item.value.login);
                    $("#type-hidden").val(ui.item.value.type);
                    return false;
                }
           });
        } else {
            $("#search-bar").autocomplete({
                source: $("#search-bar").data('link'),
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
                if ($(this).children('option:selected').val() === 'n') {
                    window.oldConf[select.id.substr(root.length + 1)] = $(select).val();
                    $(select).val("n");
                } else if ($(this).children('option:selected').val() === 'y' && window.oldConf[select.id.substr(root.length + 1)] !== 'undefined') {
                    console.log($(this).children('option:selected').val());
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
    if ($("#type-list").selectedItem === "user") {
        $("#hideField").show();
    } else {
        $("#hideField").hide();
    }
}

function showColumn()
{
    if (!$("#groupSelector").children("option:selected").length) {
        return ;
    }
    $($("."+($("#groupSelector").children("option:selected").val()))).show();
    $("#groupSelector").children("option:selected").remove();
    $("#group-head").attr('colspan', parseInt($("#group-head").attr('colspan')) + 1);
}