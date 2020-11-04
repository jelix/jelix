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