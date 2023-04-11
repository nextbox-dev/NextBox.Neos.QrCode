<?php

namespace NextBox\Neos\QrCode\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use NextBox\Neos\QrCode\Domain\Model\QrCode;
use NextBox\Neos\QrCode\Services\QrCodeService;
use NextBox\Neos\UrlShortener\Domain\Model\UrlShortener;
use NextBox\Neos\UrlShortener\Domain\Repository\UrlShortenerRepository;

class QrCodeCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var QrCodeService
     */
    protected $qrCodeService;

    /**
     * @Flow\Inject()
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var UrlShortenerRepository
     */
    protected $urlShortenerRepository;

    /**
     * Generate a new qr code if it is missing
     * Force remove the existing qr code if the argument `force` is set
     *
     * @param string $shortIdentifier the value of the short identifier
     * @param string $shortType the definition in the settings
     * @param bool $force overwrite an existing image
     * @return void
     */
    public function generateCommand(string $shortIdentifier, string $shortType = 'default', bool $force = false): void
    {
        $qrCode = $this->getQrCode($shortIdentifier, $shortType);

        $resource = $this->qrCodeService->getFileForQrCode($qrCode);

        if ($resource && !$force) {
            $this->output->outputLine('<comment>There is an image already existing. Use --force to overwrite this image and create a new one.</comment>');
        } else {
            if ($force) {
                $this->qrCodeService->deleteQrCodeResource($qrCode);
                $this->output->outputLine('<comment>The qr-code was force removed</comment>');
            }

            $this->qrCodeService->getOrCreateQrCodeResource($qrCode->getUrlShortener());
            $this->output->outputLine('<success>The qr-code with the `url-short-identifier` ' . $shortIdentifier . ' was created successfully.</success>');
        }
    }

    /**
     * Delete a qr-code resource
     *
     * @param string $shortIdentifier the value of the short identifier
     * @param string $shortType
     * @return void
     */
    public function removeImageCommand(string $shortIdentifier, string $shortType = 'default'): void
    {
        $qrCode = $this->getQrCode($shortIdentifier, $shortType);

        $this->qrCodeService->deleteQrCodeResource($qrCode);
        $this->output->outputLine('<success>The qr-code image for the `short-identifier` ' . $shortIdentifier . ' was removed successfully.</success>');
    }

    /**
     * Delete a qr-code
     *
     * @param string $shortIdentifier the value of the short identifier
     * @param string $shortType
     * @return void
     */
    public function removeCommand(string $shortIdentifier, string $shortType = 'default'): void
    {
        $qrCode = $this->getQrCode($shortIdentifier, $shortType);

        $this->qrCodeService->remove($qrCode);
        $this->output->outputLine('<success>The qr-code for the `short-identifier` ' . $shortIdentifier . ' was removed successfully.</success>');
    }

    /**
     * Check for UrlShortener and return qr code
     *
     * @param string $shortIdentifier
     * @param string $shortType
     * @return QrCode
     */
    protected function getQrCode(string $shortIdentifier, string $shortType = 'default'): QrCode
    {
        $urlShortener = $this->urlShortenerRepository->findOneByShortIdentifierAndShortType($shortIdentifier, $shortType);
        if (!$urlShortener instanceof UrlShortener) {
            $this->output->outputLine('<error>Can not find a shortened url with those values.</error>');
            $this->sendAndExit(1);
        }

        $qrCode = $this->qrCodeService->findQrCode($urlShortener);
        if (!$qrCode instanceof QrCode) {
            $this->output->outputLine('<error>Can not find a Qr-Code for the shortened url.</error>');
            $this->sendAndExit(1);
        }

        return $qrCode;
    }
}
