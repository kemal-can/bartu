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

import ActivityIndex from '@/views/Activity/Index'
import ActivityCreate from '@/views/Activity/Create'
import ActivityEdit from '@/views/Activity/Edit'
import ActivityCalendar from '@/views/Activity/Calendar'

export default [
  {
    path: '/activities/calendar',
    name: 'activity-calendar',
    component: ActivityCalendar,
    meta: { title: i18n.t('calendar.calendar') },
  },
  {
    path: '/activities',
    name: 'activity-index',
    component: ActivityIndex,
    meta: {
      title: i18n.t('activity.activities'),
    },
    children: [
      {
        path: 'create',
        name: 'create-activity',
        components: {
          create: ActivityCreate,
        },
        meta: { title: i18n.t('activity.create') },
      },
      {
        path: ':id',
        name: 'view-activity',
        meta: {
          scrollToTop: false,
        },
        props: {
          edit: {
            resourceName: 'activities',
          },
        },
        components: {
          edit: ActivityEdit,
        },
      },
      {
        path: ':id/edit',
        name: 'edit-activity',
        meta: {
          scrollToTop: false,
        },
        props: {
          edit: {
            resourceName: 'activities',
          },
        },
        components: {
          edit: ActivityEdit,
        },
      },
    ],
  },
]
