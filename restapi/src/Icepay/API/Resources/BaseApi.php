<?php

/**
 * ICEPAY REST API for PHP
 *
 * @version     0.0.2 Prestashop
 * @license     BSD-2-Clause, see LICENSE.md
 * @copyright   (c) 2016, ICEPAY B.V. All rights reserved.
 */

require_once(dirname(__FILE__).'/../Client.php');

class Icepay_BaseApi
{
    /**
     * @var Icepay_Client
     */
    public $client;

    /**
     * @param Icepay_Client $client
     */
    public function __construct(Icepay_Client $client)
    {
        $this->client = $client;
    }

    /**
     * Returns the current timestamp
     *
     * @return string
     */
    public function getTimeStamp()
    {
        return gmdate("Y-m-d\TH:i:s\Z");
    }

    /**
     * Get the customers IP Address
     *
     * @return string
     */
    public function getClientIp()
    {
        $ipaddress = getenv('HTTP_CLIENT_IP') ? getenv('HTTP_CLIENT_IP') :
            getenv('HTTP_X_FORWARDED_FOR') ? getenv('HTTP_X_FORWARDED_FOR') :
                getenv('HTTP_X_FORWARDED') ? getenv('HTTP_X_FORWARDED') :
                    getenv('HTTP_FORWARDED_FOR') ? getenv('HTTP_FORWARDED_FOR') :
                        getenv('HTTP_FORWARDED') ? getenv('HTTP_FORWARDED') :
                            getenv('REMOTE_ADDR');
			    
	//Try to get client IP from SERVER variables
        if(!$ipaddress)
        {
            return $this->getClientIpFromServerVar();
        }
        return $ipaddress;
    }


    /**
     * Get the customers IP address from server variables
     *
     * @return string
     */
    public function getClientIpFromServerVar()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

}
