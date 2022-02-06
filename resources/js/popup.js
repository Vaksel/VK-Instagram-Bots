var spinner = new jQuerySpinner({
    parentId: 'loader',
});

$(document).ready(function($) {

    $('.instagram-activity form').submit(function() {

        $('.popup-fade').fadeIn();

        spinner.show();

    });

    $('.popup-fade').click(function(e) {

        if ($(e.target).closest('.popup').length == 0) {

            $(this).fadeOut();

        }

    });

});

