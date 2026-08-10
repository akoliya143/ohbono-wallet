# Commit 0069 — Email Queue Administration

## Added

- Admin email queue listing
- Customer information
- Transaction reference
- Queue status
- Attempt count
- Availability time
- Last error visibility

## Git

```bash
git add .
git commit -m "feat(wallet): add persistent wallet email queue"

git add .
git commit -m "feat(wallet): add email worker and retry handling"

git add .
git commit -m "feat(admin): add wallet email queue administration"

git push
```

## Important architecture

```text
Wallet Credit/Debit
       |
       v
Commit financial transaction
       |
       +----> In-site notification
       |
       +----> Email queue
                    |
                    v
                 Cron worker
                    |
             +------+------+
             |             |
            Sent          Retry
                           |
                     Failed after 5
```

Email delivery is never allowed to roll back wallet money movement.
