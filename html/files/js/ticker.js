$(function() {
    $('.ticker-add-link').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        $('.ticker-add form').slideToggle(200);
        $('.ticker-add-link').toggleClass('active');

        bindCloseTickerAdd();
    });

    $('.ticker-add form').on('submit', function(e) {
        e.preventDefault();

        $('.ticker-add form .success').hide();
        $('.ticker-add form .error').hide();

        $.post('ticker.php', $(this).serialize(), function(data) {
            if (data.status) {
                $('.ticker-add form')[0].reset();
                $('.ticker-add form .success').fadeIn();
            } else {
                $('.ticker-add form .error').fadeIn();
            }
        }, 'json');
    });

    function bindCloseTickerAdd() {
        $(document).bind('click.ticker-hide', function(e) {
            if ($(e.target).closest('.ticker-add').length != 0 && $(e.target).not('.ticker-add-link')) return true;
            hideTickerAdd();
        });
    }

    function hideTickerAdd() {
        $('.ticker-add form').slideUp(200);
        $(document).unbind('click.ticker-hide');  
        $('.ticker-add-link').removeClass('active');
    }


    $('.ticker .ticker-up').on('click', function(e) {
        e.preventDefault();

        $(this).parent().addClass('voted');

        var $points = $(this).siblings().not('.ticker-up-voted').find('.points');
        $points.text(parseInt($points.text()) + 1);

        var tid = $(this).parent().attr('data-id');

        $.post('ticker.php', {tid: tid, action: 'vote'});
    });

    $('.icon-remove').on('click', function(e) {
        e.preventDefault();

        $(this).parent().slideUp();

        var tid = $(this).parent().attr('data-id');

        $.post('ticker.php', {tid: tid, action: 'decline'});
    });

    $('.icon-ok').on('click', function(e) {
        e.preventDefault();

        $(this).parent().slideUp();

        var tid = $(this).parent().attr('data-id');

        $.post('ticker.php', {tid: tid, action: 'accept'});
    });
});