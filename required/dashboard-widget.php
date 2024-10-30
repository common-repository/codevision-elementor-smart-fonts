<?php

add_action( 'wp_dashboard_setup', 'cv_add_dashboard_widget', 990 );
add_action( 'wp_dashboard_setup', 'cv_order_dashboard_widgets', 999 );


function cv_add_dashboard_widget() {

    $widget_id = apply_filters('cv_widget_id', 'cv_widget_id');
    $widget_name = apply_filters('cv_widget_name', uniqid());

    wp_add_dashboard_widget( $widget_id, $widget_name, 'cv_dashboard_widget');
}

function cv_order_dashboard_widgets() {
    global $wp_meta_boxes;

    $widget_id = apply_filters('cv_widget_id', 'cv_widget_id');

    $normal_dashboard = $wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ];

    $widget_backup = [
        $widget_id => $normal_dashboard[ $widget_id ],
    ];

    $wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ] = $widget_backup;
}

function cv_dashboard_widget() {

    $target = apply_filters('cv_widget_url', 'https://www.codevision.io');

    $widget_id = apply_filters('cv_widget_id', 'cv_widget_id');

    ?>
    <div class="cv-widget">
        <div class="lds-css ng-scope iframe-loader" style="margin-top: 100px">
            <div style="width:100%;height:100%" class="lds-ripple">
                <div></div>
                <div></div>
            </div>
        </div>
        <iframe data-src="<?php echo $target ?>" class="hidden"></iframe>
    </div>

    <style type="text/css">

        #<?php echo $widget_id?> {
            margin: 0;
        }

        #<?php echo $widget_id?>.postbox .inside {
            margin: 0;
            padding: 0;
        }

        #<?php echo $widget_id?> iframe {
            width: 100%;
            height: auto;
            min-height: 90vh;
            border: none;
        }

        #<?php echo $widget_id?> iframe.hidden {
             display: none;
        }

        @keyframes lds-ripple {
            0% {
                top: 96px;
                left: 96px;
                width: 0;
                height: 0;
                opacity: 1;
            }
            100% {
                top: -22px;
                left: -22px;
                width: 236px;
                height: 236px;
                opacity: 0;
            }
        }

        @-webkit-keyframes lds-ripple {
            0% {
                top: 96px;
                left: 96px;
                width: 0;
                height: 0;
                opacity: 1;
            }
            100% {
                top: -22px;
                left: -22px;
                width: 236px;
                height: 236px;
                opacity: 0;
            }
        }

        .lds-ripple {
            position: relative;
            margin: 0 auto;
        }

        .lds-ripple div {
            box-sizing: content-box;
            position: absolute;
            border-width: 4px;
            border-style: solid;
            opacity: 1;
            border-radius: 50%;
            -webkit-animation: lds-ripple 1s cubic-bezier(0, 0.2, 0.8, 1) infinite;
            animation: lds-ripple 1s cubic-bezier(0, 0.2, 0.8, 1) infinite;
        }

        .lds-ripple div:nth-child(1) {
            border-color: #1d3f72;
        }

        .lds-ripple div:nth-child(2) {
            border-color: #5699d2;
            -webkit-animation-delay: -0.5s;
            animation-delay: -0.5s;
        }

        .lds-ripple {
            width: 200px !important;
            height: 200px !important;
            -webkit-transform: translate(-100px, -100px) scale(1) translate(100px, 100px);
            transform: translate(-100px, -100px) scale(1) translate(100px, 100px);
        }

    </style>

    <script>
        (function ($) {

            'use strict';

            $(function () {

                var $frame = $('#<?php echo $widget_id?> iframe'),
                    $loader = $('#<?php echo $widget_id?> .iframe-loader');

                $frame.attr('src', $frame.data('src')).on('load', function(){
                    $loader.remove();
                    $frame.removeClass('hidden');
                });

            });

        })(jQuery);

    </script>
<?php
}
