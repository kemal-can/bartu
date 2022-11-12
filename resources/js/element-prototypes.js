/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
// Parents selector
Element.prototype.parents = function (selector) {
  var elements = []
  var elem = this
  var ishaveselector = selector !== undefined

  while ((elem = elem.parentElement) !== null) {
    if (elem.nodeType !== Node.ELEMENT_NODE) {
      continue
    }

    if (!ishaveselector || elem.matches(selector)) {
      elements.push(elem)
    }
  }

  return elements
}
