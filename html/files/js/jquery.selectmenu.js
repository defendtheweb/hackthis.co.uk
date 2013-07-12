/*! A multi-level select menu alternative - https://github.com/0x6C77/JQuery-SelectMenu v0.0.1 by @0x6C77 */

(function($) {
    $.fn.selectMenu = function() {
        this.each(function() {
            var $this = $(this);
            var $label = $this.children('label');
            var $list = $this.children('ul');
            var $items = $list.find('li');
            var width = $list.outerWidth();

            //Create hidden form field
            id = $this.attr('data-id')?$this.attr('data-id'):'';
            value = $this.attr('data-value')?$this.attr('data-value'):'';;
            var $field = $("<input/>", {type: 'hidden', id: id, name: id, value: value}).appendTo($this);

            $list.wrap($('<div>', {class: 'select-menu-container', height: 0}));
            var $container = $list.parent();

            $list.find('ul').each(function() {
                $(this).css('left', width);
            });

            $label.on('click', function(e) {
                $list.css('left', 0);
                $this.toggleClass('active');
                $container.css('height', $list.outerHeight());
            });

            $items.each(function() {
                if (!$(this).children('ul').length) return;
                $(this).addClass('sub');
            }).on('click', function(e) {
                e.stopPropagation();
                var $childList = $(this).children('ul');

                if ($childList.length) {
                    //Replace current menu with child
                    $list.css('left', $list.position().left-width);
                    $container.css('height', $childList.outerHeight());
                } else {
                    //Select value and close menu
                    $this.removeClass('active');
                    $container.css('height', 0);

                    text = $(this).attr('data-text')?$(this).attr('data-text'):$(this).text();
                    value = $(this).attr('data-value')?$(this).attr('data-value'):text;
                    $label.text(text);
                    $field.val(value);
                }
            });


            $(document).bind('click.selectMenu-hide', function(e) {
                if ($(e.target).closest('.select-menu').length != 0 || $(e.target).hasClass('select-menu')) return true;
                $this.removeClass('active');
                $container.css('height', 0);
            });
        });
    }
})(jQuery);