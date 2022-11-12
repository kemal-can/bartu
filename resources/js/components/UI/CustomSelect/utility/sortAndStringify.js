/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
/**
 * @param sortable {Object}
 *
 * @return {String}
 */
function sortAndStringify(sortable) {
  const ordered = {}

  Object.keys(sortable)
    .sort()
    .forEach(key => {
      ordered[key] = sortable[key]
    })

  return JSON.stringify(ordered)
}

export default sortAndStringify
