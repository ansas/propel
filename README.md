# Propel2

Propel2 is an open-source Object-Relational Mapping (ORM) for PHP.

[![Github actions Status](https://github.com/ansas/propel/workflows/CI/badge.svg?branch=master)](https://github.com/ansas/propel/actions?query=workflow%3ACI+branch%3Amaster)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/ansas/propel/license.svg)](https://packagist.org/packages/ansas/propel)

## Requirements

Propel uses the following Symfony Components:

* [Config](https://github.com/symfony/config)
* [Console](https://github.com/symfony/console)
* [Filesystem](https://github.com/symfony/filesystem)
* [Finder](https://github.com/symfony/finder)
* [Translation](https://github.com/symfony/translation)
* [Validator](https://github.com/symfony/validator)
* [Yaml](https://github.com/symfony/yaml)

Propel primarily relies on [**Composer**](https://github.com/composer/composer) to manage dependencies, but you
also can use [ClassLoader](https://github.com/symfony/ClassLoader) (see the `autoload.php.dist` file for instance).


## Installation

Read the [Propel documentation](http://propelorm.org/documentation/01-installation.html).


## Contribute

Everybody is welcome to contribute to Propel! Just [fork the repository](https://docs.github.com/en/get-started/quickstart/fork-a-repo) and [create a pull request](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request).

Please include unit tests to verify your changes. Have a look at the [test suite guide](http://propelorm.org/documentation/cookbook/working-with-test-suite.html) for more details about test development in Propel, like how to run tests locally. It also has information on how to apply [Propel coding standards](https://github.com/propelorm/Propel2/wiki/Coding-Standards).

More detailed information can be found in our [contribution guideline](http://propelorm.org/contribute.html).

Thank you!

## License

MIT. See the `LICENSE` file for details.
