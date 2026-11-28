#!/usr/bin/env bash
set -euo pipefail
[[ $(id -u) -eq 0 ]] || { echo 'Run as root' >&2; exit 1; }
[[ -f ./zeitops-access-keys ]] || { echo 'Run from the scripts directory containing zeitops-access-keys' >&2; exit 1; }
command -v visudo >/dev/null
id zeitops >/dev/null 2>&1 || { echo 'ZeitOps management account not found' >&2; exit 1; }
if ! id zeitops-access >/dev/null 2>&1; then
    /usr/sbin/useradd -m -s /bin/bash zeitops-access
fi
HOME_DIR="$(getent passwd zeitops-access | cut -d: -f6)"
[[ "$HOME_DIR" == /home/zeitops-access ]] || { echo 'Unexpected access account home' >&2; exit 1; }
[[ ! -L "$HOME_DIR" && -d "$HOME_DIR" ]] || exit 1
/usr/bin/install -d -o root -g root -m 700 "$HOME_DIR/.ssh"
[[ ! -L "$HOME_DIR/.ssh/authorized_keys" ]] || { echo 'Refusing symlinked authorized_keys' >&2; exit 1; }
/usr/bin/touch "$HOME_DIR/.ssh/authorized_keys"
/usr/bin/chown root:root "$HOME_DIR/.ssh/authorized_keys"
/usr/bin/chmod 600 "$HOME_DIR/.ssh/authorized_keys"
/usr/bin/install -d -o root -g root -m 755 /usr/local/libexec/zeitops
/usr/bin/install -o root -g root -m 755 ./zeitops-access-keys /usr/local/libexec/zeitops/access-keys
# Script only accepts valid Ed25519 key additions/removals scoped to zeitops-access.
POLICY_TMP="$(/usr/bin/mktemp /etc/sudoers.d/.zeitops-access-XXXXXX)"
trap 'rm -f -- "$POLICY_TMP"' EXIT
printf '%s\n' 'zeitops ALL=(root) NOPASSWD: /usr/local/libexec/zeitops/access-keys *' > "$POLICY_TMP"
/usr/bin/chown root:root "$POLICY_TMP"
/usr/bin/chmod 440 "$POLICY_TMP"
/usr/sbin/visudo -cf "$POLICY_TMP" >/dev/null
/usr/bin/mv -fT "$POLICY_TMP" /etc/sudoers.d/zeitops-access-keys
/usr/bin/chmod 440 /etc/sudoers.d/zeitops-access-keys
# Disabled sudo and no administrator group membership for access account.
if id -nG zeitops-access | grep -Eq '(^| )(sudo|adm|docker|root)( |$)'; then
    echo 'Unsafe access account groups detected; review before using.' >&2
    exit 1
fi
echo 'SSH access helper installed for restricted account zeitops-access.'
