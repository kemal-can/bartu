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

function isISODate(str) {
  // First perform the checks below, less IQ
  if (!isString(str)) {
    return false
  }

  if (str.indexOf('-') === 1) {
    return false
  }

  // 2020-04-02T03:39:56.000000Z
  return /\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{3,6}Z/.test(str)
}

export default isISODate
