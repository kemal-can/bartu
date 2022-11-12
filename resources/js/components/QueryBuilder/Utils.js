/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
export function getDefaultQuery() {
  return {
    condition: 'and',
    children: [],
  }
}

export function isNullableOperator(operator) {
  return (
    ['is_empty', 'is_not_empty', 'is_null', 'is_not_null'].indexOf(operator) >=
    0
  )
}

export function isBetweenOperator(operator) {
  return ['between', 'not_between'].indexOf(operator) >= 0
}

export function needsArray(operator) {
  return ['in', 'not_in', 'between', 'not_between'].includes(operator)
}
