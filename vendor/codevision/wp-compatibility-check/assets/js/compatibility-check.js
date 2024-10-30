(function ($) {

    'use strict';

    var do_reload = true,
        in_check = false,
        finish = function( reload ) {

            $.post( ajaxurl, {
                'action' : 'codevision-compatibility-check-done',
            }, function (resp) {

                if ( ! reload ) {
                    return;
                }

                if ( ! resp.reload ) {
                    return;
                }

                in_check = false;

                location.reload(true);
            });
        },
        validate = function() {
            var $t = $(this),
                $parent = $t.closest('.codevision-compatibility-check'),
                $icon = $t.find('.dashicons'),
                $error = $t.find('.error-message'),
                $next = $t.closest('.compatibility-checks'),
                $final = $parent.find('.final-message');


            $icon.attr('class','updating-message');
            $t.removeClass('state-unresolved').addClass('state-in_progress');

            in_check = true;

            $.post( ajaxurl, {
                'action' : 'codevision-compatibility-check',
                'hash' : $t.data('hash')
            }, function (resp) {

                $t.removeClass('state-in_progress');
                $icon.attr('class', 'dashicons dashicons-no file-error');

                if ( resp.stop_processing ) {

                    $final
                        .removeClass('hidden')
                        .addClass('file-error')
                        .html( resp.final_message );

                    $error.html( resp.message );

                    $t.addClass('state-error');
                    $t.closest('.notice').removeClass('notice-info').addClass('notice-error');
                    $('#message').remove();
                    in_check = false;
                    return;
                }

                if (resp.ok) {
                    $t.addClass('state-successful');
                    $icon.attr('class', 'updated-message');
                } else {
                    $t.addClass('state-error');
                    $error.html( resp.message );
                    $t.closest('.notice').removeClass('notice-info').addClass('notice-error');
                    $('#message').remove();
                    do_reload = false;
                    in_check = false;
                }

                $next = $next.find('.state-unresolved:first');

                if ( $next.length === 0) {

                    in_check = false;

                    setTimeout(function(){
                        finish( do_reload );
                    }, 1000);

                    return;
                }

                $next.trigger('codevision-compatibility-check');
            });
        };

    $(function () {
        var $check = $('.compatibility-checks');

        $check.find('.simple-check').on('codevision-compatibility-check', validate );

        setTimeout(function(){
            $check.find('.state-unresolved:first').trigger('codevision-compatibility-check');
        }, 1000);


        $(window).on('beforeunload', function () {

            if ( ! in_check ) {
                return;
            }

            return 'We are currently checking your wordpress environment! Please do not leave the page before we are finished!';

        });

    });

})(jQuery);


