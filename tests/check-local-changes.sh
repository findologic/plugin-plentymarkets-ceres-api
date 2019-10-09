#!/bin/bash

npm run build

if [[ `git status --porcelain` ]]; then
  echo 'Local changes found. Ensure to build your JS and CSS files by running "gulp".'
  exit 1
else
  echo 'No local changes found, JS and CSS files were compiled properly.'
  exit 0
fi
