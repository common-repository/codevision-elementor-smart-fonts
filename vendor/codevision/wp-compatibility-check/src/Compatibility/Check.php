<?php
/**
 * Created by PhpStorm.
 * User: b2wsra
 * Date: 2019-02-25
 * Time: 16:21
 */

namespace Codevision\Compatibility;


interface Check {


    /**
     * @return bool
     */
    public function check();

    /**
     * @return string
     */
    public function getHash();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getErrorMessage();

    /**
     * @return int
     */
    public function getEscalationScore();
}