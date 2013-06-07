$(function() {
    $('.profile').on('mouseover', '.removefriend', function(e){
        $(this).html('<i class="icon-removefriend"></i> Remove');
    }).on('mouseout', '.removefriend', function(e){
        $(this).html('<i class="icon-user"></i> Friends');
    });


    $('.profile').on('click', '.addfriend, .acceptfriend, .removefriend', function(e) {
        e.preventDefault();
        var $this = $(this);

        if ($this.hasClass('addfriend') || $this.hasClass('acceptfriend'))
            var uri = '/files/ajax/user.php?action=add&uid=';
        else
            var uri = '/files/ajax/user.php?action=remove&uid=';
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