$(function() {
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
});