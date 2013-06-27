$(function() {
    var container = $('.inbox-main');

    if (container.find('.new').length) {
        container.mCustomScrollbar("scrollTo", "ul li.new:first");
    } else {
        container.mCustomScrollbar("scrollTo", "li:nth-last-child(2):first");
    } 
});