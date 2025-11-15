# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

- Add IP anonymization support: `user_ip_hash` column and `ONLINE_USERS_IP_SALT` for hashing (sha256 by default).
- Add `online-users:populate-ip-hash` command with `--dry-run` and `--batch` options to safely populate new hash column from existing IPs.
- Add migration to drop raw `user_ip` after verification (optional) and a safe migration path.
- Implement `store_raw_ip` option to preserve raw IPs while adding the hash.
- Updated middleware to support `ip|session|user_id` tracking strategies and to respect anonymization settings.
- Add tests for anonymization, population command, and migrations.
- Add per-Laravel-version workflow badges and a reusable workflow for tests.
- Add README image and polished docs for migration and privacy features.
- Add `--dry-run` to `online-users:populate-ip-hash`.
- Misc: PHPUnit tests, PHPStan analysis, and .gitignore wording update.
