/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
let idCount = 0

/**
 * Dead simple unique ID implementation.
 * Thanks lodash!
 * @return {number}
 */
function uniqueId() {
  return ++idCount
}

export default uniqueId
