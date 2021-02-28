#!/bin/sh
set -e

mkdir -p /app/public/samples/images
chmod -R 777 /app/public/samples/images

exec "$@"
