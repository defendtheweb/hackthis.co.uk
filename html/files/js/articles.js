$(function() {
    $(".article-sidebar .sticky").sticky({topSpacing:45});
    $(".article-sidebar ul.categories a").on('click', function(e) {
        if (!$(this).siblings('ul').length)
            return;
        e.preventDefault();
        $(this).parent().toggleClass('active');
    });


    function checkSuggestion() {
        var viewport_bottom = $window.scrollTop() + $window.height();
        var height = $elem.height();
        var bottom = $elem.offset().top + $elem.height();
        if (bottom <= viewport_bottom) {
            $target.fadeIn();
        } else {
            $target.fadeOut();
        }
    }
    if ($('.article-suggest').length) {
        var $window = $(window);
        var $elem = $('article.body');
        var $target = $('.article-suggest');

        $window.scroll(checkSuggestion);

        checkSuggestion();
    }
});