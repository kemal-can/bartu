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

import SettingsMicrosoft from '@/views/Settings/Integrations/SettingsMicrosoft'
import SettingsGoogle from '@/views/Settings/Integrations/SettingsGoogle'
import SettingsPusher from '@/views/Settings/Integrations/SettingsPusher'
import SettingsTwilio from '@/views/Settings/Integrations/SettingsTwilio'
import SettingsZapier from '@/views/Settings/Integrations/SettingsZapier'

export default [
  {
    path: 'integrations/microsoft',
    component: SettingsMicrosoft,
    name: 'settings-integrations-microsoft',
    meta: {
      title: 'Microsoft',
    },
  },
  {
    path: 'integrations/google',
    component: SettingsGoogle,
    name: 'settings-integrations-google',
    meta: {
      title: 'Google',
    },
  },
  {
    path: 'integrations/pusher',
    component: SettingsPusher,
    name: 'settings-integrations-pusher',
    meta: {
      title: 'Pusher',
    },
  },
  {
    path: 'integrations/twilio',
    component: SettingsTwilio,
    name: 'settings-integrations-twilio',
    meta: {
      title: 'Twilio',
    },
  },
  {
    path: 'integrations/zapier',
    component: SettingsZapier,
    name: 'settings-integrations-zapier',
    meta: {
      title: 'Zapier',
    },
  },
]
