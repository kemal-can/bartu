/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
export function useTypes() {
  function formatTypesForIcons(types) {
    return types.map(type => ({
      tooltip: type.name,
      icon: type.icon,
      id: type.id,
    }))
  }

  return { formatTypesForIcons }
}
