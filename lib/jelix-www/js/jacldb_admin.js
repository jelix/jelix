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
    $($("."+($("#groupSelector").children("option:selected").val()))).show();
    $("#groupSelector").children("option:selected").remove();
    $("#group-head").attr('colspan', parseInt($("#group-head").attr('colspan')) + 1);
    $(".subjects-groups").attr('colspan', parseInt($(".subjects-groups").attr('colspan')) + 1);
    $("#foot-col").attr('colspan', parseInt($("#foot-col").attr('colspan')) + 1);
    if (!$("#groupSelector").children("option:selected").length) {
        $(".colhide").hide();
        $(".colblank").hide();
        $("#div-group-selector").hide();
    }
}