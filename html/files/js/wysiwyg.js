$(function() {
    $('.wysiwyg .controls a').on('click', function(e) {
        var textarea = $(this).closest('.wysiwyg').find('textarea');
        var tag = this.getAttribute('data-tag');

        var pos = tag.length + 2;

        tag = '[' + tag + '][/' + tag + ']';



        textarea.val(function( index, value ) {
            if (value) {
                tag = ' ' + tag;
                pos++;
            }
            pos += value.length;

            return value + tag;
        }).focus().setCursorPosition(pos);
    });

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
});