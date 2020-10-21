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