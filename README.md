# Account Lockout — Flarum Extension

Protect your [Flarum](https://flarum.org) forum against brute-force login attacks by automatically locking accounts after too many failed login attempts.

## Features

- **Configurable Attempt Threshold** — Set the maximum number of failed login attempts before an account is locked (default: 5)
- **Timed Lockout** — Accounts auto-unlock after a configurable duration (5, 10, 15, 30, or 60 minutes)
- **Manual Lockout** — Require an admin or moderator to manually unlock accounts
- **Password Reset Unlock** — Timed lockouts are automatically cleared when a user resets their password
- **Admin Bypass** — Admin accounts are never locked out
- **Unlock Controls** — Moderators and admins can unlock accounts from user profiles and the admin users page
- **Locked Badge** — Locked users display a badge visible to moderators and admins
- **Login Error Messages** — Custom error messages inform users when their account is locked and when they can try again

## Requirements

- Flarum `^1.8`
- PHP `^8.0`

## Links

- [Ralkage](https://ralkage.com)
- [GitHub](https://github.com/Ralkage/flarum-account-lockout)
- [Packagist](https://packagist.org/packages/ralkage/flarum-account-lockout)

## Installation

```bash
composer require ralkage/flarum-account-lockout
```

Then enable it in your Flarum admin panel under **Extensions**.

## Configuration

1. Go to **Admin → Account Lockout**.
2. Set the **Maximum Failed Login Attempts** (default: 5).
3. Choose a **Lockout Mode**:
   - **Timed** — Accounts auto-unlock after the configured duration.
   - **Manual** — Accounts stay locked until an admin or moderator unlocks them.
4. Set the **Lockout Duration** (only applies in timed mode).
5. Assign the **Unlock locked accounts** permission to the appropriate groups.

## License

MIT — see [LICENSE](LICENSE).
