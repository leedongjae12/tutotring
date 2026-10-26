#!/usr/bin/env bash
# Rocky Linux 9: PHP 8.4 runtime and application dependencies.
set -euo pipefail
app_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
check_only=0
if [[ ${1:-} == --check ]]; then check_only=1; shift; fi
if (( $# )); then echo 'Usage: bash install.sh [--check]' >&2; exit 2; fi

if (( ! check_only )); then
  . /etc/os-release
  if [[ ${ID:-} != rocky || ${VERSION_ID:-} != 9* ]]; then
    echo 'Automatic installation requires Rocky Linux 9.x. Use --check for an existing PHP 8.4 runtime.' >&2
    exit 1
  fi
  if (( EUID == 0 )); then
    echo 'Run as the application owner with sudo access, not as root (Composer must not run as root).' >&2
    exit 1
  fi
  command -v sudo >/dev/null || { echo 'sudo is required.' >&2; exit 1; }
  sudo dnf install -y dnf-plugins-core epel-release curl
  sudo dnf config-manager --set-enabled crb
  if ! rpm -q remi-release >/dev/null 2>&1; then
    tmp_dir=$(mktemp -d)
    trap 'rm -rf -- "$tmp_dir"' EXIT
    curl --fail --silent --show-error --location --proto '=https' --proto-redir '=https' \
      https://rpms.remirepo.net/RPM-GPG-KEY-remi2021 -o "$tmp_dir/remi.key"
    sudo rpm --import "$tmp_dir/remi.key"
    # Require signature verification even for the downloaded release RPM.
    sudo dnf --setopt=localpkg_gpgcheck=1 install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm
  fi
  sudo dnf module reset -y php
  sudo dnf module enable -y php:remi-8.4
  sudo dnf --setopt=gpgcheck=1 install -y php-cli php-fpm php-curl php-mysqlnd php-mbstring php-gd php-xml php-zip php-opcache composer
fi

command -v php >/dev/null || { echo 'PHP is not installed.' >&2; exit 1; }
php -r 'if (PHP_MAJOR_VERSION !== 8 || PHP_MINOR_VERSION !== 4) {fwrite(STDERR,"PHP 8.4 is required.\n"); exit(1);} foreach (["mysqli","curl","mbstring","openssl","gd","dom","zip","xmlreader","xmlwriter"] as $e) {if (!extension_loaded($e)) {fwrite(STDERR,"Missing extension: ".$e.PHP_EOL); exit(1);}}'
php -v
cd "$app_dir"
if (( ! check_only )); then
  composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
fi
failed=0
while IFS= read -r -d '' file; do
  if ! php -d short_open_tag=Off -l "$file"; then failed=1; fi
done < <(find . -path './vendor' -prune -o -type f -name '*.php' -print0)
if (( failed )); then exit 1; fi
php tests/php84_smoke.php
echo 'Runtime and dependency checks passed. Web server and database still require configuration; see README.md.'
