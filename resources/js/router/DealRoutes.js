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

import DealImport from '@/views/Deals/Import'
import DealIndex from '@/views/Deals/Index'
import DealCreate from '@/views/Deals/Create'
import DealView from '@/views/Deals/View'
import DealBoard from '@/views/Deals/Board'

import CompanyCreate from '@/views/Companies/Create'
import ContactCreate from '@/views/Contacts/Create'

export default [
  {
    path: '/deals',
    name: 'deal-index',
    component: DealIndex,
    meta: {
      title: i18n.t('deal.deals'),
    },
    children: [
      {
        path: 'create',
        name: 'create-deal',
        components: {
          create: DealCreate,
        },
        meta: { title: i18n.t('deal.create') },
      },
    ],
  },
  {
    path: '/import/deals',
    name: 'import-deal',
    component: DealImport,
    meta: { title: i18n.t('deal.import') },
  },
  {
    path: '/deals/board',
    name: 'deal-board',
    component: DealBoard,
    meta: {
      title: i18n.t('deal.deals'),
    },
  },
  {
    path: '/deals/:id',
    name: 'view-deal',
    component: DealView,
    props: {
      resourceName: 'deals',
    },
    children: [
      {
        path: 'contacts/create',
        component: ContactCreate,
        name: 'createContactViaDeal',
      },
      {
        path: 'companies/create',
        component: CompanyCreate,
        name: 'createCompanyViaDeal',
      },
    ].map(route => Object.assign(route, { meta: { scrollToTop: false } })),
  },
]
