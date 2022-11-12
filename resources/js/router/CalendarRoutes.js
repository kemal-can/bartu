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

import CalendarSync from '@/views/Calendar/CalendarSync'

export default [
  {
    path: '/calendar/sync',
    name: 'calendar-sync',
    component: CalendarSync,
    meta: {
      title: i18n.t('calendar.calendar_sync'),
    },
  },
]
