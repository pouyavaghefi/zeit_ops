# ZeitOps Git preflight patch (not yet website publication)

## Reviewed inputs
- `Website` uses `repository_url`, `repository_branch`, `framework`, `stack`, and `php_version`.
- `ProvisionWebsiteJob` already contains a dispatch call to `DeployWebsiteJob` after provisioning success.
- `ServerBootstrapService` currently installs a root helper that allows only `check`, `update`, `install-apache`, `install-php83`, `install-tools`; Apache website configuration is not authorized by that helper.
- Website setup Blade shows deployment as a future stage and polls only the provisioning run.

## Changes
- `app/Jobs/DeployWebsiteJob.php`: safe Git access preflight using the currently pinned SSH host identity, owner verification, and `git ls-remote` for the saved GitHub branch; logs commit and errors through the existing provisioning log. No Git checkout, application code execution, Apache change, DB migration, or website activation.
- `app/Jobs/ProvisionWebsiteJob.php`: retains successful 6/6 stack provisioning, dispatches the new job if a repository URL exists, guards against a missing `runtime_version` property, and isolates dispatch failures from the already completed stack run.

## Install

```bash
cd /var/www/zeitops
# Back up existing file before copying the patch files into corresponding paths
php -l app/Jobs/ProvisionWebsiteJob.php
php -l app/Jobs/DeployWebsiteJob.php
php artisan optimize:clear
php artisan queue:restart
```

Start via existing Supervisor-managed queue workers, or for testing ONLY (run as your Laravel app account):

```bash
sudo -u www-data php artisan queue:work database --queue=provisioning,deployments --tries=3 --timeout=1800
```

**Important**: the user-facing website is NOT published by this patch. If your latest provisioning run already completed, the automatic dispatch in that run will not rerun; launch only the read-only Git verification with:

```bash
sudo -u www-data php artisan tinker --execute='App\Jobs\DeployWebsiteJob::dispatch(1);'
```

If PsySH has no writable config directory, use a tiny one-time Artisan command instead, or configure HOME/XDG_CONFIG_HOME to a properly permissioned directory. Avoid running queues as root.

## Publishing requirements

Before customer code can be executed or served safely, ZeitOps needs:
1. Per-website isolated OS identity (not `zeitops`, which holds management credentials), locked-down releases/storage paths, and resource limits.
2. A reviewed root-owned restricted site-management helper and matching exact sudoers rules; the currently installed helper does not configure virtual hosts.
3. A release state machine and logs persisted separately from `WebsiteProvisioningRun`; UI stage after server dependencies.
4. Secure Git credentials for private repos, secrets and DB provisioning, Composer/Node dependency isolation, HTTPS and domain verification.
5. Health checks, atomic activation, and rollback.

Do **not** give `zeitops` `NOPASSWD: ALL` or run arbitrary customer package scripts under the SSH management account. This package is a safe precursor rather than pretending to enable full application deployment.
