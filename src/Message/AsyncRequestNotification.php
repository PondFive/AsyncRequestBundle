<?php

namespace Pond5\AsyncRequestBundle\Message;

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

    /**
     * Message must be serializable while some attributes may contain e.g. Closures.
     * Additionally, the body must be fetched from php://input before serialization (or it is lost).
     * @see https://symfony.com/doc/current/messenger.html#creating-a-message-handler
     * @return array
     */
    public function __serialize(): array
    {
        $this->request->getContent(); // read content from resource to have it serialized
        // remove attributes
        $request = $this->request->duplicate(null, null, []);

        return [
            'request' => $request,
        ];
    }
}
