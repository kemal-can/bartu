/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import { createI18n } from 'vue-i18n/index'
import { getLocale } from '@/utils'

const i18n = createI18n({
  globalInjection: true,
  locale: getLocale(),
  messages: lang,
})

export default {
  instance: i18n,
  t: i18n.global.t,
}
