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

import EmailAccountsIndex from '@/views/EmailAccounts/EmailAccountsIndex'
import EmailAccountCreate from '@/views/EmailAccounts/EmailAccountCreate'
import EmailAccountEdit from '@/views/EmailAccounts/EmailAccountEdit'

export default [
  {
    path: '/mail/accounts',
    name: 'email-accounts-index',
    component: EmailAccountsIndex,
    meta: {
      title: i18n.t('mail.account.accounts'),
    },
    children: [
      {
        path: 'create',
        name: 'create-email-account',
        component: EmailAccountCreate,
        meta: { title: i18n.t('mail.account.create') },
      },
      {
        path: ':id/edit',
        name: 'edit-email-account',
        component: EmailAccountEdit,
      },
    ],
  },
]
