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

import CompanyIndex from '@/views/Companies/Index'
import CompanyCreate from '@/views/Companies/Create'
import CompanyView from '@/views/Companies/View'

import ContactCreate from '@/views/Contacts/Create'
import DealCreate from '@/views/Deals/Create'

export default [
  {
    path: '/companies',
    name: 'company-index',
    component: CompanyIndex,
    meta: {
      title: i18n.t('company.companies'),
    },
    children: [
      {
        path: 'create',
        name: 'create-company',
        components: {
          create: CompanyCreate,
        },
        meta: { title: i18n.t('company.create') },
      },
    ],
  },
  {
    path: '/companies/:id',
    name: 'view-company',
    component: CompanyView,
    props: {
      resourceName: 'companies',
    },
    children: [
      {
        path: 'contacts/create',
        component: ContactCreate,
        name: 'createContactViaCompany',
      },
      {
        path: 'deals/create',
        component: DealCreate,
        name: 'createDealViaCompany',
      },
    ].map(route => Object.assign(route, { meta: { scrollToTop: false } })),
  },
]
