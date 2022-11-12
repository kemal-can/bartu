/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import Tooltip from './Tooltip'
import Modal from './Modal'
import Toggle from './Toggle'

function registerDirectives(app) {
  app.directive('i-modal', Modal)
  app.directive('i-toggle', Toggle)
  app.directive('i-tooltip', Tooltip)
}

export { Tooltip, Modal, Toggle, registerDirectives }
