$(function() {

    var tmpl_user_details = '<tmpl><h2>${username}</h2>\
                            <img src="${image}" width="67px" height="67px"/>\
                            <ul class="plain">\
                                <li>#${uid}</li>\
                                <li>${email}</li>\
                                <li><span>Score:</span> ${score}</li>\
                                <li><span>Levels:</span> N/A</li>\
                            </ul>\
                            <div class="privilages">\
                                <span class="medal {{if site_priv > 1}}medal-gold{{else site_priv < 1}}medal-error{{/if}}">Site</span>\
                                <span class="medal {{if pm_priv > 1}}medal-gold{{else pm_priv < 1}}medal-error{{/if}}">PM</span>\
                                <span class="medal {{if forum_priv > 1}}medal-gold{{else forum_priv < 1}}medal-error{{/if}}">Forum</span>\
                                <span class="medal {{if pub_priv > 1}}medal-gold{{else pub_priv < 1}}medal-error{{/if}}">Publish</span>\
                            </div></tmpl>';

    /* DASHBOARD */
    $('.admin-module-user-manager input').on('keyup', function() {
        var $this = $(this);
        delay(function() {
            $('.user-details').html("Loading...");
            var term = $this.val();
            $.getJSON('/api/?method=user.profile&user='+term, function(data) {
                if (data.profile && data.profile.username) {
                    $('.user-details').html($(tmpl_user_details).tmpl(data.profile));
                } else {
                    $('.user-details').html("User not found");
                }
            });
        }, 500);
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