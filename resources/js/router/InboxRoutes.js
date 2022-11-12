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

import Inbox from '@/views/Inbox/Inbox'
import InboxMessages from '@/views/Inbox/Messages/InboxMessages'
import InboxMessage from '@/views/Inbox/Messages/InboxMessage'

export default [
  {
    path: '/inbox',
    name: 'inbox',
    component: Inbox,
    meta: {
      title: i18n.t('inbox.inbox'),
    },
    children: [
      {
        path: ':account_id/folder/:folder_id/messages',
        components: {
          messages: InboxMessages,
        },
        name: 'inbox-messages',
        meta: {
          title: i18n.t('inbox.inbox'),
        },
      },
      {
        path: ':account_id/folder/:folder_id/messages/:id',
        components: {
          message: InboxMessage,
        },
        name: 'inbox-message',
        meta: {
          scrollToTop: false,
        },
      },
    ],
  },
]
