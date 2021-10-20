#!/bin/bash

ROOT=$(cd $(dirname $0)/..;pwd)
VERSION=v0.19.0

source $(dirname $0)/_templater.sh
grammar-tmpl() {
    _templater $(dirname $0)/tmpl/grammar.php grammar=$1
}
composer-tmpl() {
    _templater $(dirname $0)/tmpl/composer.json grammar=$1 VERSION=$2
}

echo "Version $(tput bold)${VERSION}$(tput sgr0)"
for grammar in go php javascript html
do
    echo -n "Building $(tput bold)${grammar}$(tput sgr0) "

    echo -n "(git clone) "
    rm -rf /tmp/grammar
    git clone --depth 1 --branch $VERSION https://github.com/tree-sitter/tree-sitter-$grammar /tmp/grammar \
        > /dev/null 2>&1

    rm -rf $ROOT/grammars/$grammar
    mkdir $ROOT/grammars/$grammar

    composer-tmpl $grammar $VERSION > $ROOT/grammars/$grammar/composer.json
    grammar-tmpl $grammar > $ROOT/grammars/$grammar/grammar.php

    grammar_files=(-I ./src)

    # Some grammars may use external scanner.
    if [[ -e /tmp/grammar/src/scanner.cc ]]
    then
        grammar_files+=(./src/scanner.cc)
    elif [[ -e /tmp/grammar/src/scanner.c ]]
    then
        grammar_files+=(./src/scanner.c)
    fi

    grammar_files+=(./src/parser.c)

    source $(dirname $0)/_crosscompiler.sh
    _crosscompiler $ROOT/grammars/$grammar /tmp/grammar ${grammar_files[@]}

    echo
done
