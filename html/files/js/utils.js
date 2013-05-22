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
    var months = new Array("January", "February", "March", 
                        "April", "May", "June", "July", "August", "September", 
                        "October", "November", "December");

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
                if (short) {
                    var day = oldD.getDate();
                    var month = oldD.getMonth()+1;
                    return ((day < 10) ? '0':'') + day + "/" + ((month < 10) ? '0':'') + month;
                } else {
                    return months[oldD.getMonth()] + ' ' + oldD.getDate() + ', ' + oldD.getFullYear();
                }
            }
        }
    }
}


$.fn.setCursorPosition = function(pos) {
    this.each(function(index, elem) {
        if (elem.setSelectionRange) {
            elem.setSelectionRange(pos, pos);
        } else if (elem.createTextRange) {
            var range = elem.createTextRange();
            range.collapse(true);
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
    });
    return this;
};

$.fn.getCursorPosition = function() {
    var el = $(this).get(0);
    var posStart = 0;
    if('selectionStart' in el) {
        posStart = el.selectionStart;
        posEnd = el.selectionEnd;
    } else if('selection' in document) {
        el.focus();
        var range = document.selection.createRange();
        posStart = 0 - range.duplicate().moveStart('character', -100000);
        posEnd = posStart + range.text.length;
    }
    return {start: posStart, end: posEnd};
};