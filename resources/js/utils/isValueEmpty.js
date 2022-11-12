/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
function isValueEmpty(value) {
  // Perform checks for all data types
  // https://javascript.info/types
  if (value !== null && typeof value !== 'undefined') {
    if (typeof value === 'string' && value !== '') {
      return false
    } else if (typeof value === 'array' && value.length > 0) {
      return false
    } else if (typeof value === 'object' && Object.keys(value).length > 0) {
      return false
    } else if (typeof value === 'boolean' || typeof value === 'number') {
      return false
    }
  }

  return true
}

export default isValueEmpty
