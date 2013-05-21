$.fn.setCursorPosition = function(pos) {
    this.each(function(index, elem) {
        if (elem.setSelectionRange) {
            elem.setSelectionRange(pos, pos);
        } else if (elem.createTextRange) {
            var range = elem.createTextRange();
            range.collapse(true);
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
    });
    return this;
};

$.fn.getCursorPosition = function() {
    var el = $(this).get(0);
    var posStart = 0;
    if('selectionStart' in el) {
        posStart = el.selectionStart;
        posEnd = el.selectionEnd;
    } else if('selection' in document) {
        el.focus();
        var range = document.selection.createRange();
        posStart = 0 - range.duplicate().moveStart('character', -100000);
        posEnd = posStart + range.text.length;
    }
    return {start: posStart, end: posEnd};
};