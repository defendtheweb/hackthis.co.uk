$(function() {
    var item_id = $("#comments").attr("data-id");
    var commentsTmpl =  '<tmpl><article id="comment_${comment_id}">'+
                        '    <header>'+
                        '        <div class="right">'+
                        '            <time pubdate datetime="${time}"></time>'+
                        '            {{if owner}}<a href="#" class="clean comment_delete">Delete</a>{{/if}}'+
                        '        </div>'+
                        '        <a href=\'/user/${username}\' class="user clean">${username}</a>'+
                        '    </header>'+
                        '    <div class="body">'+
                        '        {{html comment}}'+
                        '    {{if replies}}'+
                        '        {{tmpl(replies) "commentsTmpl"}}'+
                        '    {{/if}}'+
                        '    </div>'+
                        '</article></tmpl>';

    // $('#comment_submit').closest('form').submit(function(ev) {
    //     ev.preventDefault();
        
    //     var text = $("#comment_submit_text").val();
    //     if (!text) return false;
                  
    //     $.post("/files/ajax/comments.php", {"action": "comment_submission", "comment": text, "item_type": item_type, "item_id": item_id}, function(data, textStatus) {
    //         renderComment(data, true);
    //     });
    // });
    
    // $('.comment_delete').live('click', function(ev) {
    //     ev.preventDefault();
        
    //     var comment_cont = $(this).closest('.comment');
    //     var comment_id = $(this).closest('.comment').attr('id').replace(/\D/g,'');
                  
    //     $.post("/files/ajax/comments.php", {"action": "delete_comment", "comment_id": comment_id}, function(data) {
    //         if (data.status)
    //             comment_cont.slideUp();
    //         else
    //             alert("Error deleting comment");
    //     }, "json");
    // });
    
    // $('.comment_report').live('click', function(ev) {
    //     ev.preventDefault();
    //     var target = $(this);
    //     var comment_id = $(this).closest('.comment').attr('id').replace(/\D/g,'');
    //     $.post("/files/ajax/comments.php", {"action": "report_comment", "comment_id": comment_id}, function(data) {
    //         console.log(data);
    //         if (!data.status)
    //             alert("Error reporting comment, please contact administration");
    //         else
    //             target.replaceWith('Reported');
    //     }, "json");
    // });

    $.get("/files/ajax/comments.php", {"action": "get", "id": item_id}, function(data) {
        renderComment(data);
    }, "json");

    function renderComment(json, submit) {
        if (json.status == true) {
            var comments = json.comments;
            // $.each(comments, function(index, item) {
            //     item.pretty_date = prettyDate(item.time);
            // });
            
            if (submit) {
                $(commentsTmpl).tmpl(comments).hide().prependTo("#comments_container").slideDown();
            } else {
                $('#comments_container .comments_loading').hide();
                // Need to compile template so it can be used recursively
                $.template("commentsTmpl", commentsTmpl);
                $.tmpl("commentsTmpl", comments).prependTo("#comments_container");
            }
        }
    }
});
