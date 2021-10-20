## Compiles sources for various architectures.
## The resulting shared objects are named as <target>.so
##
## $1 destination folder
## $2 workdir
## $3..n source files/folders
_crosscompiler() {
    dest=$1
    workdir=$2
    shift 2

    # Why php grammar cannot compile for "darwin64 darwin64h darwin32 win64 win32"?
    # for target in x86_64 armv5 armv7 aarch64 darwin64 darwin64h darwin32 win64 win32
    # for target in x86_64
    for target in x86_64 armv5 armv7 aarch64
    do
        echo -n "$target "
        docker run \
            -u $(id -u $USER):$(id -g $USER) \
            -e CROSS_TRIPLE=$target \
            --rm \
            -v $workdir:/workdir \
            -v $dest:/dest \
            multiarch/crossbuild \
            cc \
                -fPIC \
                -shared \
                -o /dest/$target.so \
                $@
    done
}

