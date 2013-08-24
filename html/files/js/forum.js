$(function() {
    $(".forum-sidebar .sticky").sticky({topSpacing:45});

    $('.new-thread').on('click', function(e) {
        e.preventDefault();

        $('.forum-container').toggleClass('new-thread');
        if ($('.forum-container').hasClass('new-thread'))
            $(this).html('<i class="icon-caret-left"></i> Thread list');
        else
            $(this).html('<i class="icon-chat"></i> New thread');
    });
});