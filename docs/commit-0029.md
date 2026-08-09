# Commit 0029 — Wallet Account Navigation & Events

## Added

- Wallet account menu helper
- Event controller
- Journal/OpenCart-friendly navigation integration
- Non-invasive account-menu JavaScript
- Wallet menu data template
- Event registration SQL example

## Account navigation

The wallet link is:

```text
My Wallet
```

and points to:

```text
extension/ohbono/account/wallet
```

## Design

The extension does not replace:

```text
catalog/view/template/account/account.twig
```

and does not modify Journal core files.

Instead it provides:

```text
walletLink()
walletMenuItem()
```

which can be used by OpenCart events or Journal custom account navigation.

## Journal 4

For a Journal account menu, place the wallet menu-data template or the
generated link in the Journal account navigation area.

The JavaScript helper supports a container marked:

```html
data-ohbono-wallet-menu
```

and a wallet URL element marked:

```html
data-ohbono-wallet-url
```

It will append the wallet item only when it is not already present.

## Event registration

`install.sql` contains an event-registration example.

Before production installation, the installer should check for an existing
event code and update/insert it idempotently.

Do not duplicate event rows.

## Important

The exact Journal 4 account-menu structure depends on the active Journal
configuration. This commit intentionally provides integration hooks rather
than hard-coding a Journal template selector.

## Next

Commit 0030 will add the admin wallet customer management screen: customer
search, balance display, wallet status, credit/debit actions, and a complete
customer wallet detail view.
