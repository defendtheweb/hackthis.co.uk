$(function() {
    var item_id = $("#comments").attr("data-id");
    // var commentsTmpl =  '<article data-id="${id}" id="comment-${id}">'+
    //                     '    <header>'+
    //                     '        <div class="right">'+
    //                     '            <time pubdate datetime="${time}">${timeSince(time)}</time>';
    // if (loggedIn) {
    //     commentsTmpl += '            {{if username}}<div class="more"><i class="icon-menu"></i>'+
    //                     '                <ul>'+
    //                     '                    {{if owner}}<li class="seperator"><a href="#" class="comment-delete">Delete</a></li>{{/if}}'+
    //                     '                    <li><a href="#" class="comment-reply">Reply</a></li>'+
    //                     '                    {{if username != 0}}<li><a href="/inbox/compose?to=${username}" class="messages-new" data-to="${username}">PM User</a></li>{{/if}}'+
    //                     '                    <li><a href="#" class="comment-report">Report</a></li>'+
    //                     '                </ul>'+
    //                     '            </div>{{/if}}';
    // }

    // commentsTmpl +=     '        </div>'+
    //                     '        <span class="strong">'+
    //                     '            {{if owner}}'+
    //                     '                <img src="${image}" width="28px"/> You'+
    //                     '            {{else username}}'+
    //                     '                {{if username == 0}}'+
    //                     '                    [deleted user]'+
    //                     '                {{else}}'+
    //                     '                    <a href=\'/user/${username}\'><img src="${image}" width="28px"/> ${username}</a>'+
    //                     '                {{/if}}'+
    //                     '            {{else}}'+
    //                     '                [comment removed]'+
    //                     '            {{/if}}'+
    //                     '        </span>'+
    //                     '    </header>'+
    //                     '    <div class="body clearfix">'+
    //                     '        {{if username}}'+
    //                     '            {{html comment}}'+
    //                     '        {{/if}}'+
    //                     '    {{if replies}}'+
    //                     '        {{tmpl(replies) "commentsTmpl"}}'+
    //                     '    {{/if}}'+
    //                     '    </div>'+
    //                     '</article>';

    var commentsTmpl =  '<article data-id="${id}" id="comment-${id}" class="comment">'+
                        '    {{if username}}'+
                        '        <a class="avatar">'+
                        '            <img src="{{if username}}${image}{{else}}/users/images/no_pic.jpg{{/if}}"/>'+
                        '        </a>'+
                        '        <div class="content">'+
                        '            {{if username != 0}}'+
                        '                <a class="author" href=\'/user/${username}\'>${username}</a>'+
                        '            {{else}}'+
                        '                <span class="dark">[deleted user]</span>'+
                        '            {{/if}}'+
                        '            <time class="small dark" pubdate datetime="${time}">${timeSince(time)}</time>'+
                        '            <div class="controls small dark">'+
                        '                <a href="#" class="comment-reply"><i class="icon-reply"></i> Reply</a> '+
                        '                {{if owner}} &middot; <a href="#" class="comment-delete"><i class="icon-trash"></i> Delete</a>{{/if}}'+
                        '            </div>'+
                        '            <div class="body clr">'+
                        '                {{html comment}}'+
                        '            </div>'+
                        '        </div>'+
                        '    {{else}}'+
                        '        <div class="content deleted">'+
                        '            [comment removed]'+
                        '        </div>'+
                        '    {{/if}}'+
                        '    {{if replies}}<div class="replies clr">'+
                        '       {{tmpl(replies) "commentsTmpl"}}'+
                        '    </div>{{/if}}'+
                        '</article>';



    // Need to compile template so it can be used recursively
    $.template("commentsTmpl", commentsTmpl);

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

            $('a[href^="http://"]:has(img), a[href^="https://"]:has(img)').addClass('hide-external');
        }
    }

    // Hash tag highlight
    function highlight(elemId){
        $(elemId).addClass('highlight');
    }
    if (document.location.hash) {
        highlight(document.location.hash);
    }

    // Menu handlers
    var mainEditor = $('.wysiwyg')[0];
    $('#comments').on('click', '.comment-reply', function(e) {
        e.preventDefault();

        var parent = $(this).closest('.content').siblings(".replies");
        if (!parent.length) {
            $(this).closest('.comment').append($('<div>', {class: 'replies clr'}));
            var parent = $(this).closest('.content').siblings(".replies");
        }

        // Check for existing editor
        var tmp = parent.children('form');
        if (tmp.length) {
            newEditor = tmp;
        } else {
            var newEditor = $(mainEditor).clone();
            var submitButton = $('<input>', {class: 'submit button right', type: 'submit', value: 'Submit'});
            var cancelButton = $('<a>', {class: 'cancel right', href: '#', text: 'Cancel'});
            var $form = $("<form>").append(newEditor).append(submitButton).append(cancelButton);

            if (parent.find('article').length)
                $(parent.find('article')[0]).before($form);
            else
                parent.append($form);

            $('.suggest').autosuggest();
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
                //update counter
                var $responses = $('#comments > h2');
                var tmp = $responses.text().replace(/(\d+)+/, function(match, number) {
                    return parseInt(number)-1;
                });
                $responses.text(tmp);
            } else {
                alert("There appears to be a problem deleting this comment");
            }
        }, 'json');
    });


    // WYSIYG controls
    $('#comments').on('click', '.cancel', function(e) {
        e.preventDefault();
        var parent = $(this).closest('form').slideUp(200, function() { $(this).remove() });
    }).on('submit', 'form', function(e) {
        e.preventDefault();

        var $article = $(this).closest('article');

        var parent_id = $article.attr("data-id");
        if (!parent_id)
            parent_id = 0;

        var $parent = $(this).closest('form');
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

                    //update counter
                    var $responses = $('#comments > h2');
                    var tmp = $responses.text().replace(/(\d+)+/, function(match, number) {
                        return parseInt(number)+1;
                    });
                    $responses.text(tmp);
                } else {
                    alert("An error!");
                }
            },
            'json');
    });
});
