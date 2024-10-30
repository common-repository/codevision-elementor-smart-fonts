<?php
/**
 * Created by PhpStorm.
 * User: b2wsra
 * Date: 2019-02-25
 * Time: 16:33
 */

namespace Codevision;


class Environment {

    private $base_path;

    private $base_file;

    private $base_url;

    private $slug;

    private $version;

    private $vendor_url;

    private $plugin_name;

    public function getBaseFile() {

        return $this->base_file;
    }

    public function setBaseFile( $base_file ) {

        $this->base_file = $base_file;

        return $this;
    }

    public function getPluginName() {

        return $this->plugin_name;
    }

    public function setPluginName( $plugin_name ) {

        $this->plugin_name = $plugin_name;

        return $this;
    }

    public function getBasePath() {

        return $this->base_path;
    }

    public function setBasePath( $base_path ) {

        $this->base_path = $base_path;

        return $this;
    }

    public function getBaseUrl() {

        return $this->base_url;
    }

    public function setBaseUrl( $base_url ) {

        $this->base_url = $base_url;

        return $this;
    }

    public function getSlug() {

        return $this->slug;
    }

    public function setSlug( $slug ) {

        $this->slug = $slug;

        return $this;
    }

    public function getVersion() {

        return $this->version;
    }

    public function setVersion( $version ) {

        $this->version = $version;

        return $this;
    }

    public function getVendorUrl() {

        return $this->vendor_url;
    }

    public function setVendorUrl( $vendor_url ) {

        $this->vendor_url = $vendor_url;

        return $this;
    }
    
}
