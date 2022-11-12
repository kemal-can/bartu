/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
const DefaultNumberAndDateFieldOperators = [
  'equal',
  'not_equal',
  'less',
  'less_or_equal',
  'greater',
  'greater_or_equal',
  'between',
  'not_between',
  'is_null',
  'is_not_null',
]

export default {
  data: () => ({
    ruleTypes: {
      text: {
        operators: [
          'equal',
          'not_equal',
          'begins_with',
          'not_begins_with',
          'contains',
          'not_contains',
          'ends_with',
          'not_ends_with',
          'is_empty',
          'is_not_empty',
          'is_null',
          'is_not_null',
        ],
        inputType: 'text',
        id: 'text-field',
      },
      number: {
        operators: DefaultNumberAndDateFieldOperators,
        inputType: 'number',
        id: 'number-field',
      },
      date: {
        operators: DefaultNumberAndDateFieldOperators.concat(['is']),
        inputType: 'date',
        id: 'date-field',
      },
      numeric: {
        operators: DefaultNumberAndDateFieldOperators,
        inputType: 'numeric',
        id: 'numeric-field',
      },
      radio: {
        operators: ['equal'],
        options: [],
        inputType: 'radio',
        id: 'radio-field',
      },
      checkbox: {
        operators: ['in'],
        options: [],
        inputType: 'checkbox',
        id: 'checkbox-field',
      },
      select: {
        operators: ['equal', 'not_equal'],
        options: [],
        inputType: 'select',
        id: 'select-field',
      },
      'multi-select': {
        operators: ['in', 'not_in'],
        options: [],
        inputType: 'select',
        id: 'multi-select-field',
      },
    },
  }),
}
