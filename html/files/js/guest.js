var loggedIn = false;

$(function() {
    var tests = {
        user: function (val) {
            return val.length > 3 &&
                   val.length <= 16 &&
                   /^[0-9A-Za-z_-][0-9A-Za-z_.-]*[0-9A-Za-z_-]$/.test(val);
        },

        email: function (val) {
            return /^(?:\w+\.?)*\w+@(?:\w+\.)+\w+$/.test(val);
        },
          
        minLength: function (val, length) {
            return val.length >= length;
        },
          
        maxLength: function (val, length) {
            return val.length <= length;
        },
          
        equal: function (val1, val2) {
            return (val1 == val2);
        }
    };

    $('#login_form').isHappy({
        fields: {
            '#username': {
                required: true
            },
            '#password': {
                required: true
            }
        }
    });

    $('#registration_form').isHappy({
        fields: {
            '#reg_username': {
                required: true,
                test: tests.user
            },
            '#reg_password': {
                required: true
            },
            '#reg_password_2': {
                required: true,
                test: tests.equal,
                arg: function () {
                    return $('#reg_password').val();
                }
            },
            '#reg_email': {
                required: true,
                test: tests.email
            }
        }
    });
});