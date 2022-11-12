/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import accounting from 'accounting-js'

function formatMoney(value) {
  return accounting.formatMoney(value, {
    symbol: Innoclapps.config.currency.symbol,
    precision: Innoclapps.config.currency.precision,
    thousand: Innoclapps.config.currency.thousands_separator,
    decimal: Innoclapps.config.currency.decimal_mark,
    format: Innoclapps.config.currency.symbol_first == true ? '%s%v' : '%v%s',
  })
}

export default formatMoney
