<?php

namespace NextBox\Neos\QrCode\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Neos\Controller\CreateContentContextTrait;
use NextBox\Neos\QrCode\Domain\Model\QrCode;
use NextBox\Neos\QrCode\Domain\Repository\QrCodeRepository;
use NextBox\Neos\UrlShortener\Domain\Model\UrlShortener;
use NextBox\Neos\UrlShortener\Domain\Repository\UrlShortenerRepository;
use NextBox\Neos\UrlShortener\Services\RedirectService;

/**
 * @Flow\Scope("singleton")
 */
class QrCodeService
{
    use CreateContentContextTrait;

    public const FILE_EXTENSION = 'png';

    public const PERSISTENT_COLLECTION = 'qrCodeResourceCollection';

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var RedirectService
     */
    protected $redirectService;

    /**
     * @Flow\Inject
     * @var QrCodeRepository
     */
    protected $qrCodeRepository;

    /**
     * @Flow\Inject
     * @var UrlShortenerRepository
     */
    protected $urlShortenerRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;


    /**
     * Get or generate a QR-Code for an uri
     *
     * @param UrlShortener $urlShortener
     * @return PersistentResource|null
     */
    public function getOrCreateQrCodeResource(UrlShortener $urlShortener): ?PersistentResource
    {
        $qrCode = $this->getQrCodeOfUrlShortener($urlShortener);

        $resource = $this->getFileForQrCode($qrCode);

        if (!$resource) {
            $resource = $this->createFileForUrlShortener($urlShortener);
            $qrCode->setResource($resource);
            $this->qrCodeRepository->update($qrCode);

            // Skip HTTP safe request specification
            $this->persistenceManager->persistAll();
        }

        return ($resource instanceof PersistentResource) ? $resource : null;
    }

    /**
     * Create a QrCode for a given UrlShortener and persist the generated image
     *
     * @param UrlShortener $urlShortener
     * @param int $size
     * @param int $margin
     * @param int $foregroundColorR
     * @param int $foregroundColorG
     * @param int $foregroundColorB
     * @param int $backgroundColorR
     * @param int $backgroundColorG
     * @param int $backgroundColorB
     * @return PersistentResource
     */
    public function createFileForUrlShortener(
        UrlShortener $urlShortener,
        int          $size = 300,
        int          $margin = 10,
        int          $foregroundColorR = 0,
        int          $foregroundColorG = 0,
        int          $foregroundColorB = 0,
        int          $backgroundColorR = 255,
        int          $backgroundColorG = 255,
        int          $backgroundColorB = 255
    ): PersistentResource {
        $shortIdentifier = $urlShortener->getShortIdentifier();
        $shortType = $urlShortener->getShortType();

        $uri = $this->redirectService->createShortUri($shortIdentifier, $shortType);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($uri)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->size($size)
            ->margin($margin)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->foregroundColor(new Color($foregroundColorR, $foregroundColorG, $foregroundColorB))
            ->backgroundColor(new Color($backgroundColorR, $backgroundColorG, $backgroundColorB))
            ->validateResult(false)
            ->build();

        $resource = $this->resourceManager->importResourceFromContent(
            $result->getString(),
            $shortIdentifier . '_' . $shortType . '.' . self::FILE_EXTENSION,
            self::PERSISTENT_COLLECTION
        );

        return $resource;
    }

    /**
     * Get the resource of a qr code
     *
     * @param QrCode|null $qrCode
     * @return PersistentResource|null
     */
    public function getFileForQrCode(?QrCode $qrCode): ?PersistentResource
    {
        if ($qrCode instanceof QrCode) {
            $resource = $qrCode->getResource();

            if ($resource instanceof PersistentResource) {
                return $resource;
            }
        }

        return null;
    }

    /**
     * Delete the resource of a QR-Code
     *
     * @param QrCode $qrCode
     * @return void
     */
    public function deleteQrCodeResource(QrCode $qrCode): void
    {
        $resource = $qrCode->getResource();
        $qrCode->setResource(null);
        $this->qrCodeRepository->update($qrCode);

        if ($resource instanceof PersistentResource) {
            $this->resourceManager->deleteResource($resource);
        }
    }

    /**
     * Remove a QrCode completely
     *
     * @param QrCode $qrCode
     * @return void
     */
    public function remove(QrCode $qrCode): void
    {
        $this->deleteQrCodeResource($qrCode);
        $this->qrCodeRepository->remove($qrCode);
    }

    /**
     * Find a UrlShortener by the short identifier and the short type
     *
     * @param string $shortIdentifier
     * @param string $shortType
     * @return UrlShortener|null
     */
    public function findUrlShortener(string $shortIdentifier, string $shortType = 'default'): ?UrlShortener
    {
        return $this->urlShortenerRepository->findOneByShortIdentifierAndShortType($shortIdentifier, $shortType);
    }

    /**
     * Find a QrCode by url shortener
     *
     * @param UrlShortener $urlShortener
     * @return UrlShortener|null
     */
    public function findQrCode(UrlShortener $urlShortener): ?QrCode
    {
        return $this->qrCodeRepository->findOneByUrlShortener($urlShortener);
    }

    /**
     * Get an existing qr code or initialize a new one
     *
     * @param UrlShortener $urlShortener
     * @return QrCode
     */
    public function getQrCodeOfUrlShortener(UrlShortener $urlShortener): QrCode
    {
        $qrCode = $this->qrCodeRepository->findOneByUrlShortener($urlShortener);

        if (!$qrCode instanceof QrCode) {
            $qrCode = new QrCode();
            $qrCode->setUrlShortener($urlShortener);
            $this->qrCodeRepository->add($qrCode);
        }

        return $qrCode;
    }
}
