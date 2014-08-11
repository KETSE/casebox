<?php

namespace Sabre\HTTP\Auth;

use
    Sabre\HTTP\RequestInterface,
    Sabre\HTTP\ResponseInterface;

/**
 * HTTP Authentication base class.
 *
 * This class provides some common functionality for the various base classes.
 *
 * @copyright Copyright (C) 2009-2014 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
abstract class AbstractAuth {

    /**
     * Authentication realm
     *
     * @var string
     */
    protected $realm;

    /**
     * Request object
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Response object
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Creates the object
     *
     * @param string $realm
     * @return void
     */
    public function __construct($realm = 'SabreTooth', RequestInterface $request, ResponseInterface $response) {

        $this->realm = $realm;
        $this->request = $request;
        $this->response = $response;

    }

    /**
     * This method sends the needed HTTP header and statuscode (401) to force
     * the user to login.
     *
     * @return void
     */
    abstract public function requireLogin();

    /**
     * Returns the HTTP realm 
     * 
     * @return string
     */
    public function getRealm() {

        return $this->realm;

    }

}
