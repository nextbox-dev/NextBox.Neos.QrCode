<?php

namespace NextBox\Neos\QrCode\Services;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use NextBox\Neos\QrCode\Domain\Model\QrCode;
use NextBox\Neos\QrCode\Domain\Repository\QrCodeRepository;
use NextBox\Neos\UrlShortener\Domain\Model\UrlShortener;

/**
 * @Flow\Scope("singleton")
 */
class BackendService extends \NextBox\Neos\UrlShortener\Services\BackendService
{
    /**
     * @Flow\Inject
     * @var QrCodeService
     */
    protected $qrCodeService;

    /**
     * @Flow\Inject
     * @var QrCodeRepository
     */
    protected $qrCodeRepository;

    /**
     * @Flow\InjectConfiguration(path="shortTypes", package="NextBox.Neos.UrlShortener")
     * @var array
     */
    protected array $typeSettings;

    /**
     * Loop to update the short identifier or create a new entry
     *
     * @param NodeInterface $node
     * @return void
     */
    public function updateNode(NodeInterface $node): void
    {
        foreach ($this->typeSettings as $shortType => $setting) {
            $nodeTypeName = $setting['nodeType'];
            $propertyName = $setting['property'];

            $this->handleQrCode($node, $shortType, $nodeTypeName, $propertyName);
        }
    }

    /**
     * Update the short identifier or create a new entry
     *
     * @param NodeInterface $node
     * @param string $shortType
     * @param string $nodeTypeName
     * @param string $propertyName
     * @return UrlShortener|null
     */
    protected function handleQrCode(NodeInterface $node, string $shortType, string $nodeTypeName, string $propertyName): ?QrCode
    {
        $urlShortener = $this->handleUrlShortener($node, $shortType, $nodeTypeName, $propertyName);

        if (!$urlShortener instanceof UrlShortener) {
            return null;
        }

        $qrCode = $this->qrCodeService->getQrCodeOfUrlShortener($urlShortener);
        $this->qrCodeService->deleteQrCodeResource($qrCode);

        $resource = $this->qrCodeService->createFileForUrlShortener($urlShortener);
        $qrCode->setResource($resource);

        $this->qrCodeRepository->update($qrCode);

        return $qrCode;
    }
}
