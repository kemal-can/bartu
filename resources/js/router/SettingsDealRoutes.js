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

import SettingsDeals from '@/views/Settings/SettingsDeals'
import PipelineCreate from '@/views/Deals/Pipelines/Create'
import PipelineEdit from '@/views/Deals/Pipelines/Edit'

export default [
  {
    path: 'deals',
    name: 'deals-settings-index',
    component: SettingsDeals,
    meta: {
      title: i18n.t('deal.deals'),
    },
    children: [
      {
        path: 'pipelines/create',
        name: 'create-pipeline',
        component: PipelineCreate,
        meta: { title: i18n.t('deal.pipeline.create') },
      },
    ],
  },
  {
    path: 'deals/pipelines/:id/edit',
    name: 'edit-pipeline',
    component: PipelineEdit,
    meta: { title: i18n.t('deal.pipeline.edit') },
  },
]
