$(function() {

    /* LEVEL EDITOR */
    $('.add-field').on('click', function(e) {
        e.preventDefault();

        html = '<div class="clr">\
                    <select style="width: auto" class="tiny" name="form_type[]">\
                        <option>Text</option>\
                        <option>Password</option>\
                    </select>\
                    <input type="text" placeholder="Label" class="span_6" name="form_label[]">\
                    <input type="text" placeholder="Name" class="span_6" name="form_name[]">\
                </div>';

        $(this).before(html);
    });

    $('.add-answer').on('click', function(e) {
        e.preventDefault();

        html = '<div class="clr">\
                    <select name="answer_method[]" style="width: auto" class="tiny">\
                        <option>GET</option>\
                        <option>POST</option>\
                    </select>\
                    <input name="answer_name[]" type="text" placeholder="Name" class="span_6">\
                    <input name="answer_value[]" type="text" placeholder="Value" class="span_6">\
                </div>';

        $(this).before(html);
    });
});