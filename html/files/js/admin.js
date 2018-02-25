$(function() {

    var tmpl_user_details = '<tmpl><h2>${username}</h2>\
                            <img src="${image}" width="67px" height="67px"/>\
                            <ul class="plain">\
                                <li>#${uid}</li>\
                                <li>${email}</li>\
                                <li><span>Score:</span> ${score}</li>\
                                <li data-uid="${uid}">\
                                    <a href="#" data-medal="contributor" class="medal {{if medal_contributor }}medal-green{{/if}}">Contributor</a>\
                                    <a href="#" data-medal="helper" class="medal {{if medal_helper }}medal-green{{/if}}">Helper</a>\
                                </li>\
                            </ul>\
                            <div class="privilages" data-uid="${uid}">\
                                <a href="#" data-priv="site" class="medal {{if site_priv > 1}}medal-gold{{else site_priv < 1}}medal-error{{/if}}">Site</a>\
                                <a href="#" data-priv="pm" class="medal {{if pm_priv > 1}}medal-gold{{else pm_priv < 1}}medal-error{{/if}}">PM</a>\
                                <a href="#" data-priv="forum" class="medal {{if forum_priv > 1}}medal-gold{{else forum_priv < 1}}medal-error{{/if}}">Forum</a>\
                                <a href="#" data-priv="pub" class="medal {{if pub_priv > 1}}medal-gold{{else pub_priv < 1}}medal-error{{/if}}">Publish</a>\
                            </div></tmpl>';

    /* DASHBOARD */
    $('.admin-module-user-manager input').on('keydown', function(e) {
        if (e.which == 13) { // ignoring enter
            e.preventDefault();
            return;
        }
    });

    $('.admin-module-user-manager input').on('keyup', function(e) {
        var $this = $(this);

        delay(function() {
            $('.user-details').html("Loading...");
            var term = $this.val();
            $.getJSON('/api/?method=user.profile&user='+term, function(data) {
                if (data.profile && data.profile.username) {

                    // Check if they have medals
                    $.each(data.profile.medals, function() {
                        if (this.label == 'Contributor') {
                            data.profile.medal_contributor = true;
                        } else if (this.label == 'helper') {
                            data.profile.medal_helper = true;
                        }
                    });

                    $('.user-details').html($(tmpl_user_details).tmpl(data.profile));
                } else {
                    $('.user-details').html("User not found");
                }
            });
        }, 500);
    });

    // User controls
    $('.admin-module-user-manager-editable, .admin-module-moderators-editable').on('click', 'a.medal', function(e) {
        e.preventDefault();

        var $this = $(this),
            value;

        // Manage privilages
        if ($this.data('priv')) {
            if ($this.hasClass('medal-gold')) {
                $this.removeClass('medal-gold');
                value = 1;
            } else if ($this.hasClass('medal-error')) {
                $this.removeClass('medal-error');
                $this.addClass('medal-gold');
                value = 2;
            } else {
                $this.addClass('medal-error');
                value = 0;
            }

            var data = {};
            data.uid = $this.parent().data('uid');
            data.priv = $this.data('priv');
            data.priv_value = value;
            console.log(data);

            $.post('/api/?method=user.admin.priv', data);
        } else if ($this.data('medal')) {
            // Manage medals
            if ($this.hasClass('medal-green')) {
                $this.removeClass('medal-green');
                value = 0;
            } else {
                $this.addClass('medal-green');
                value = 1;
            }

            var data = {};
            data.uid = $this.parent().data('uid');
            data.medal = $this.data('medal');
            data.medal_value = value;
            console.log(data);

            $.post('/api/?method=user.admin.medal', data);
        }
    });

    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    // Flag controls
    $('.flags a.remove').on('click', function(e) {
        e.preventDefault();
        var $this = $(this),
            $row = $(this).closest('tr');
        
        $.getJSON('forum.php?action=flag.remove&id='+$row.attr('data-pid'), function(data) {
            if (data.status === true) {
                $row.slideUp();
            }
        });
    });






    /* LEVEL EDITOR */
    $('.add-field').on('click', function(e) {
        e.preventDefault();

        html = '<div class="clr">\
                    <select style="width: auto" class="tiny" name="form_type[]">\
                        <option>Text</option>\
                        <option>Password</option>\
                    </select>\
                    <input type="text" placeholder="Label" class="span_6" name="form_label[]">\
                    <input type="text" placeholder="Name" class="span_6" name="form_name[]">\
                </div>';

        $(this).before(html);
    });

    $('.add-answer').on('click', function(e) {
        e.preventDefault();

        html = '<div class="clr">\
                    <select name="answer_method[]" style="width: auto" class="tiny">\
                        <option>GET</option>\
                        <option>POST</option>\
                    </select>\
                    <input name="answer_name[]" type="text" placeholder="Name" class="span_6">\
                    <input name="answer_value[]" type="text" placeholder="Value" class="span_6">\
                </div>';

        $(this).before(html);
    });
});