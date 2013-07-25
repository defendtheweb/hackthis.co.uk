$(function() {
    // $('#upload-drop').hide();
    // $('#upload-form').addClass('visible');

    $('#upload-drop').on('click', function() {
        $('#file').trigger('click');
    }).filedrop({
        fallback_id: 'file',   // an identifier of a standard file input element
        url: '/files/ajax/upload.php',              // upload handler, handles each file separately, can also be a function returning a url
        paramname: 'file',          // POST parameter name used on serverside to reference file
        error: function(err, file) {
            switch(err) {
                case 'BrowserNotSupported':
                    $('#upload-drop').hide();
                    $('#upload-form').addClass('visible');
                    break;
                case 'FileTooLarge':
                    // program encountered a file whose size is greater than 'maxfilesize'
                    // FileTooLarge also has access to the file which was too large
                    // use file.name to reference the filename of the culprit file
                    $('.msg-error').show().children('span').text('File is too large');
                    break;
                case 'FileTypeNotAllowed':
                    $('.msg-error').show().children('span').text('Invalid file type');
                    break;
                default:
                    $('.msg-error').show().children('span').text('Error uploading file');
                    break;
            }
        },
        allowedfiletypes: ['image/jpeg','image/jpg','image/png','image/gif'],   // filetypes allowed by Content-Type.  Empty array means no restrictions
        maxfiles: 1,
        maxfilesize: 5,    // max file size in MBs
        uploadFinished: function(i, file, response, time) {
            if (response == 'done')
                window.location.replace("?done");
            else 
                window.location.replace("?error");
        },
        beforeSend: function(file, i, done) {
            done();
        }
    });
});