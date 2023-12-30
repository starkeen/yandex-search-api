<?php

declare(strict_types=1);

namespace YandexSearchAPI\xml;

use SimpleXMLElement;

class ResponseRoot extends SimpleXMLElement
{
    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return new Request($this->request->asXML());
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return new Response($this->response->asXML());
    }
}
