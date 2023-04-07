<?php
namespace NextBox\Neos\QrCode\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Neos\Controller\CreateContentContextTrait;
use Endroid\QrCode\Writer\PngWriter;
use NextBox\Neos\UrlShortener\Domain\Model\UrlShortener;
use NextBox\Neos\UrlShortener\Domain\Repository\UrlShortenerRepository;
use NextBox\Neos\UrlShortener\Services\RedirectService;

/**
 * @Flow\Scope("singleton")
 */
class QrCodeService
{
    use CreateContentContextTrait;

    const FILE_EXTENSION = 'png';

    const PERSISTENT_COLLECTION = 'qrCodeResourceCollection';

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
     * @var UrlShortenerRepository
     */
    protected $urlShortenerRepository;

    /**
     * @Flow\Inject()
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * Get or generate a QR-Code for an uri
     *
     * @param string $uri
     * @param string $shortIdentifier
     * @return PersistentResource|null
     */
    public function getQrCodeResource(string $uri, string $shortIdentifier, string $shortType = 'default'): ?PersistentResource
    {
        $resource = $this->getFile($shortIdentifier, $shortType);

        if (!$resource) {
            $resource = $this->createFile($uri, $shortIdentifier, $shortType);
        }

        return ($resource instanceof PersistentResource) ? $resource : null;
    }

    /**
     * Create a QrCode for a given uri and save the image to the filesystem
     *
     * @param string $uri
     * @param string $shortIdentifier
     * @param string $shortType
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
    public function createFile(
        string $uri,
        string $shortIdentifier,
        string $shortType = 'default',
        int    $size = 300,
        int    $margin = 10,
        int    $foregroundColorR = 0,
        int    $foregroundColorG = 0,
        int    $foregroundColorB = 0,
        int    $backgroundColorR = 255,
        int    $backgroundColorG = 255,
        int    $backgroundColorB = 255
    ): PersistentResource
    {
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
            $shortIdentifier . '_' . $shortType . '.' . self::FILE_EXTENSION, self::PERSISTENT_COLLECTION
        );

        $urlShort = $this->urlShortenerRepository->findOneByShortIdentifierAndShortType($shortIdentifier, $shortType);

        if ($urlShort instanceof UrlShortener) {
            $urlShort->setResource($resource);
            $this->urlShortenerRepository->update($urlShort);
            $this->persistenceManager->persistAll();
        }

        return $resource;
    }

    /**
     * Get the resource of a short identifier and short type
     *
     * @param string $shortIdentifier
     * @param string $shortType
     * @return PersistentResource|null
     */
    public function getFile(string $shortIdentifier, string $shortType = 'default'): ?PersistentResource
    {
        $urlShort = $this->urlShortenerRepository->findOneByShortIdentifierAndShortType($shortIdentifier, $shortType);

        if ($urlShort instanceof UrlShortener) {
            $resource = $urlShort->getResource();

            if ($resource instanceof PersistentResource) {
                return $resource;
            }
        }

        return null;
    }

    /**
     * Delete the resource of a QR-Code
     *
     * @param string $shortIdentifier
     * @param string $shortType
     * @return bool
     */
    public function deleteFile(string $shortIdentifier, string $shortType = 'default'): bool
    {
        $urlShort = $this->urlShortenerRepository->findOneByShortIdentifierAndShortType($shortIdentifier, $shortType);

        if ($urlShort instanceof UrlShortener) {
            $resource = $urlShort->getResource();
            $urlShort->setResource(null);
            $this->urlShortenerRepository->update($urlShort);

            if ($resource instanceof PersistentResource) {
                $this->resourceManager->deleteResource($resource);
            }

            $this->persistenceManager->persistAll();

            return true;
        }

        return false;
    }

    /**
     * Generate the uri for the action to get a qr code image
     * Send a request to this uri
     *
     * @param string $shortIdentifier the value of the short identifier
     * @param string $shortType
     * @param int $size
     * @param int $margin
     * @param int $foregroundColorR
     * @param int $foregroundColorG
     * @param int $foregroundColorB
     * @param int $backgroundColorR
     * @param int $backgroundColorG
     * @param int $backgroundColorB
     * @return void
     */
    public function generateAndSendToUri(
        string $shortIdentifier,
        string $shortType = 'default',
        int    $size = 300,
        int    $margin = 10,
        int    $foregroundColorR = 0,
        int    $foregroundColorG = 0,
        int    $foregroundColorB = 0,
        int    $backgroundColorR = 255,
        int    $backgroundColorG = 255,
        int    $backgroundColorB = 255
    ): void
    {
        $this->createFile(
            $this->redirectService->createShortUri($shortIdentifier, $shortType),
            $shortIdentifier,
            $shortType,
            $size,
            $margin,
            $foregroundColorR,
            $foregroundColorG,
            $foregroundColorB,
            $backgroundColorR,
            $backgroundColorG,
            $backgroundColorB
        );
    }
}
