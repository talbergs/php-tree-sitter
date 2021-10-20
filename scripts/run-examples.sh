#!/bin/bash

ROOT=$(cd $(dirname $0)/..;pwd)

source $(dirname $0)/_header.sh

if [[ ! -d $ROOT/binding/vendor ]]
then
    docker run \
        -t \
        -u $(id -u ${USER}):$(id -g ${USER}) \
        --rm \
        --volume $ROOT:/ts \
        $TAG \
        composer install --working-dir /ts/binding
fi

for example in $ROOT/examples/$1*
do
    example=$(basename $example)

    echo
    echo "Running example $(tput bold)${example}$(tput sgr0) "

    if [[ ! -d $ROOT/examples/$example/vendor ]]
    then
        docker run \
            -t \
            -u $(id -u ${USER}):$(id -g ${USER}) \
            --rm \
            --volume $ROOT:/ts \
            $TAG \
            composer install --working-dir /ts/examples/$example
    fi

    docker run \
        -u $(id -u ${USER}):$(id -g ${USER}) \
        --rm \
        -t \
        --volume $ROOT:/ts \
        -t \
        $TAG \
        php /ts/examples/$example/example.php

done
