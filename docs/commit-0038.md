# Commit 0038 — OpenCart Events & Account Navigation Integration

## Added

- Storefront account navigation integration
- Wallet account link endpoint
- OpenCart event registration
- Journal/theme-friendly account navigation snippet
- No OpenCart core-file modifications

## Customer account link

The wallet page is exposed through:

```text
extension/ohbono/module/wallet.history
```

The customer must be logged in.

The link is shown only when:

```text
ohbono_wallet_status = 1
```

## Events

Registered events:

```text
ohbono_wallet_account_links
ohbono_wallet_account_data
```

The installer uses event upsert behavior so repeated installation does not
create duplicate events.

## Journal 3 integration

The theme should render the wallet link using the provided:

```text
upload/catalog/view/template/extension/ohbono/module/account_links.twig
```

The integration intentionally avoids modifying OpenCart core account files.

This is important for OpenCart upgrades and Journal Theme 3.x compatibility.

## Important

The account navigation snippet is a theme integration asset. Depending on the
specific Journal account-navigation configuration, it may need to be added
through the Journal layout/module system.

No core template should be overwritten.

## Next

Commit 0039 will add the customer wallet dashboard: current balance,
available wallet amount, recent transactions, refund summary and responsive
Journal-friendly styling.
