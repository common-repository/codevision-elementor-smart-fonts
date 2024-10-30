<?php


class ESF_Acf {


    public function init_bundle() {

        add_filter( 'acf/settings/path', [$this, 'acf_settings_path']  );
        add_filter( 'acf/settings/dir', [$this, 'acf_settings_dir'] );
        add_filter( 'acf/settings/load_json', [$this, 'acf_json_load_point'] );

        if ( strpos( site_url(), 'localhost' ) === false ) {
            add_filter( 'acf/settings/show_admin', '__return_false' );
        }

        include_once( __DIR__ . '/acf/acf.php' );

        return $this;

    }

    function acf_json_load_point( $paths ) {


        // append path
        $paths[] = __DIR__ . '/acf/settings';

        // return
        return $paths;

    }

    function acf_settings_path( $path ) {

        // update path
        $path = __DIR__ . '/acf/';

        // return
        return $path;

    }

    function acf_settings_dir( $dir ) {

        $dir = plugin_dir_url( __FILE__ ) . '/acf/';

        // return
        return $dir;

    }

}


if ( ! function_exists('cv_acf_json_save_point') ) {

    add_filter( 'acf/settings/save_json', 'cv_acf_json_save_point' );

    function cv_acf_json_save_point( $path ) {

        // update path
        $path = __DIR__ . '/acf/settings';


        // return
        return $path;

    }
}

$esf_acf = new ESF_Acf();
$esf_acf->init_bundle();
