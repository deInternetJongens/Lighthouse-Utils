#!/usr/bin/env bash
osName=`uname`

if [[ "$osName" == 'Darwin' ]]; then
    vendor/bin/phpcbf --standard=CS-DIJ.xml ./config ./src ./tests
fi

array=(
    "vendor/bin/phpcs --standard=CS-DIJ.xml ./config ./src ./tests"
    "find -L ./config ./src ./tests -name '*.php' -print0 | xargs -0 -n 1 -P 4 php -l"
)

for ix in ${!array[*]}
do
    eval "${array[$ix]}"
    if [ "$?" -ne "0" ]; then
        echo "Command failed"
        exit 1
    fi
done
