#!/bin/bash

ROOT=$(cd $(dirname $0)/..;pwd)
VERSION=v0.19.0

echo -n "Building version $(tput bold)${VERSION}$(tput sgr0) "

echo -n "(git clone) "
sources=/tmp/libts
rm -rf $sources
git clone --depth 1 --branch $VERSION https://github.com/tree-sitter/tree-sitter $sources \
    > /dev/null 2>&1

rm -rf $ROOT/binding/builds
mkdir $ROOT/binding/builds

source $(dirname $0)/_crosscompiler.sh
_crosscompiler $ROOT/binding/builds /tmp/libts \
    -I ./lib/include -I ./lib/src ./lib/src/lib.c

# preprocessing..
sed \
    -e '/__cplusplus/,+2d' \
    -e '/ts_tree_print_dot_graph/d' \
    $sources/lib/include/tree_sitter/api.h \
    > $ROOT/binding/builds/header.h

echo
