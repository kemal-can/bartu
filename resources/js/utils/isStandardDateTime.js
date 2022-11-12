/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import isString from 'lodash/isString'

function isStandardDateTime(str) {
  // First perform the checks below, less IQ
  if (!isString(str)) {
    return false
  }

  if (
    str.indexOf('-') <= 1 ||
    str.indexOf(' ') === 0 ||
    str.indexOf(':') === 0
  ) {
    return false
  }

  return /\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(str)
}

export default isStandardDateTime
