$.fn.share = function() {
    this.each(function () {
        self = this;
        $self = $(self);

        var item_id = $self.attr("data-id");
        var link = "http://www.hackthis.co.uk"+$self.attr('data-link');
        var title = escape($self.attr('data-title'));

        //Closures rock!
        loadData = function() {
            var $tmp = $self;
            // Grab twitter stats
            $.getJSON("http://urls.api.twitter.com/1/urls/count.json?url="+link+"&callback=?", function(data) {
                count = data.count?data.count:'0';
                $tmp.find('a.twitter').append($('<span>', {text: count}));
            });

            // Grab twitter stats
            $.getJSON("https://graph.facebook.com/"+link, function(data) {
                shares = data.shares?data.shares:'0';
                $tmp.find('a.facebook').append($('<span>', {text: shares}));
            });
        }();

        $self.find('a').on('click', function(e) {
            var $this = $(this);
            if ($this.hasClass('comments'))
                return;

            e.preventDefault();

            if ($this.hasClass('favourite')) {
                var count = parseInt($this.children('span').text());

                if (!$this.children('i').hasClass('icon-heart')) {
                    // Add
                    $.post('/files/ajax/comments.php?action=favourite', {id: item_id}, function(data) {
                        if (data.status) {
                            $this.children('span').text(count+1);
                            $this.children('i').removeClass('icon-heart-2').addClass('icon-heart');
                        }
                    }, 'json');
                } else {
                    // Remove
                    $.post('/files/ajax/comments.php?action=unfavourite', {id: item_id}, function(data) {
                        if (data.status) {
                            $this.children('span').text(count-1);
                            $this.children('i').addClass('icon-heart-2').removeClass('icon-heart');
                        }
                    }, 'json');
                }
            } else if ($this.hasClass('twitter') || $this.hasClass('facebook')) {
                if ($this.hasClass('twitter'))
                    uri = 'http://twitter.com/share?url=' + link + '&via=hackthisuk&text=' + title;
                else
                    uri = 'http://www.facebook.com/share.php?u=' + link;

                PopupCenter(uri,'Share','600','250'); 
            }
        });
    });
};

$(function() {
    $('.share').share();
});