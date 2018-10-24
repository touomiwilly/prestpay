![ICEPAY](https://camo.githubusercontent.com/49043ebb42bd9b98941d6013761d4aadcd33f14f/68747470733a2f2f6963657061792e636f6d2f6e6c2f77702d636f6e74656e742f7468656d65732f6963657061792f696d616765732f6865616465722f6c6f676f2e737667)

## Payment Module for PrestaShop

Make payments in your PrestaShop webshop possible. Download the special Prestashop webshop module [here](https://github.com/ICEPAYdev/Prestashop/releases), and you will be able to offer the most frequently used national and international online payment methods.

The master branche may not be stable. See the [release list](https://github.com/ICEPAY/Prestashop/releases) for stable versions of this module.

Installation and configuration instruction is available [here](https://github.com/ICEPAY/Prestashop/wiki).

### Requirements

Type       | Value
---------- | ------------------
PrestaShop | 1.7.0.0 - 1.7.4.3

### License

Our module is available under the BSD-2-Clause. See the [LICENSE](https://github.com/ICEPAY/Prestashop/blob/master/LICENSE.md) file for more information.

### Changelog

Version      | Release date   | Changes
------------ | -------------- | -------------------------------------
2.3.1        | 24/10/2018     | New payment methods
2.3.0 alpha 1| 11/01/2018     | Compatibility with Prestashop 1.7
2.2.0 beta 4 | 26/09/2016    | PHP 7.0 compatibility issues resolved
2.2.0 beta 3 | 05/09/2016     | Dutch transnslations added
2.2.0 beta 2 | 18/08/2016     | BugFix: Autoloader conflicting with other modules using the same calss name. Compatibility with PHP 5.2 (namespaces removed)
2.2.0 beta 1 | 11/08/2016     | REST API version
2.1.2        | 23/09/2015     | On some hosts, check for new updates results in a error.
2.1.1        | 21/09/2015     | This emergency release disables SSL intermediate certificate checking to allow merchants on shared hosting providers to continue processing transactions while the hosting providers update their certificate store.
2.1.0        | 23/08/2015     | Compatiblity with PrestaShop 1.6.1.1.<br>Using Bootstrap on configuration page.

### Contributing

* Fork it
* Create your feature branch (`git checkout -b my-new-feature`)
* Commit your changes (`git commit -am 'Add some feature'`)
* Push to the branch (`git push origin my-new-feature`)
* Create new Pull Request

### Bug report

If you found a repeatable bug, and troubleshooting tips didn't help, then be sure to [search existing issues](https://github.com/icepay/Prestashop/issues) first. Include steps to consistently reproduce the problem, actual vs. expected results, screenshots, and your PrestaShop version and Payment module version number. Disable all other third party extensions to verify the issue is a bug in the Payment module.
