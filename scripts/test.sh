#!/bin/bash

ROOT=$(cd $(dirname $0)/..;pwd)

source $(dirname $0)/_header.sh

if [[ ! -d $ROOT/tests/vendor ]]
then
    docker run \
        -t \
        -u $(id -u ${USER}):$(id -g ${USER}) \
        --rm \
        --volume $ROOT:/ts \
        $TAG \
        composer install --working-dir /ts/tests
fi

docker run \
    -u $(id -u ${USER}):$(id -g ${USER}) \
    --rm \
    --volume $ROOT:/ts \
    -e XDEBUG_MODE=coverage \
    -t \
    $TAG \
    php /ts/tests/vendor/bin/phpunit \
        -c /ts/tests/phpunit.xml

if [[ -f $ROOT/tests/.cache/index.html ]]
then
    read -t 5 -p "Open HTML coverage? (press enter in 5 seconds): " && $BROWSER $ROOT/tests/.cache/index.html
fi
