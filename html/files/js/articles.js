$(function() {
    var $window = $(window);
    var $elem = $('article.body');
    var $target = $('.article-suggest');

    $window.scroll(checkSuggestion);

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
    checkSuggestion();
});