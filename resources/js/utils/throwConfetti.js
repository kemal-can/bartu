/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
const confetti = require('canvas-confetti')

const throwConfetti = function () {
  var confettiCanvas = document.createElement('canvas')

  confettiCanvas.classList.add(
    'absolute',
    'bottom-0',
    'w-full',
    'h-full',
    'confetti'
  )

  document.body.appendChild(confettiCanvas)

  var myConfetti = confetti.create(confettiCanvas, {
    resize: true,
  })

  myConfetti({
    particleCount: 300,
    spread: 300,
    ticks: 150,
    origin: {
      x: 1,
    },
  }).then(() => {
    const newCanvas = confettiCanvas.cloneNode(true)
    // Removed any event listeners
    confettiCanvas.replaceWith(newCanvas)
    confettiCanvas.remove()
    // Not sure if it's needed to remove?
    newCanvas.remove()
  })
}

export default throwConfetti
