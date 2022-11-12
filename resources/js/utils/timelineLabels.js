/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
// https://stackoverflow.com/questions/36125038/generate-array-of-times-as-strings-for-every-x-minutes-in-javascript
const timelineLabels = (
  desiredStartTime,
  interval,
  period,
  format = 'hh:mm A',
  maxHour = null
) => {
  const periodsInADay = moment.duration(1, 'day').as(period)

  const timeLabels = []
  const startTimeMoment = moment(desiredStartTime, 'hh:mm')

  if (maxHour) {
    maxHour = moment(maxHour + ':00', 'HH:mm a')
  }

  for (let i = 0; i < periodsInADay; i += interval) {
    startTimeMoment.add(i === 0 ? 0 : interval, period)

    if (
      !maxHour ||
      (maxHour && startTimeMoment.isSameOrBefore(maxHour, 'hour'))
    ) {
      timeLabels.push(startTimeMoment.format(format))
    }
  }

  return timeLabels
}

export default timelineLabels
