#!/bin/bash

# Exit on error
set -e

# Ensure .env exists
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
    echo "Created .env from .env.example"
  else
    echo "Error: .env.example not found" >&2
    exit 1
  fi
fi

# Run PHP database setup
php admin/db_setup.php
