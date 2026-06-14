#!/usr/bin/env bash
cd ~/laravelProjects/volamani || exit 1
for slug in mechanical-keyboard-creator-edition premium-cotton-brand-tee wireless-noise-cancelling-headphones 4k-webcam-pro leather-laptop-sleeve-15; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/marketplace/products/$slug")
  echo "show $slug -> $code"
done
echo "products index -> $(curl -s -o /dev/null -w '%{http_code}' 'http://localhost:8000/marketplace/products')"
