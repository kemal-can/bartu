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

import SettingsDealRoutes from './SettingsDealRoutes'
import SettingsManageUsersRoutes from './SettingsManageUsersRoutes'
import SettingsIntegrationRoutes from './SettingsIntegrationRoutes'
import SettingsCompanyRoutes from './SettingsCompanyRoutes'

import SettingsIndex from '@/views/Settings/Settings'
import SettingsGeneral from '@/views/Settings/SettingsGeneral'

import SettingsUpdate from '@/views/Settings/System/SettingsUpdate'
import SettingsTools from '@/views/Settings/System/SettingsTools'
import SettingsTranslator from '@/views/Settings/System/SettingsTranslator'
import SettingsSystemInfo from '@/views/Settings/System/SettingsSystemInfo'
import SettingsSystemLogs from '@/views/Settings/System/SettingsSystemLogs'

import SettingsSecurity from '@/views/Settings/Security/SettingsSecurity'
import SettingsRecaptcha from '@/views/Settings/Security/SettingsRecaptcha'
import SettingsMailableTemplates from '@/views/Settings/SettingsMailableTemplates'
import SettingsWorkflows from '@/views/Workflows/Index'
import SettingsFields from '@/views/Settings/Fields/SettingsFields'
import SettingsActivities from '@/views/Settings/SettingsActivities'
import SettingsCalls from '@/views/Settings/SettingsCalls'
import SettingsProducts from '@/views/Settings/SettingsProducts'
import SettingsForms from '@/views/Settings/SettingsWebForms'
import FormsCreate from '@/views/WebForms/Create'
import FormsEdit from '@/views/WebForms/Edit'

export default [
  {
    path: '/settings',
    name: 'settings',
    component: SettingsIndex,
    meta: {
      title: i18n.t('settings.settings'),
      gate: 'is-super-admin',
    },
    children: [
      {
        path: 'general',
        component: SettingsGeneral,
        name: 'settings-general',
        meta: { title: i18n.t('settings.general_settings') },
        alias: '/settings',
      },
      {
        path: 'fields/:resourceName',
        name: 'resource-fields',
        component: SettingsFields,
      },
      {
        path: 'forms',
        name: 'web-forms-index',
        component: SettingsForms,
        meta: {
          title: i18n.t('form.forms'),
        },
        children: [
          {
            path: 'create',
            name: 'web-form-create',
            component: FormsCreate,
          },
        ],
      },
      {
        path: 'forms/:id/edit',
        name: 'web-form-edit',
        component: FormsEdit,
      },
      ...SettingsCompanyRoutes,
      ...SettingsIntegrationRoutes,
      ...SettingsDealRoutes,
      ...SettingsManageUsersRoutes,
      {
        path: 'activities',
        name: 'activity-settings',
        component: SettingsActivities,
        meta: {
          title: i18n.t('activity.activities'),
        },
      },
      {
        path: 'calls',
        name: 'calls-settings',
        component: SettingsCalls,
        meta: {
          title: i18n.t('call.calls'),
        },
      },
      {
        path: 'products',
        component: SettingsProducts,
        name: 'settings-products',
        meta: { title: i18n.t('product.products') },
      },
      {
        path: '/settings/workflows',
        component: SettingsWorkflows,
        meta: { title: i18n.t('workflow.workflows') },
      },
      {
        path: '/settings/mailables',
        component: SettingsMailableTemplates,
        meta: { title: i18n.t('mail_template.mail_templates') },
      },
      {
        path: '/settings/mailables',
        component: SettingsMailableTemplates,
        meta: { title: i18n.t('mail_template.mail_templates') },
      },
      {
        path: '/settings/update',
        component: SettingsUpdate,
        name: 'update',
        meta: { title: i18n.t('update.system') },
      },
      {
        path: '/settings/tools',
        component: SettingsTools,
        meta: { title: i18n.t('settings.tools.tools') },
      },
      {
        path: '/settings/translator',
        component: SettingsTranslator,
        meta: { title: i18n.t('settings.translator.translator') },
      },
      {
        path: '/settings/info',
        component: SettingsSystemInfo,
        meta: { title: i18n.t('app.system_info') },
      },
      {
        path: '/settings/logs',
        component: SettingsSystemLogs,
        meta: { title: 'Logs' },
      },
      {
        path: '/settings/security',
        component: SettingsSecurity,
        meta: { title: i18n.t('settings.security.security') },
      },
      {
        path: '/settings/recaptcha',
        component: SettingsRecaptcha,
        meta: { title: i18n.t('settings.recaptcha.recaptcha') },
      },
    ],
  },
]
