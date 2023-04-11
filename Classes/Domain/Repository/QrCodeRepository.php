<?php

namespace NextBox\Neos\QrCode\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;
use NextBox\Neos\QrCode\Domain\Model\QrCode;
use NextBox\Neos\UrlShortener\Domain\Model\UrlShortener;

/**
 * @Flow\Scope("singleton")
 *
 * @method QrCode|null findOneByUrlShortener(UrlShortener $urlShortener)
 */
class QrCodeRepository extends Repository
{
}
