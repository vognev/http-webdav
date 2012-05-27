<?php

interface HTTP_Transport_Interface
{
    public function __construct(array $options = array());

    /**
     * @abstract
     * @param HTTP_Request $request
     * @return HTTP_Response
     */
    public function execute(HTTP_Request $request);
}