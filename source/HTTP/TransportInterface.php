<?php

namespace HTTP;

interface TransportInterface
{
    public function __construct(array $options = array());

    /**
     * @abstract
     * @param Request $request
     * @return Response
     */
    public function execute(Request $request);
}