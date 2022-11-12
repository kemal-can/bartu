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

import SettingsCompany from '@/views/Settings/SettingsCompany'

export default [
  {
    path: 'companies',
    component: SettingsCompany,
    name: 'settings-companies',
    meta: { title: i18n.t('company.companies') },
  },
]
