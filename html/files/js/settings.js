$(function() {

    var linkTypes = [];
    linkTypes["twitter\."] = "twitter";
    linkTypes["facebook\."] = "facebook";
    linkTypes["github\."] = "github-2";
    linkTypes["youtube\."] = "youtube";
    linkTypes["reddit\."] = "reddit";
    linkTypes["soundcloud\."] = "soundcloud";
    linkTypes["dribbble\."] = "dribbble";
    linkTypes["deviantart\."] = "deviantart";
    linkTypes["flickr\."] = "flickr";
    linkTypes["plus\.google\."] = "google-plus";
    linkTypes["stackoverflow\."] = "stackoverflow";
    linkTypes["last\.fm"] = "lastfm";
    linkTypes["pinterest\."] = "pinterest";

    $('.settings-profile-website-add').on('click', function(e) {
        e.preventDefault();

        // Duplicate existing input
        var $input = $('.website-input:last');
        $input.parent().append($input.clone());

        // Clear inputs
        $('.website-input:last input').val('');
        $('.website-input:last i').attr('class', 'icon-globe');
    });

    $('.settings-profile-websites').on('keyup', 'input', function() {
        var value = $(this).val();

        for (var key in linkTypes) {
            var pattern = new RegExp("^(https?:\/\/)?(www.)?" + key + "(.*)\/(.+)", 'i');
            console.log(pattern);
            if (pattern.test(value)) {
                console.log(linkTypes[key]);
                $(this).siblings('i').attr('class', 'icon-' + linkTypes[key]);
                return;
            }
        }

        $(this).siblings('i').attr('class', 'icon-globe');
    });

});