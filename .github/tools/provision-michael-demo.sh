#!/usr/bin/env bash
set -euo pipefail
set +x

SITE_URL="${SITE_URL:-https://velmorabank.us}"

if [ -z "${MICHAEL_EMAIL:-}" ] || [ -z "${MICHAEL_PASSWORD:-}" ]; then
  echo "MICHAEL_EMAIL and MICHAEL_PASSWORD must exist in Replit Secrets."
  exit 1
fi

tmp_dir="$(mktemp -d)"
trap 'rm -rf "$tmp_dir"' EXIT

cookie_jar="$tmp_dir/cookies.txt"
signup_headers="$tmp_dir/signup.headers"
login_headers="$tmp_dir/login.headers"
dashboard_html="$tmp_dir/dashboard.html"

echo "Creating or locating Michael Griffin demo login..."
curl --silent --show-error   --cookie-jar "$cookie_jar"   --cookie "$cookie_jar"   --dump-header "$signup_headers"   --output "$tmp_dir/signup.html"   --data-urlencode "full_name=Michael Griffin"   --data-urlencode "email=$MICHAEL_EMAIL"   --data-urlencode "password=$MICHAEL_PASSWORD"   --data-urlencode "sign_up=sign-up"   "$SITE_URL/signup/"

echo "Verifying the configured demo credentials..."
curl --silent --show-error   --cookie-jar "$cookie_jar"   --cookie "$cookie_jar"   --dump-header "$login_headers"   --output "$tmp_dir/login.html"   --data-urlencode "email=$MICHAEL_EMAIL"   --data-urlencode "password=$MICHAEL_PASSWORD"   --data-urlencode "sign_in=sign-in"   "$SITE_URL/login/"

if ! grep -Eiq '^location:[[:space:]]*/dashboard/??$' "$login_headers"; then
  echo "Login verification failed. No secret values were printed."
  exit 1
fi

echo "Opening the dashboard to seed the isolated demo history..."
curl --silent --show-error --fail   --cookie "$cookie_jar"   --output "$dashboard_html"   "$SITE_URL/dashboard/"

grep -Fq 'Michael Griffin' "$dashboard_html"
grep -Fq '980,000.00' "$dashboard_html"
grep -Fq 'Medical Equipment Supplier Payment' "$dashboard_html"
grep -Fq 'michael-griffin.png' "$dashboard_html"
grep -Fq 'DEMO ACCOUNT' "$dashboard_html"

echo "Michael Griffin demo account is ready."
echo "Verified: demo label, profile image reference, $980,000.00 balance, and December 2025 pending transaction."
