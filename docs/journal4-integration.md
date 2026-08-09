# Journal 4 Checkout Integration

## Purpose

OHBONO Wallet does not call private Journal JavaScript functions directly.

Instead, the wallet checkout component emits:

```javascript
ohbono:wallet-applied
```

after the wallet amount changes.

## Journal adapter

The final Journal 4 adapter should listen for that event and invoke the Journal checkout refresh mechanism used by the exact installed Journal 4 release.

This is intentional because Journal's checkout implementation can change between releases.

Example adapter shape:

```javascript
document.addEventListener('ohbono:wallet-applied', function () {
    // Call the native Journal 4 checkout refresh function here.
});
```

The adapter must be tested against the customer's actual Journal 4 build before release.

## Important

Do not copy private Journal source code into this extension.

Do not hard-code undocumented Journal functions until the installed Journal build has been verified.
