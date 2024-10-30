<?php
/**
 * Created by PhpStorm.
 * User: b2wsra
 * Date: 2019-02-18
 * Time: 19:54
 */

namespace Codevision\Compatibility;


use Codevision\Environment;

class CompatibilityCheck {

    /**
     * @var array<Check>
     */
    private $checks;

    /**
     * @var string
     */
    private $title;


    /**
     * @var Environment
     */
    private $env;

    /**
     * @var int
     */
    private $score;

    /**
     * @var int
     */
    private $escalation_score;

    /**
     * @var \Closure
     */
    private $escalation_function;

    /**
     * @var \Closure
     */
    private $success_function;

    /**
     * @var bool
     */
    private $escalated;

    /**
     * CompatibilityCheck constructor.
     */
    public function __construct() {

        $this->score     = 99;
        $this->escalated = false;

        $this->checks = [];
    }

    public function init() {

        $this->check_for_upgrade();

        $checked = get_option( $this->get_check_result_key(), false );

        if ( $checked === false ) {

            add_action( 'admin_init', [ $this, 'wp_init' ], 1 );

            return false;
        }

        if ( $this->success_function ) {
            return call_user_func( $this->success_function );
        }

        return true;
    }


    public function add( Check $check ) {

        array_push( $this->checks, $check );

        return $this;
    }


    private function get_check_result_key() {

        return 'codevision-compatibility-check-' . $this->env->getSlug();
    }


    private function get_check_storage_key() {

        return 'codevision-compatibility-storage-' . $this->env->getSlug();
    }

    private function get_check_score_key() {

        return 'codevision-compatibility-score-' . $this->env->getSlug();
    }

    private function get_check_version_key() {

        return 'codevision-compatibility-version-' . $this->env->getSlug();
    }

    private function check_for_upgrade() {

        $current_version = get_option( $this->get_check_version_key(), false );

        if ( $current_version === $this->env->getVersion() ) {
            return;
        }

        update_option( $this->get_check_version_key(), $this->env->getVersion() );

        $this->reset( false );
    }

    function wp_init() {

        if ( ! is_admin() ) {
            return;
        }

        add_action( 'wp_ajax_codevision-compatibility-check', [ $this, 'ajax_check' ] );
        add_action( 'wp_ajax_codevision-compatibility-check-done', [ $this, 'ajax_check_done' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_notices', [ $this, 'display' ] );
    }

    function display() {

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        include_once __DIR__ . '/../../templates/compatibility_check.phtml';
    }

    function enqueue_styles() {

    }

    function enqueue_scripts() {

        wp_enqueue_script( 'compatiblity-check-js',
            $this->env->getVendorUrl() . "/codevision/wp-compatibility-check/assets/js/compatibility-check.js",
            array( 'jquery' ),
            $this->env->getVersion(),
            true );
    }

    private function by_hash( $hash ) {

        /**
         * @var $check Check
         */
        foreach ( $this->checks as $check ) {

            if ( $check->getHash() === $hash ) {
                return $check;
            }

        }

        return false;
    }

    public function reset( $include_version_check = true ) {

        delete_option( $this->get_check_storage_key() );
        delete_option( $this->get_check_result_key() );

        delete_transient( $this->get_check_score_key() );

        if ( $include_version_check ) {
            delete_option( $this->get_check_version_key() );
        }
    }

    function ajax_check_done() {

        $score = array_reduce( $this->checks,
            function( $carry, $check ) {

                /**
                 * @var $check Check
                 */
                $fail = $check->check();

                if ( ! $fail ) {
                    return $carry;
                }

                $carry += $check->getEscalationScore();

                return $carry;

            },
            0 );

        $this->reset( false );

        $successful = $score < $this->getEscalationScore();

        update_option( $this->get_check_result_key(), $successful ? 'successful' : 'error' );

        wp_send_json( [ 'reload' => ! $successful ] );
    }

    function ajax_check() {

        $hash = isset( $_REQUEST[ 'hash' ] ) ? $_REQUEST[ 'hash' ] : '';
        $hash = sanitize_text_field( $hash );

        if ( ! $hash ) {

            wp_send_json( [
                'ok'      => false,
                'message' => __( 'Missing Hash in Payload!', $this->env->getSlug() ),
            ] );

            return;
        }

        $check = $this->by_hash( $hash );

        if ( $check === false ) {
            wp_send_json( [
                'ok'      => false,
                'message' => __( 'Could not find corresponding Check!', $this->env->getSlug() ),
            ] );

            return;
        }

        $key = $this->get_check_storage_key();

        $check_storage = get_option( $key );

        if ( ! $check_storage ) {
            $check_storage = [];
        }

        $result  = $check->check();
        $message = $final_message = $check->getErrorMessage();

        $check_storage[ $check->getHash() ] = $result;

        if ( $result === false ) {
            $final_message = $this->escalate( $check );
        }

        update_option( $key, $check_storage );

        wp_send_json( [
            'ok'              => $result,
            'message'         => __( $message, $this->env->getSlug() ),
            'final_message'   => __( $final_message, $this->env->getSlug() ),
            'stop_processing' => $this->escalated,
        ] );

    }

    private function escalateScore( Check $check ) {

        $current_score = get_transient( $this->get_check_score_key() );

        if ( $current_score === false ) {
            $current_score = 0;
        }

        $current_score += $check->getEscalationScore();

        set_transient( $this->get_check_score_key(), $current_score, 10 * MINUTE_IN_SECONDS );

        return $current_score;
    }

    private function escalate( Check $check ) {

        $score           = $this->escalateScore( $check );
        $this->escalated = false;

        if ( $score < $this->getEscalationScore() ) {
            return $check->getErrorMessage();
        }

        delete_transient( $this->get_check_score_key() );

        $this->escalated = true;

        if ( $this->escalation_function ) {
            return call_user_func( $this->escalation_function );
        }

        return $this->default_escalation();
    }

    /**
     *
     * Disable Plugin and show simple error message
     *
     * @return string
     */
    private function default_escalation() {

        deactivate_plugins( plugin_basename( $this->env->getBaseFile() ) );

        return sprintf( __( 'The Test did not pass! We are disabling the Plugin %s due to incompatiblity. Please fix all issues and try again!', $this->env->getSlug() ), $this->env->getPluginName() );
    }

    public function getEscalationScore() {

        return $this->escalation_score;
    }

    public function setEscalationScore( $escalation_score ) {

        $this->escalation_score = $escalation_score;

        return $this;
    }

    public function setEscalationFunction( $escalation_function ) {

        $this->escalation_function = $escalation_function;

        return $this;
    }

    public function getTitle() {

        return $this->title;
    }

    public function setTitle( $title ) {

        $this->title = $title;

        return $this;
    }

    public function getEnv() {

        return $this->env;
    }

    public function setEnv( $env ) {

        $this->env = $env;

        return $this;
    }

    public function setSuccessFunction( $success_function ) {

        $this->success_function = $success_function;

        return $this;
    }


}
