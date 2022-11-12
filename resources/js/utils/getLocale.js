/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
function getLocale(fallback = 'en') {
  // Check if defined, e.q. in layout auth is not defined yet @todo, define locale, perhaps from session
  if (typeof config !== 'undefined') {
    return config.locale || config.fallback_locale || fallback
  } else if (typeof window !== 'undefined') {
    const { userLanguage, language } = window.navigator
    return (userLanguage || language).substr(0, 2)
  }

  return fallback
}

export default getLocale
