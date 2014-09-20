// Adds a CSRF token to all ajax requests with
// "hackthis.co.uk" as their target hostname. The
// token is added as a parameter to the url.
(function(jQuery) {
    var dataKey = jQuery('body').attr('data-key');
    if(typeof dataKey !== 'string' || dataKey === '') return;

    // The GET param name to be added
    var TOKEN_NAME = 'ajax_csrf_token';
    // The value for this user
    var TOKEN_VALUE = dataKey;
    // list of hostnames that the token is added for
    var ALLOWED_HOSTNAMES = ['www.hackthis.co.uk', 'hackthis.co.uk', 'localhost'];

    // The token string added to the url of each request
    var TOKEN_STRING = TOKEN_NAME + '=' + TOKEN_VALUE;

    jQuery.ajaxPrefilter(function(options) {
        // Resolve the url, grab the hostname
        var tempLink = document.createElement("a");
        tempLink.href = options.url;
        // Relative links in IE gives "" as hostname
        var hostname = tempLink.hostname || window.location.hostname;

        if (ALLOWED_HOSTNAMES.indexOf(hostname) > -1) {
            var urlParts = options.url.split('?');
            var queryString = urlParts[1];

            if (typeof queryString === 'undefined') {
                queryString = TOKEN_STRING;
            } else if (queryString.indexOf(TOKEN_NAME) === -1) {
                queryString = TOKEN_STRING + '&' + queryString;
            }

            options.url = urlParts[0] + '?' + queryString;
        }
    });

})(jQuery);
