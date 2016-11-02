<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 10/18/16
 * Time: 11:04 AM
 */

namespace App;


use Twilio\Rest\Client;

class SomlengClient extends Client
{
    public function getApi()
    {
        if (!$this->_api) {
            $this->_api = new SomlengApi($this);
        }
        return $this->_api;
    }
}