# QR-Code generator

This package creates QR-Codes for shortened urls in Neos. Note this package requires `nextbox/neos-qrcode`.

The qr code images are stored in a persistent resource collection.

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

### Extensibility

Follow the steps in the ReadMe of the package `nextbox/neos-qrcode` to create your own definition for short url redirections.

The QrCode-Controller uses the identifier and the type to get the shortened url.
