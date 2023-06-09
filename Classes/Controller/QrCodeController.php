<?php

namespace NextBox\Neos\QrCode\Controller;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Exception\PageNotFoundException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Neos\Service\LinkingService;
use NextBox\Neos\QrCode\Services\QrCodeService;
use NextBox\Neos\UrlShortener\Services\RedirectService;

class QrCodeController extends ActionController
{
    /**
     * @Flow\Inject
     * @var QrCodeService
     */
    protected $qrCodeService;

    /**
     * @Flow\Inject
     * @var RedirectService
     */
    protected $redirectService;

    /**
     * @Flow\Inject
     * @var LinkingService
     */
    protected $linkingService;

    /**
     * @param string $shortIdentifier
     * @return void
     * @throws PageNotFoundException
     * @throws StopActionException
     */
    public function generateQrCodeAction(string $shortIdentifier, string $shortType = 'default'): void
    {
        $documentNode = $this->redirectService->getNodeByShortIdentifierAndType($shortIdentifier, $shortType);

        if (!$documentNode instanceof NodeInterface) {
            throw new PageNotFoundException();
        }

        $urlShortener = $this->qrCodeService->findUrlShortener($shortIdentifier, $shortType);
        $resource = $this->qrCodeService->getOrCreateQrCodeResource($urlShortener);

        $fileName = 'qrcode_' . $shortIdentifier . '_' . ($documentNode->getProperty('title') ?: '') . '.' . QrCodeService::FILE_EXTENSION;

        $this->response->setContentType('image/png');
        $this->response->setHttpHeader('Content-disposition', 'inline; filename="' . $fileName . '"');
        $this->response->setHttpHeader('Content-Control', 'public, must-revalidate, max-age=0');
        $this->response->setHttpHeader('Pragma', 'public');
        $this->response->setHttpHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        $this->response->setContent($resource->getStream());

        throw new StopActionException();
    }
}
