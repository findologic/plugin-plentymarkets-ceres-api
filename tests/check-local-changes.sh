#!/bin/bash

VN=$(git describe --abbrev=7 HEAD 2>/dev/null)

git update-index -q --refresh
CHANGED=$(git diff-index --name-only HEAD --)
if [[ -n "$CHANGED" ]]; then
  echo 'No local changes found, JS and CSS files were compiled properly.'
  exit 0
else
  echo 'Local changes found. Ensure to build your JS and CSS files by running "gulp".'
  exit 1
fi



if [[ `git status --porcelain` ]]; then
  echo 'No local changes found, JS and CSS files were compiled properly.'
  exit 0
else
  echo 'Local changes found. Ensure to build your JS and CSS files by running "gulp".'
  exit 1
fi
