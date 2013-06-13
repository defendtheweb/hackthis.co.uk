$(function() {
    $('.profile').on('mouseover', '.removefriend', function(e){
        $(this).html('<i class="icon-removefriend"></i> Remove');
    }).on('mouseout', '.removefriend', function(e){
        $(this).html('<i class="icon-user"></i> Friends');
    });


 $('.remove').click(function(){
        var fid = $(this).attr('data-fid');
        var $elem = $(this).closest('li');

        $.confirm({
            title   : 'Delete Confirmation',
            message : 'Are you sure you want to remove this activity from your feed? <br />It cannot be restored at a later time! Continue?',
            buttons : {
                No  : {},
                Yes : {
                    action: function(){
                        // Remove item from feed
                        var uri = '/files/ajax/user.php?action=feed.remove&id=' + fid;
                        $.getJSON(uri, function(data) {
                            if (data.status) {
                                $elem.slideUp();
                            }
                        });
                    }
                }
            }
        });

    });

    $('.profile').on('click', '.addfriend, .acceptfriend, .removefriend', function(e) {
        e.preventDefault();
        var $this = $(this);

        if ($this.hasClass('addfriend') || $this.hasClass('acceptfriend'))
            var uri = '/files/ajax/user.php?action=friend.add&uid=';
        else
            var uri = '/files/ajax/user.php?action=friend.remove&uid=';
        uri += $(this).attr('data-uid');

        $.getJSON(uri, function(data) {
            if (data.status) {
                if ($this.hasClass('addfriend')) {
                    $this.html('Pending').removeClass('addfriend').addClass('button-disabled');
                } else if ($this.hasClass('acceptfriend')) {
                    $this.html('<i class="icon-user"></i> Friends').removeClass('acceptfriend').addClass('button-blank removefriend');
                } else if ($this.hasClass('removefriend')) {
                    $this.html('<i class="icon-addfriend"></i> Add friend').removeClass('removefriend button-blank').addClass('addfriend');
                }

            }
        });
    });
});