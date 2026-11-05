# Public URL of a forwarded port: Codespaces URL when running there,
# otherwise localhost.
url_for() {
  if [ -n "${CODESPACE_NAME:-}" ]; then
    echo "https://${CODESPACE_NAME}-$1.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
  else
    echo "http://localhost:$1"
  fi
}
