/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import i18n from '@/i18n'

export default {
  operatorLabels: {
    is: i18n.t('filters.operators.is'),
    was: i18n.t('filters.operators.was'),
    equal: i18n.t('filters.operators.equal'),
    not_equal: i18n.t('filters.operators.not_equal'),
    in: i18n.t('filters.operators.in'),
    not_in: i18n.t('filters.operators.not_in'),
    less: i18n.t('filters.operators.less'),
    less_or_equal: i18n.t('filters.operators.less_or_equal'),
    greater: i18n.t('filters.operators.greater'),
    greater_or_equal: i18n.t('filters.operators.greater_or_equal'),
    between: i18n.t('filters.operators.between'),
    not_between: i18n.t('filters.operators.not_between'),
    begins_with: i18n.t('filters.operators.begins_with'),
    not_begins_with: i18n.t('filters.operators.not_begins_with'),
    contains: i18n.t('filters.operators.contains'),
    not_contains: i18n.t('filters.operators.not_contains'),
    ends_with: i18n.t('filters.operators.ends_with'),
    not_ends_with: i18n.t('filters.operators.not_ends_with'),
    is_empty: i18n.t('filters.operators.is_empty'),
    is_not_empty: i18n.t('filters.operators.is_not_empty'),
    is_null: i18n.t('filters.operators.is_null'),
    is_not_null: i18n.t('filters.operators.is_not_null'),
  },
  matchType: i18n.t('filters.match_type'),
  matchTypeAll: i18n.t('filters.match_type_all'),
  matchTypeAny: i18n.t('filters.match_type_any'),
  addRule: i18n.t('filters.add_condition'),
  removeRule: '<span aria-hidden="true">&times;</span>',
  addGroup: i18n.t('filters.add_group'),
  removeGroup: '<span aria-hidden="true">&times;</span>',
}
