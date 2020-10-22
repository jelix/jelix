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
    if ($("#type-list").selectedItem === "user") {
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
}