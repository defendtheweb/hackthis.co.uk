var loggedIn = true; // overwritten in guest.js

$(function() {
    $('textarea').autosize();

    // Confirmation box
    $('.confirmation').on('click', function(e) {
        if ($(this).attr('data-confirm'))
            return confirm($(this).attr('data-confirm') + ", are you sure?");
        else
            return confirm("Are you sure?");
    });

    // Update timestamps
    (function updateTimes() {
        $('time').each(function(index, value) {
            $this = $(this);
            d = new Date($this.attr('datetime'));
            $this.text(timeSince(d, $this.hasClass('short')));
        });

        setTimeout(updateTimes, 10000);
    })();
});

if (!Date.prototype.toISOString) {
    Date.prototype.toISOString = function() {
        function pad(n) { return n < 10 ? '0' + n : n }
        return this.getUTCFullYear() + '-'
            + pad(this.getUTCMonth() + 1) + '-'
            + pad(this.getUTCDate()) + 'T'
            + pad(this.getUTCHours()) + ':'
            + pad(this.getUTCMinutes()) + ':'
            + pad(this.getUTCSeconds()) + 'Z';
    };
}


function timeSince(oldD, short) {
    if (Object.prototype.toString.call(oldD) !== "[object Date]")
        oldD = new Date(oldD);

    var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    newD = new Date();
    diff = Math.round((newD.getTime() - oldD.getTime()) / 1000);
    
    isSameDay = (oldD.getDate() == newD.getDate() 
                 && oldD.getMonth() == newD.getMonth()
                 && oldD.getFullYear() == newD.getFullYear());

    if (isSameDay) {
        if (diff < 60) {
            return "secs" + (!short ? " ago" : '');
        } else if (diff < 3600) {
            var n = Math.floor(diff/60);
            return n + " min" + (n==1?'':'s') + (!short ? " ago" : '');
        } else {
            var n = Math.floor(diff/3600);
            return n + " hour" + (n==1?'':'s') + (!short ? " ago" : '');
        }
    } else {
        newD.setDate(newD.getDate() - 1);

        isYesterday = (oldD.getDate() == newD.getDate() 
                     && oldD.getMonth() == newD.getMonth()
                     && oldD.getFullYear() == newD.getFullYear());

        if (isYesterday) {
            return "Yesterday";
        } else {
            newD.setDate(newD.getDate() - 6);
            if (oldD > newD)
                return days[oldD.getDay()];
            else {
                var day = oldD.getDate();
                var month = oldD.getMonth()+1;
                return ((day < 10) ? '0':'') + day + "/" + ((month < 10) ? '0':'') + month + (!short ? "/" + oldD.getFullYear() : '');
            }
        }
    }
}