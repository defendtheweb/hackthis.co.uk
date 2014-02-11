$(function() {
    var time = 4500,
    	active = 0,
    	$list = $('.home-ticker ul li'),
        timer;

    $list.children('a').addClass('hide-external');

    function stopTimer() {
        clearTimeout(timer);
    }

    function setTimer() {
        timer = setTimeout(function() {
            ticker_next();
        }, time);
    }

    function ticker_next() {
    	$($list.get(active)).css('top', '-20px');    	

    	// move last item back to bottom
    	$list.show();
    	if (active > 0)
    		$($list.get(active - 1)).hide().css('top', '20px');
    	else
    		$($list.get($list.length - 1)).hide().css('top', '20px');

    	active++;

    	// if last item go back
    	if (active == $list.length) {
    		active = 0;
    	}

    	$($list.get(active)).css('top', '0px');

    	setTimer();
    }

    $(".home-ticker").hover(stopTimer, setTimer);

    setTimer();
});