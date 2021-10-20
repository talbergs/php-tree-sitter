### <template-file> [ARG=VALUE..]
## Variables are replaced only within "{{" and "}}" notation.
## Example:
##         $0 path-to-tmpl REF=master pass=xxx
##         # The template may look like so:
##         #    $pass = ["user", "{{ $pass }}"];
##         # Resulting in:
##         #    $pass = ["user", "xxx"];
##~
_templater() {
    tmpl=$1
    shift

    for i in $@; do
        declare $i;
    done

    eval "echo \"$(sed -e 's/"/\\"/g' -e 's/\$/\\$/g' -e 's/{{\s*\\\(\$\w*\)\s*}}/\1/g' $tmpl)\""
}
