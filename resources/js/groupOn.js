// Add a new task to the To Do list when clicking on the submit button
$('#addGroupOnUser').click(function(){
    var supplierID = $("[name='supplierID']");
    var token = $("[name='token']");
    var data = {
        'supplierID': supplierID.val(),
        'token': token.val()
    };
    $.ajax({
        type: "POST",
        url: "/group-on",
        data: data,
        success: function(data)
        {
            var data = jQuery.parseJSON( data );
            $("ul.groupOnUsers").append('' +
                '<li>' +
                '   <span class="groupOnUser">' + data.supplierID + '</span> ' +
                '   <span class="groupOnUser">' + data.token + '</span> ' +
                '</li>');
            supplierID.val("");
            token.val("");
        },
        error: function(data)
        {
            alert("ERROR");
        }
    });
});
 
// Update the status of an existing task in the To Do list and mark it as done when clicking on the Mark as done button
/*$(document).on('click', 'button.done-button', function(e) {
    var button = this;
    var id = button.id;
    $.ajax({
        type: "PUT",
        url: "/todo/" + id,
        success: function(data)
        {
            var data = jQuery.parseJSON( data );
            if(data.isDone)
            {
                $("#" + id).removeClass("done-button").addClass("delete-button").html("Delete from list");
                $("#" + id).prev().addClass("done");
            }
            else
            {
                alert("ERROR");
            }
        },
        error: function(data)
        {
            alert("ERROR");
        }
    });
});
 */
// Delete a task from the To Do list when clicking on the Delete from list button
/*$(document).on('click', 'button.delete-button', function(e) {
    var button = this;
    var id = button.id;
    $.ajax({
        type: "DELETE",
        url: "/todo/" + id,
        success: function(data)
        {
            $("#" + id).parent().remove();
        },
        error: function(data)
        {
            alert("ERROR");
        }
    });
});*/