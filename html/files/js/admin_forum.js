$(function() {
    // Flag controls
    $('.flags a.remove').on('click', function(e) {
        e.preventDefault();
        var $this = $(this),
            $row = $(this).closest('tr');
        
        $.getJSON('forum.php?action=flag.remove&id='+$row.attr('data-pid'), function(data) {
            if (data.status === true) {
                $row.slideUp();
            }
        });
    });


    var threadDeleteTmpl = '<tmpl>\
                                <form>\
                                    <ul class="reasons plain">\
                                        <li>\
                                            <input type="radio" name="reason" id="reason1" value="1"/>\
                                            <h3><label for="reason1">It is off-topic</label></h3>\
                                            This thread is not relevant to any section of the site.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason2" value="2"/>\
                                            <h3><label for="reason2">It is a spoiler</label></h3>\
                                            This thread contains primarily answers or more detailed information than is necessary.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason3" value="3"/>\
                                            <h3><label for="reason3">It is spam</label></h3>\
                                            This thread is primarily an advertisement with no disclosure. It is not useful or relevant, but promotional.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason4" value="4"/>\
                                            <h3><label for="reason4">It is very low quality</label></h3>\
                                            This thread has severe formatting or content problems.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason5" value="5"/>\
                                            <h3><label for="reason5">It is not English</label></h3>\
                                            The HackThis!! community’s first language is English.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason6" value="6"/>\
                                            <h3><label for="reason6">No longer relevant</label></h3>\
                                            This thread is being removed just to tidy things up.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason7" value="7"/>\
                                            <h3><label for="reason7">Other</label></h3>\
                                            <div class="modal-reason-other hide">\
                                                <textarea name="other" placeholder="Add explanation"/>\
                                            </div>\
                                        </li>\
                                    </ul>\
                                    <input type="submit" class="button left" value="Delete thread"/>\
                                </form>\
                            </tmpl>';

    var postDeleteTmpl = '<tmpl>\
                                <form>\
                                    <ul class="reasons plain">\
                                        <li>\
                                            <input type="radio" name="reason" id="reason1" value="1"/>\
                                            <h3><label for="reason1">It is off-topic</label></h3>\
                                            This post is not relevant to the thread.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason2" value="2"/>\
                                            <h3><label for="reason2">It is a spoiler</label></h3>\
                                            This post contains primarily answers or more detailed information than is necessary.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason3" value="3"/>\
                                            <h3><label for="reason3">It is spam</label></h3>\
                                            This post is primarily an advertisement with no disclosure. It is not useful or relevant, but promotional.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason4" value="4"/>\
                                            <h3><label for="reason4">It is very low quality</label></h3>\
                                            This post has severe formatting or content problems.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason5" value="5"/>\
                                            <h3><label for="reason5">It is not English</label></h3>\
                                            The HackThis!! community’s first language is English.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason6" value="6"/>\
                                            <h3><label for="reason6">No longer relevant</label></h3>\
                                            This post refers to a post that longer exists and is being removed just to tidy things up.\
                                        </li>\
                                        <li>\
                                            <input type="radio" name="reason" id="reason7" value="7"/>\
                                            <h3><label for="reason7">Other</label></h3>\
                                            <div class="modal-reason-other hide">\
                                                <textarea name="other" placeholder="Add explanation"/>\
                                            </div>\
                                        </li>\
                                    </ul>\
                                    <input type="submit" class="button left" value="Delete post"/>\
                                </form>\
                            </tmpl>';

    var modal_thread_edit = 'hello',
        modal_thread_delete = $(threadDeleteTmpl).tmpl()[0].outerHTML,
        modal_post_edit = 'hello',
        modal_post_delete = $(postDeleteTmpl).tmpl()[0].outerHTML;

    $('.thread-edit, .thread-delete, .post-edit, .post-delete').on('click', function(e) {
        e.preventDefault();
        var title = '',
            id = $(this).closest('li').data('id'),
            action;

        if ($(this).hasClass('thread-delete')) {
            action = 'admin.thread.remove';
            id = $('.forum-main').data('thread-id');
        } else if ($(this).hasClass('post-delete')) {
            action = 'admin.post.remove';
        }

        if ($(this).hasClass('thread-edit')) {
            title = 'Edit thread';
            template = modal_thread_edit;
        } else if ($(this).hasClass('thread-delete')) {
            title = 'Delete thread';
            template = modal_thread_delete;
        } else if ($(this).hasClass('post-edit')) {
            title = 'Edit post';
            template = modal_post_edit;
        } else if ($(this).hasClass('post-delete')) {
            title = 'Delete post';
            template = modal_post_delete;
        }

        $.createModal(title, template, function() {
            var $modal = this;
            $modal.find('.reasons input[type=radio]').on('change', function() {
                if ($modal.find('#reason7:checked').length) {
                    $modal.find('.modal-reason-other').slideDown('fast');
                } else {
                    $modal.find('.modal-reason-other').slideUp('fast');
                }
            });

            $modal.find('input[type=submit]').on('click', function(e) {
                e.preventDefault();

                if (!$modal.find(':checked').length)
                    return false;

                var reason = $modal.find(':checked').attr('value'),
                    other = $modal.find('textarea').val();

                data = {
                    reason: reason,
                    extra: other,
                    id: id
                }

                $modal.height($modal.height());
                $modal.find('.modal-content').fadeOut('fast', function() {
                    $.get('/files/ajax/forum.php?action='+action, data, function() {
                        $modal.find('.modal-content').html($('<div>', {'html': "<i class='icon-good'></i>Thank you", 'class': 'thanks'})).fadeIn();
                    });
                });
            });
        });
    });
});