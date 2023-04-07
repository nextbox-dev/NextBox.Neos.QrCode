<?php

namespace NextBox\Neos\QrCode\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use NextBox\Neos\QrCode\Services\QrCodeService;

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
        $resource = $this->qrCodeService->getFile($shortIdentifier, $shortType);

        if ($resource && !$force) {
            $this->output->outputLine('<comment>There is an image already existing. Use --force to overwrite this image and create a new one.</comment>');
        } else {
            if ($force) {
                $this->qrCodeService->deleteFile($shortIdentifier, $shortType);
                $this->output->outputLine('<comment>The qr-code was force removed</comment>');
                $this->persistenceManager->persistAll();
            }

            // ToDo: Uri has `/./` after the TLD
            $this->qrCodeService->generateAndSendToUri($shortIdentifier, $shortType);
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
    public function removeCommand(string $shortIdentifier, string $shortType = 'default'): void
    {
        $state = $this->qrCodeService->deleteFile($shortIdentifier, $shortType);

        if ($state) {
            $this->output->outputLine('<success>The qr-code with the `short-identifier` ' . $shortIdentifier . ' was removed successfully.</success>');
        } else {
            $this->output->outputLine('<error>Can not find a qr-code with the `short-identifier` ' . $shortIdentifier . '</error>');
        }
    }
}
