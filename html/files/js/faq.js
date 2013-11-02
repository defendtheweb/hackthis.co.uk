$(function() {
    $('.donate input').on('keyup', function() {
        var value = Number(this.value.replace(/[^0-9\.]+/g,""));

        if (value >= 20)
            $('.donate-perk').slideDown();
        else
            $('.donate-perk').slideUp();
    });

    $('input[name="js"]').val('true');
});