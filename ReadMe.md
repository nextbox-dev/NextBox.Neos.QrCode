# QR-Code generator

This package creates QR-Codes for shortened urls in Neos. Note this package requires `nextbox/neos-qrcode`.

If the shortened url was changed then the persisted QrCode will be regenerated immediately, the old image will be deleted.

## Configuration

Follow the steps in the ReadMe of the package `nextbox/neos-qrcode` to create your own definition for short url redirections.

### Routing

Create a new `Routes.yaml` with the name of the type that should be used:

```yaml
# Configuration/Routes.yaml

-
  name: 'Create QR-Code for default'
  uriPattern: 'qr-code/{shortIdentifier}' # replace `qr-code` with your preferences
  defaults:
    '@package': 'NextBox.Neos.QrCode'
    '@controller': 'QrCode'
    '@action': 'generateQrCode'
    'shortType': 'default' # change this to the name of the type from the settings
  appendExceedingArguments: true
  httpMethods: ['GET']
```

### Disable Image Generation from the Backend

If you want to disable the image generation from the backend disable the following settings. After a Node was published the resource will be deleted.

```yaml
# Settings.yaml

NextBox:
  Neos:
    QrCode:
      backend:
        # Should QR-Codes be generated from the backend after a node publish?
        generateQrCodesFromBackend: false
```

### Extensibility

Follow the steps in the ReadMe of the package `nextbox/neos-qrcode` to create your own definition for short url redirections.

The QrCode-Controller uses the identifier and the type to get the shortened url.
