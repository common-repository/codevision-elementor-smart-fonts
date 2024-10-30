<?php
/**
 * Created by PhpStorm.
 * User: b2wsra
 * Date: 2019-02-25
 * Time: 16:18
 */

namespace Codevision\Compatibility;


class SimpleCheck implements Check {

    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $error_message;

    /**
     * @var \Closure
     */
    private $callable;

    private $uuid;

    /**
     * @var int
     */
    private $score;

    /**
     * SimpleCheck constructor.
     *
     * @param string $title
     * @param string $error_message
     * @param int $score
     * @param callable $callable
     */
    public function __construct( $title, $error_message, $callable, $score = 0 ) {

        $this->title         = $title;
        $this->error_message = $error_message;
        $this->callable      = $callable;
        $this->score         = $score;

        $this->uuid = sha1( $title . $error_message );
    }

    public function check() {

        return call_user_func( $this->callable );
    }

    public function getUuid() {

        return $this->uuid;
    }

    public function getHash() {

        return $this->getUuid();
    }

    public function getTitle() {

        return $this->title;
    }

    public function getErrorMessage() {

        return $this->error_message;
    }

    public function getEscalationScore() {

        return $this->score;
    }


}