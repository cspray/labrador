<?php

/**
 * An event triggered after the Labrador\Application is finished processing the
 * request and before the Response is sent to the user.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Events;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationFinishedEvent extends LabradorEvent {

    private $response;

    /**
     * The $response may be null if this event is triggered when the Application
     * is configured to throw exceptions raised during processing.
     *
     * @param Request $request
     * @param Response $response
     */
    function __construct(Request $request, Response $response = null) {
        parent::__construct($request);
        $this->response = $response;
    }

    /**
     * @return Response
     */
    function getResponse() {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    function setResponse(Response $response) {
        $this->response = $response;
    }

} 
