/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
/**
 * Determine the type based on the given minutes
 *
 * @param  {Number} minutes
 *
 * @return {String}
 */
function determineReminderTypeBasedOnMinutes(minutes) {
  if (minutes < 59) {
    return 'minutes'
  } else if (minutes >= 10080) {
    return 'weeks'
  } else if (minutes >= 1440) {
    return 'days'
  }

  return 'hours'
}

/**
 * Determine the field value based on the given minutes
 *
 * @param  {Number} minutes
 *
 * @return {Number}
 */
function determineReminderValueBasedOnMinutes(minutes) {
  const type = determineReminderTypeBasedOnMinutes(minutes)

  if (type === 'minutes') {
    return minutes
  } else if (type === 'hours') {
    return minutes / 60
  } else if (type === 'days') {
    return minutes / 1440
  } else if (type === 'weeks') {
    return minutes / 10080
  }
}

export {
  determineReminderTypeBasedOnMinutes,
  determineReminderValueBasedOnMinutes,
}
