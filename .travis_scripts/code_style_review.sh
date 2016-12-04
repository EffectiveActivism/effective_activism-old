#!/bin/bash

# Add statement to see that this is running in Travis CI.
echo "running travis/code_style_review.sh"

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
# The first time this is run, it will install Drupal.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

# Install phpcs.
pyrus install pear/PHP_CodeSniffer

# Install codesniffer
composer global require drupal/coder
export PATH="$PATH:$HOME/.composer/vendor/bin"
phpcs --config-set installed_paths ~/.composer/vendor/drupal/coder/coder_sniffer

# Validate code style for all projects.
cd "$DRUPAL_TI_DRUPAL_DIR/modules/effective_activism"
phpcs --standard=Drupal --extensions=php,module,inc,install,test,profile,theme,css,info,txt,md .
phpcs --standard=DrupalPractice --extensions=php,module,inc,install,test,profile,theme,css,info,txt,md .
