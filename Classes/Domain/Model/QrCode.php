<?php

namespace NextBox\Neos\QrCode\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\PersistentResource;
use NextBox\Neos\UrlShortener\Domain\Model\UrlShortener;

/**
 * @Flow\Entity
 */
class QrCode
{
    /**
     * @ORM\OneToOne(cascade={"all"})
     * @var UrlShortener
     */
    protected UrlShortener $urlShortener;

    /**
     * @ORM\OneToOne(cascade={"all"})
     * @var PersistentResource|null
     */
    protected ?PersistentResource $resource = null;


    /**
     * Getter for urlShortener
     *
     * @return UrlShortener
     */
    public function getUrlShortener(): UrlShortener
    {
        return $this->urlShortener;
    }

    /**
     * Setter for urlShortener
     *
     * @param UrlShortener $urlShortener
     * @return QrCode
     */
    public function setUrlShortener(UrlShortener $urlShortener): QrCode
    {
        $this->urlShortener = $urlShortener;

        return $this;
    }

    /**
     * Getter for resource
     *
     * @return PersistentResource|null
     */
    public function getResource(): ?PersistentResource
    {
        return $this->resource;
    }

    /**
     * Setter for resource
     *
     * @param PersistentResource|null $resource
     * @return QrCode
     */
    public function setResource(?PersistentResource $resource): QrCode
    {
        $this->resource = $resource;

        return $this;
    }
}
