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

import ContactIndex from '@/views/Contacts/Index'
import ContactCreate from '@/views/Contacts/Create'
import ContactView from '@/views/Contacts/View'

import CompanyCreate from '@/views/Companies/Create'
import DealCreate from '@/views/Deals/Create'

export default [
  {
    path: '/contacts',
    name: 'contact-index',
    component: ContactIndex,
    meta: {
      title: i18n.t('contact.contacts'),
    },
    children: [
      {
        path: 'create',
        name: 'create-contact',
        components: {
          create: ContactCreate,
        },
        meta: { title: i18n.t('contact.create') },
      },
    ],
  },
  {
    path: '/contacts/:id',
    name: 'view-contact',
    component: ContactView,
    props: {
      resourceName: 'contacts',
    },
    children: [
      {
        path: 'companies/create',
        component: CompanyCreate,
        name: 'createCompanyViaContact',
      },
      {
        path: 'deals/create',
        component: DealCreate,
        name: 'createDealViaContact',
      },
    ].map(route => Object.assign(route, { meta: { scrollToTop: false } })),
  },
]
