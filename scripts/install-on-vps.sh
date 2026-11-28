#!/usr/bin/env bash
# Run as root on the selected trusted VPS after reviewing this script.
set -Eeuo pipefail
[[ "$(id -u)" == 0 ]] || { echo 'Run as root.' >&2; exit 1; }
DIR="$(cd "$(dirname "$0")" && pwd)"
[[ -f "$DIR/zeitops-domains" ]] || { echo 'Missing zeitops-domains.' >&2; exit 1; }
command -v visudo >/dev/null || { echo 'sudo required.' >&2; exit 1; }
command -v certbot >/dev/null || { echo 'certbot required.' >&2; exit 1; }
command -v dig >/dev/null || { echo 'Install dnsutils first (apt-get install dnsutils).' >&2; exit 1; }
command -v flock >/dev/null || { echo 'flock required.' >&2; exit 1; }
install -d -o root -g root -m 755 /usr/local/libexec/zeitops
install -o root -g root -m 755 "$DIR/zeitops-domains" /usr/local/libexec/zeitops/domains
TEMP="$(mktemp /etc/sudoers.d/.zeitops-domains-XXXXXXXX)"
trap 'rm -f "$TEMP"' EXIT
# Arguments are validated by the root-owned helper. For single-owner trusted VPSs only.
printf '%s\n' 'zeitops ALL=(root) NOPASSWD: /usr/local/libexec/zeitops/domains *' > "$TEMP"
chown root:root "$TEMP"; chmod 440 "$TEMP"
visudo -cf "$TEMP" >/dev/null
mv -fT "$TEMP" /etc/sudoers.d/zeitops-domains
chmod 440 /etc/sudoers.d/zeitops-domains
trap - EXIT
# Webroot certificates require an Apache reload after Certbot renews files.
install -d -o root -g root -m 755 /etc/letsencrypt/renewal-hooks/deploy
cat > /etc/letsencrypt/renewal-hooks/deploy/zeitops-apache-reload <<'HOOK'
#!/usr/bin/env bash
set -euo pipefail
apache2ctl configtest >/dev/null
systemctl reload apache2
HOOK
chown root:root /etc/letsencrypt/renewal-hooks/deploy/zeitops-apache-reload
chmod 755 /etc/letsencrypt/renewal-hooks/deploy/zeitops-apache-reload
printf '%s\n' 'ZeitOps domain helper installed. No existing site or SSL configuration was changed.'
