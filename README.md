# 😱 **Not stable yet**, although the sources are *somewhat useable already* (the API's used in [./examples](./examples) are **not** expected to change).

Contributions are very welcome! Just start your project and use this library,
your project will be also plugged here and this library will be adjusted
to fit everyones needs, just submit PR or open an issue.


### PHP bindings for the famous [tree-sitter](https://github.com/tree-sitter/tree-sitter) library, thanks to FFI (available since php 7.4).

### Using this library you can create static code analysis tools, automate refactoring, rewrite [NeoVim](https://github.com/neovim/neovim)
### into PHP, create a language server for PHP in PHP, create syntax highlighter in PHP.. Anything will be promoted here.

### PHP is your daily driver? Tired to constantly look up how the `console.log` works in `DenoJS` - no more! It is time for a storm
### of ideas and tools that can be verily easily build is most efficient way for our community.

## Other bright FFI project will be plugged as well, just open a ticket (providing an argument to the why question).

# Mono repo:

> Yes this is mono repo to ease dev.

- This means all packages will have **the same** version.
- This means all packages are testable all together.
- This means `packagist` will remain in many, many `[read-only]` repositories (as a workaround for [packagist.org](packagist.org) approach).

# issues

Open PR providing minimal example to easily reproduce problem.

versioning: 

# Basic usage:

> For more extensive examples see [./examples](./examples) directory.

```bash
require composer talbergs/tree-sitter --dev
require composer talbergs/tree-sitter-php --dev
require composer talbergs/tree-sitter-go --dev
require composer talbergs/tree-sitter-html --dev
require composer talbergs/tree-sitter-<next-language> --dev
# so on ...
```

```php
$parser = TS\Parser::new();
$parser->setLanguage(TS\Language::php());
$tree = $parser->parseString(null, '<?php $code;');
var_dump($tree->getRootNode());
```

# To do:
- [ ] cross compile (new just few builds are available and not tested)
- [ ] create a way to load grammer source files as language (non performant, for grammar development purposes).
- [x] provide some working examples
- [ ] coverage (100%)
- [ ] check if all malloc's are freed
- [ ] Investigation [#1](https://blog.logrocket.com/hosting-all-your-php-packages-together-in-a-monorepo/), [#2](https://github.com/symplify/monorepo-builder)

# Versioning

> binding is versioned along with TS & grammar versions.

Using [semantic versioning standard](https://semver.org/).
- MAJOR.MINOR.PATCH version corresponds to [tree-sitter](https://github.com/tree-sitter/tree-sitter) the build of parsing library and their grammars.
- Pre-release version affix `-` is used to denote incremental improvement to this bindings library.
-- backwards incompatible changes are MAJOR
-- memory leak fixes are MAJOR
-- additional builds are considered MINOR
-- additional methods are considered MINOR
-- coding style changes are PATCH

i.e. `0.19.0-1.3.22`

# Automation:

- run all or one example: `> ./scripts/run-examples.sh` or `> ./scripts/run-examples.sh 02`

# Credits:
- God
- https://github.com/tree-sitter/tree-sitter
