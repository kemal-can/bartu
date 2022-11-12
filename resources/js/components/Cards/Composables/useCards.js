/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import map from 'lodash/map'
import find from 'lodash/find'

export function useCards() {
  function applyUserConfig(cards, dashboard) {
    return map(cards, (card, index) => {
      let config = find(dashboard.cards, ['key', card.uriKey])

      card.order = config
        ? config.hasOwnProperty('order')
          ? config.order
          : index + 1
        : index + 1

      card.enabled =
        !config || config.enabled || typeof config.enabled == 'undefined'
          ? true
          : false

      return card
    })
  }

  return { applyUserConfig }
}
