#!/usr/bin/env bash
set -euo pipefail

if [ -z "${DEPLOY_HOST:-}" ] || [ -z "${DEPLOY_USER:-}" ] || [ -z "${TARGET_PATH:-}" ]; then
  echo "Missing required environment variables. Ensure DEPLOY_HOST, DEPLOY_USER, and TARGET_PATH are set."
  exit 1
fi

mkdir -p ~/.ssh
if [ -n "${DEPLOY_KEY:-}" ]; then
  printf '%s' "$DEPLOY_KEY" > ~/.ssh/id_rsa
  chmod 600 ~/.ssh/id_rsa
fi

ssh-keyscan -H "$DEPLOY_HOST" >> ~/.ssh/known_hosts

rsync -az --delete --exclude='.git' --exclude='vendor' --exclude='node_modules' ./ "$DEPLOY_USER"@"$DEPLOY_HOST":"$TARGET_PATH"

if [ -n "${POST_DEPLOY_COMMANDS:-}" ]; then
  ssh -i ~/.ssh/id_rsa "$DEPLOY_USER"@"$DEPLOY_HOST" "$POST_DEPLOY_COMMANDS"
fi

echo "Deployment finished."
