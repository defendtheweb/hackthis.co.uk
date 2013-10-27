$.fn.autosuggest = function() {
    this.each(function () {
        $self = $(this);

        $self.keyup(function(event) {
            var $this = $(this);
            var auto = $this.attr('data-suggest-at')==='false'?false:true;

            var caret = $this.getCursorPosition().end;
            var val = this.value + ' ';
            var word = /\S+$/.exec(val.slice(0, val.indexOf(' ', caret)));

            if (!word) {
                $this.siblings('.autosuggest').remove();
                return;
            }

            word = word[0];

            if (auto) {
                if (word.substr(0,1) !== '@') {
                    $this.siblings('.autosuggest').remove();
                    return;
                }
                word = word.substr(1);
            }

            var max = $this.attr('data-suggest-max')?$this.attr('data-suggest-max'):5;
            
            if (word.length < 3)
                return false;

            //lookup word
            $.get('/files/ajax/autosuggest.php', {user: word, max: max}, function(data) {
                $this.siblings('.autosuggest').remove();
                
                var list = $('<ul>', {class: 'autosuggest'});
                if (!auto) {
                    list.addClass('autosuggest-alt');
                }

                if (data.status == false)
                    return;

                for (var i = 0; i < data.users.length; ++i) {
                    user = data.users[i];

                    var icon = $('<i>', {class: 'icon-addfriend'});
                    var link = $('<a>', {text: user.username, href: '#'+user.username});
                    if (user.friends == 1)
                        link.append(icon);
                    $('<li>').append(link).appendTo(list);
                }

                $this.after(list);
            }, 'json');

            $(document).bind('click.suggest-hide', function(e) {
                if ($(e.target).closest('.autosuggest').length != 0 || $(e.target).hasClass('suggest')) return true;
                $('.autosuggest').remove();
                $(document).unbind('click.suggest-hide');
            });
        });

        $self.parent().on('click', '.autosuggest a', function(e) {
            var $this = $(this);
            e.preventDefault();
            e.stopPropagation();

            var $self = $this.closest('.autosuggest').prev();
            var auto = $self.attr('data-suggest-at')==='false'?false:true;

            $this.closest('.autosuggest').remove();
            var insert = this.hash.slice(1);
            if (!auto) insert += ",";

            tmp = $self.val() + ' ';

            var caret = $self.getCursorPosition().end;
            var wordEnd = tmp.indexOf(' ', caret);
            var word = /\S+$/.exec(tmp.slice(0, wordEnd));

            if (auto)
                var start = tmp.substr(0, wordEnd-word[0].length+1);
            else
                var start = tmp.substr(0, wordEnd-word[0].length);
            var end = tmp.substr(wordEnd);

            var tmp = start + insert + end;
            $self.val(tmp).focus().setCursorPosition(start.length+insert.length+1);
        });
    });
};


function searchsuggest() {
    var $this = $('.nav-search input'),
    value = $this[0].value;

    if (value.length < 3) {
        $this.parent().siblings('.searchsuggest').remove();
        return false;
    }

    $.get('/files/ajax/autosuggest.php', {search: value}, function(data) {
        // console.log(data);

        if (data.status) {
            var suggest = $('<div>', {class: 'searchsuggest'});

            if (data.data.users) {
                var users = data.data.users;
                title = $('<h3>', {text: 'Users'});
                suggest.append(title);

                len = users.length < 5 ? users.length : 5;
                for (var i = 0; i < len; ++i) {
                    link = $('<a>', {text: users[i].username, href: '/user/'+users[i].username});
                    suggest.append(link);
                }
            }

            if (data.data.articles) {
                var articles = data.data.articles;
                title = $('<h3>', {text: 'Articles'});
                suggest.append(title);

                len = articles.length < 5 ? articles.length : 5;
                for (var i = 0; i < len; ++i) {
                    link = $('<a>', {text: articles[i].title, href: '/articles/'+articles[i].slug});
                    suggest.append(link);
                }
            }

            if (data.data.forum) {
                var forum = data.data.forum;
                title = $('<h3>', {text: 'Forum'});
                suggest.append(title);

                len = forum.length < 5 ? forum.length : 5;
                for (var i = 0; i < len; ++i) {
                    link = $('<a>', {text: forum[i].title, href: '/forum/'+forum[i].slug});
                    suggest.append(link);
                }
            }


            $this.parent().siblings('.searchsuggest').remove();
            $this.parent().after(suggest);
        } else {
            $this.parent().siblings('.searchsuggest').remove();
        }
    }, 'json');

    $(document).bind('click.search-hide', function(e) {
        if ($(e.target).closest('.searchsuggest').length != 0 || $(e.target).hasClass('suggest')) return true;
        $('.searchsuggest').remove();
        $(document).unbind('click.search-hide');
    });
}

$(function() {
    var timer = null;
    $('.suggest').autosuggest();

    // navigation search bar
    $('.nav-search input').keyup(function(event) {
        if (timer) {
            clearTimeout(timer);
        }

        timer = setTimeout(searchsuggest, 200);
    });
});
