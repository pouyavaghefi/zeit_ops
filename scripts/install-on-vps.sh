#!/usr/bin/env bash
# Run as root on the intended customer-owned VPS after independently inspecting
# the sibling zeitops-deploy script. Existing bootstrap permissions are preserved.
set -Eeuo pipefail
[[ "$(id -u)" == 0 ]] || { echo 'Run as root on the VPS.' >&2; exit 1; }
DIR="$(cd "$(dirname "$0")" && pwd)"
[[ -f "$DIR/zeitops-deploy" ]] || { echo 'zeitops-deploy file missing.' >&2; exit 1; }
command -v visudo >/dev/null || { echo 'sudo visudo is required.' >&2; exit 1; }
command -v php-fpm8.3 >/dev/null || { echo 'PHP 8.3 FPM required.' >&2; exit 1; }

# Never modify the existing `zeitops-provision` root helper or sudoers file.
install -d -o root -g root -m 755 /usr/local/libexec/zeitops
install -o root -g root -m 755 "$DIR/zeitops-deploy" /usr/local/libexec/zeitops/deploy

POLICY_TMP="$(mktemp /etc/sudoers.d/.zeitops-deploy-XXXXXX)"
trap 'rm -f "$POLICY_TMP"' EXIT
printf '%s\n' 'zeitops ALL=(root) NOPASSWD: /usr/local/libexec/zeitops/deploy *' > "$POLICY_TMP"
chown root:root "$POLICY_TMP"
chmod 440 "$POLICY_TMP"
visudo -cf "$POLICY_TMP" >/dev/null
mv -fT "$POLICY_TMP" /etc/sudoers.d/zeitops-deploy
chmod 440 /etc/sudoers.d/zeitops-deploy
trap - EXIT

# Certbot is optional in deployment script, but install it here to support TLS.
apt-get update -qq
apt-get install -y --no-install-recommends certbot python3-certbot-apache openssl curl

echo 'ZeitOps deployment helper installed.'
echo 'Run: sudo -u zeitops sudo -n /usr/local/libexec/zeitops/deploy'
echo 'Expected without args: ZEITOPS_ERROR: Expected site-id domain repo branch commit.'
