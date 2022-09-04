# 😱 **Not stable yet**
> Sources are *useable already* (the API's used in [./examples](./examples) are **not** expected to change).

---

# About

PHP bindings for the famous [tree-sitter](https://github.com/tree-sitter/tree-sitter) library, thanks to FFI (available since php 7.4).

By using this library you can:
- create static code analysis tools
- automate refactoring
- create a language server
- .. _your idea_

# Install

Version must be specified when requiring unstable packages:

```bash
composer require talbergs/tree-sitter=0.19.0-rc3
composer require talbergs/tree-sitter-php=0.19.0-rc3
composer require talbergs/tree-sitter-html=0.19.0-rc3
```

Requirements:

- ext-ffi
- php >= 8.0

# Basic usage

> For more extensive examples see [./examples](./examples) directory.

```php
$parser = TS\Parser::new();
$parser->setLanguage(TS\Language::php());
$tree = $parser->parseString(null, '<?php $code;');
var_dump($tree->getRootNode());
```

---

# Contribute

Contributions are very welcome! Just start your project and use this library,
your project will be also plugged here and this library will be adjusted
to fit everyone's needs, just submit PR or open an issue.

# Used by

> Your project, if using this binding will be plugged here as well, just open a ticket or submit PR.

- https://github.com/talbergs/php-language-server-lsp - PHP Language Server (implements LSP)

# Mono repo

> Yes this is mono repo to ease dev.

- This means all packages will have **the same** version.
- This means all packages are testable all together.
- This means `packagist` will remain in many, many `[read-only]` repositories (as a workaround for [packagist.org](packagist.org) approach).

# To do:
- [ ] cross compile (new just few builds are available and not tested)
- [ ] create a way to load grammar source files as language (non performant, for grammar development purposes).
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
- https://elib.uni-stuttgart.de/bitstream/11682/10299/1/SKilL%20Language%20Server.pdf (chapter 4)
- https://github.com/php-ffi
- https://php.watch/versions/8.1/fibers
