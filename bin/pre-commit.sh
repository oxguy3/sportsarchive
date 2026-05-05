#!/usr/bin/env bash

function echogood() {
    echo -e "\033[0;32m[pre-commit] $@\033[0m"
}
function echobad() {
    echo -e "\033[0;31m[pre-commit] $@\033[0m"
}

symfony php vendor/bin/phpstan analyze --memory-limit=512M
status=$?
if test $status -eq 0
then
    echogood "phpstan passed"
else
    echobad "phpstan failed, aborting commit"
    exit 1
fi

symfony php vendor/bin/rector --dry-run
status=$?
if test $status -eq 0
then
    echogood "rector passed"
else
    echobad "rector failed, aborting commit"
    exit 1
fi

symfony php vendor/bin/php-cs-fixer fix --dry-run
status=$?
if test $status -eq 0
then
    echogood "php-cs-fixer passed"
else
    echobad "php-cs-fixer failed, aborting commit"
    exit 1
fi

symfony php bin/phpunit
status=$?
if test $status -eq 0
then
    echogood "phpunit passed"
else
    echobad "phpunit failed, aborting commit"
    exit 1
fi

echogood "All pre-commit checks passed!"