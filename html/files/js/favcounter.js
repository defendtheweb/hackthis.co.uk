/*! A small JS library to add a number overlay to a pages favicon - https://github.com/0x6C77/favcounter v0.0.1 by @0x6C77 */

var FavCounter = (function(options) {
    var _canvas,
        _element,
        _img,
        _options,
        _default = {
            bg: '#00BB00',
            text: '#000000'
        };

    _options = merge_options(_default, (options) ? options : {});

    set = function (count) {
        _canvas = getCanvas();
        if (_canvas.getContext) {
            setImg(count);
        }
    }

    function merge_options(obj1, obj2) {
        var obj3 = {};
        for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
        for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
        return obj3;
    }

    var getCanvas = function () {
        if (_canvas)
            return _canvas;

        var canvas = document.createElement('canvas');

        if (!canvas.getContext) {
            return false;
        }

        return canvas;
    }

    var setImg = function (count) {
        count = count <= 99 ? count : 99;

        if (!_img) {
            url = getFavicon();
            _img = document.createElement('img');
            _img.setAttribute('src', url);
            _img.onload = function() {
                height = this.height;
                width = this.width;

                _canvas.height = height;
                _canvas.width = width;

                drawCount(count, _canvas);

                setIcon(_canvas.toDataURL('image/png'));
            };
        } else {            
            drawCount(count, _canvas);

            setIcon(_canvas.toDataURL('image/png'));
        }
    }

    var getFavicon = function () {
        var favicon = undefined;
        var nodeList = document.getElementsByTagName("link");
        for (var i = 0; i < nodeList.length; i++) {
            if((nodeList[i].getAttribute("rel") == "icon")||(nodeList[i].getAttribute("rel") == "shortcut icon")) {
                _element = nodeList[i];
                favicon = _element.getAttribute("href");
            }
        }
        return favicon;
    }

    var drawCount = function (count, canvas) {
        context = canvas.getContext('2d');
        context.drawImage(_img, 0, 0);

        if (count > 0 || count < 0) {
            height = canvas.height;
            width = canvas.width;

            // Draw counter
            h = height/1.5;
            w = width/1.3;
            x = width - w;
            y = height - h;

            radius = 4;
            context.beginPath();

            context.moveTo(x + radius, y);
            context.lineTo(x + w - radius, y);
            context.quadraticCurveTo(x + w, y, x + w, y + radius);
            context.lineTo(x + w, y + h - radius);
            context.quadraticCurveTo(x + w, y + h, x + w - radius, y + h);
            context.lineTo(x + radius, y + h);
            context.quadraticCurveTo(x, y + h, x, y + h - radius);
            context.lineTo(x, y + radius);
            context.quadraticCurveTo(x, y, x + radius, y);


            context.fillStyle = _options.bg;
            context.fill();
            context.closePath();
            context.beginPath();
            context.stroke();

            x = x + 5;
            y = y + 4;
            h = height/1.8;
            w = width/1.8;
            context.font = "bold " + Math.floor(h) + "px sans-serif";
            context.textAlign = 'center';
            context.fillStyle = _options.text;
            context.fillText(count, Math.floor(x + w / 2), Math.floor(y + h - h * 0.15));
            context.closePath();
        }
    }

    var setIcon = function (data) {
        _element.parentNode.removeChild(_element);

        newElm = document.createElement('link');
        newElm.setAttribute('rel', 'icon');
        newElm.setAttribute('href', data);
        _element = document.getElementsByTagName('head')[0].appendChild(newElm);
    };


    return {
        set: set
    };
});