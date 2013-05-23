$(function() {
    var item_id = $("#comments").attr("data-id");
    var commentsTmpl =  '<article data-id="${id}" id="comment-${id}">'+
                        '    <header>'+
                        '        <div class="right">'+
                        '            <time pubdate datetime="${time}">${timeSince(time)}</time>';
    if (loggedIn) {
        commentsTmpl += '            {{if username}}{{if username != 0}}<div class="more"><i class="icon-menu"></i>'+
                        '                <ul>'+
                        '                    {{if owner}}<li><a href="#">Edit</a></li>{{/if}}'+
                        '                    {{if owner}}<li class="seperator"><a href="#" class="comment-delete">Delete</a></li>{{/if}}'+
                        '                    <li><a href="#" class="comment-reply">Reply</a></li>'+
                        '                    <li><a href="#">PM User</a></li>'+
                        '                    <li><a href="#" class="comment-report">Report</a></li>'+
                        '                </ul>'+
                        '            </div>{{/if}}{{/if}}';
    }

    commentsTmpl +=     '        </div>'+
                        '        <span class="strong">'+
                        '            {{if owner}}'+
                        '                <img src="http://www.hackthis.co.uk/users/images/28/1:1/${image}.jpg"/> You'+
                        '            {{else username}}'+
                        '                {{if username == 0}}'+
                        '                    [deleted user]'+
                        '                {{else}}'+
                        '                    <a href=\'/user/${username}\'><img src="http://www.hackthis.co.uk/users/images/28/1:1/${image}.jpg"/> ${username}</a>'+
                        '                {{/if}}'+
                        '            {{else}}'+
                        '                [comment removed]'+
                        '            {{/if}}'+
                        '        </span>'+
                        '    </header>'+
                        '    <div class="body">'+
                        '        {{if username}}'+
                        '            {{html comment}}'+
                        '        {{/if}}'+
                        '    {{if replies}}'+
                        '        {{tmpl(replies) "commentsTmpl"}}'+
                        '    {{/if}}'+
                        '    </div>'+
                        '</article>';
    // Need to compile template so it can be used recursively
    $.template("commentsTmpl", commentsTmpl);

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
            
            if (submit) {
                $.tmpl("commentsTmpl", comments).hide().prependTo("#comments_container").slideDown();
            } else {
                $('#comments_container .comments_loading').hide();
                $.tmpl("commentsTmpl", comments).prependTo("#comments_container");

                if (document.location.hash && document.location.hash.substring(0, 9) == "#comment-") {
                    highlight(document.location.hash);
                    target = $(document.location.hash);
                    var wheight = $(window).height();
                    var pos = target.offset().top - (wheight / 2) + target.height();
                    $('body, html').animate({ scrollTop: pos }, 400);
                }
            }
        }
    }

    // Hash tag highlight
    function highlight(elemId){
        $(elemId).addClass('highlight');
        setTimeout(function(){$(elemId).removeClass('highlight')}, 6000);
    }
    if (document.location.hash) {
        highlight(document.location.hash);
    }

    // Menu handlers
    var mainEditor = $('.wysiwyg')[0];
    $('#comments').on('click', '.comment-reply', function(e) {
        e.preventDefault();

        var parent = $(this).closest('header').siblings(".body");

        // Check for existing editor
        var tmp = parent.children('form');
        if (tmp.length) {
            newEditor = tmp;
        } else {
            var newEditor = $(mainEditor).clone();
            var submitButton = $('<input>', {class: 'submit button right', type: 'submit', value: 'Submit'});
            var cancelButton = $('<a>', {class: 'cancel right', href: '#', text: 'Cancel'});
            var $form = $("<form>").append(newEditor).append(submitButton).append(cancelButton);

            newEditor.children('textarea').autosuggest();

            if (parent.find('article').length)
                $(parent.find('article')[0]).before($form);
            else
                parent.append($form);
        }
        
        newEditor.children('textarea').focus();

        var wheight = $(window).height();
        var pos = newEditor.offset().top - (wheight / 2) + newEditor.height();
        $('body, html').animate({ scrollTop: pos }, 400);
    }).on('click', '.comment-delete', function(e) {
        e.preventDefault();
        $article = $(this).closest('article');
        var comment_id = $article.attr('data-id');

        $.post('/files/ajax/comments.php?action=delete', {"id": comment_id}, function(data) {
            if (data.status) {
                $article.slideUp(function() { $(this).remove(); });
            } else {
                alert("There appears to be a problem deleting this comment");
            }
        }, 'json');
    });


    // WYSIYG controls
    $('#comments').on('click', '.cancel', function(e) {
        e.preventDefault();
        var parent = $(this).closest('form').slideUp(200, function() { $(this).remove() });
    }).on('click', '.submit', function(e) {
        e.preventDefault();

        var $article = $(this).closest('article');

        var parent_id = $article.attr("data-id");
        if (!parent_id)
            parent_id = 0;

        var $parent = $(this).parent();
        var body = $parent.find('textarea').val();

        $parent.children('.msg').remove();

        $.post('/files/ajax/comments.php?action=add', {"id": item_id, "parent": parent_id, "body": body},
            function(data) {
                if (data.status) {
                    var msg = $('<div>', {class: 'hide msg msg-good'}).append($('<i>', {class: 'icon-good'})).append("Comment submitted");
                    var newComment = $.tmpl("commentsTmpl", data.comment);

                    if (parent_id === 0) {
                        msg.hide().prependTo($parent).slideDown();
                        $parent.find('textarea').val('');
                        $parent = $('#comments_container');
                        newComment.hide().prependTo($parent).slideDown();
                    } else {
                        msg.prependTo(newComment);
                        $parent.after(newComment);
                        $parent.remove();
                    }
                } else {
                    alert("An error!");
                }
            },
            'json');
    });
});
