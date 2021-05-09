<?php

namespace jlekowski\AsyncRequestBundle\Message;

use Symfony\Component\HttpFoundation\Request;

class AsyncRequestNotification
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
