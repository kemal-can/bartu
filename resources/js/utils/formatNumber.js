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

function formatNumber(value) {
  return accounting.formatNumber(value, {
    precision: Innoclapps.config.currency.precision,
    thousand: Innoclapps.config.currency.thousands_separator,
    decimal: Innoclapps.config.currency.decimal_mark,
  })
}

export default formatNumber
