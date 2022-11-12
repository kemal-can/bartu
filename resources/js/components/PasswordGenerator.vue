<template>
  <section
    class="rounded-md border border-neutral-100 bg-neutral-50 py-5 px-4 shadow-sm dark:border-neutral-500 dark:bg-neutral-800"
  >
    <div class="text-center">
      <h5
        class="mb-4 text-lg font-medium text-neutral-800 dark:text-neutral-100"
        v-t="'app.password_generator.heading'"
      ></h5>
    </div>
    <div
      class="relative mb-16 flex h-20 w-full items-center justify-center rounded-md border border-neutral-200 bg-white p-4 text-center dark:border-neutral-500 dark:bg-neutral-700"
    >
      <div class="mr-10 select-all text-neutral-700 dark:text-neutral-100">
        {{ password }}
      </div>
      <i-button-icon icon="Refresh" @click="generatePassword" />

      <copy-button
        :text="password"
        :success-message="$t('app.password_generator.copied')"
        class="ml-3"
      />
    </div>
    <div class="mb-3">
      <div class="flex justify-between">
        <i-form-label v-t="'app.password_generator.strength'" />
        <p
          class="text-sm font-medium text-neutral-800 dark:text-neutral-100"
          v-t="'app.password_generator.' + strength.text"
        ></p>
      </div>

      <input
        :class="{
          'bg-danger-400': strength.text === 'weak',
          'bg-warning-300': strength.text === 'average',
          'bg-success-400':
            strength.text === 'strong' || strength.text === 'secure',
        }"
        type="range"
        class="input-score pointer-events-none h-2 w-full appearance-none overflow-hidden rounded-md focus:outline-none"
        min="0"
        max="100"
        v-model="strength.score"
      />
    </div>

    <div class="mb-3 mt-10">
      <div class="flex justify-between">
        <i-form-label v-t="'app.password_generator.length'" />
        <p
          class="text-neutral-800 dark:text-neutral-100"
          v-text="settings.length"
        ></p>
      </div>
      <input
        type="range"
        class="range-slider h-2 w-full appearance-none overflow-hidden rounded-md bg-primary-200 focus:outline-none"
        min="6"
        v-bind:max="settings.maxLength"
        v-model="settings.length"
      />
    </div>
    <div class="mb-3">
      <div class="flex justify-between">
        <i-form-label v-t="'app.password_generator.digits'" />
        <p
          class="text-neutral-800 dark:text-neutral-100"
          v-text="settings.digits"
        ></p>
      </div>

      <input
        type="range"
        class="range-slider h-2 w-full appearance-none overflow-hidden rounded-md bg-primary-200 focus:outline-none"
        min="0"
        v-bind:max="settings.maxDigits"
        v-model="settings.digits"
      />
    </div>
    <div class="mb-3">
      <div class="flex justify-between">
        <i-form-label v-t="'app.password_generator.symbols'" />
        <p
          class="text-neutral-800 dark:text-neutral-100"
          v-text="settings.symbols"
        ></p>
      </div>

      <input
        type="range"
        class="range-slider h-2 w-full appearance-none overflow-hidden rounded-md bg-primary-200 focus:outline-none"
        min="0"
        v-bind:max="settings.maxSymbols"
        v-model="settings.symbols"
      />
    </div>
  </section>
</template>
<script>
export default {
  data() {
    return {
      password: '',
      copied: false,
      settings: {
        maxLength: 64,
        maxDigits: 10,
        maxSymbols: 10,
        length: 12,
        digits: 4,
        symbols: 2,
        ambiguous: true,
      },
    }
  },
  computed: {
    lengthThumbPosition: function () {
      return ((this.settings.length - 6) / (this.settings.maxLength - 6)) * 100
    },
    digitsThumbPosition: function () {
      return ((this.settings.digits - 0) / (this.settings.maxDigits - 0)) * 100
    },
    symbolsThumbPosition: function () {
      return (
        ((this.settings.symbols - 0) / (this.settings.maxSymbols - 0)) * 100
      )
    },
    strength: function () {
      let count = {
        excess: 0,
        upperCase: 0,
        numbers: 0,
        symbols: 0,
      }

      let weight = {
        excess: 3,
        upperCase: 4,
        numbers: 5,
        symbols: 5,
        combo: 0,
        flatLower: 0,
        flatNumber: 0,
      }

      let strength = {
        text: '',
        score: 0,
      }

      let baseScore = 30

      for (let i = 0; i < this.password.length; i++) {
        if (this.password.charAt(i).match(/[A-Z]/g)) {
          count.upperCase++
        }
        if (this.password.charAt(i).match(/[0-9]/g)) {
          count.numbers++
        }
        if (this.password.charAt(i).match(/(.*[!,@,#,$,%,^,&,*,?,_,~])/)) {
          count.symbols++
        }
      }

      count.excess = this.password.length - 6

      if (count.upperCase && count.numbers && count.symbols) {
        weight.combo = 25
      } else if (
        (count.upperCase && count.numbers) ||
        (count.upperCase && count.symbols) ||
        (count.numbers && count.symbols)
      ) {
        weight.combo = 15
      }

      if (this.password.match(/^[\sa-z]+$/)) {
        weight.flatLower = -30
      }

      if (this.password.match(/^[\s0-9]+$/)) {
        weight.flatNumber = -50
      }

      let score =
        baseScore +
        count.excess * weight.excess +
        count.upperCase * weight.upperCase +
        count.numbers * weight.numbers +
        count.symbols * weight.symbols +
        weight.combo +
        weight.flatLower +
        weight.flatNumber

      if (score < 30) {
        strength.text = 'weak'
        strength.score = 10
        return strength
      } else if (score >= 30 && score < 75) {
        strength.text = 'average'
        strength.score = 40
        return strength
      } else if (score >= 75 && score < 150) {
        strength.text = 'strong'
        strength.score = 75
        return strength
      } else {
        strength.text = 'secure'
        strength.score = 100
        return strength
      }
    },
  },
  mounted() {
    this.generatePassword()
  },
  watch: {
    settings: {
      handler: function () {
        this.generatePassword()
      },
      deep: true,
    },
  },
  methods: {
    // generate the password
    generatePassword() {
      let lettersSetArray = [
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
      ]
      let symbolsSetArray = [
        '=',
        '+',
        '-',
        '^',
        '?',
        '!',
        '%',
        '&',
        '*',
        '$',
        '#',
        '^',
        '@',
        '|',
      ]
      //let ambiguousSetArray = ["(",")","{","}","[","]","(",")","/","~",";",":",".","<",">"];
      let passwordArray = []
      let digitsArray = []
      let digitsPositionArray = []

      // first, fill the password array with letters, uppercase and lowecase
      for (let i = 0; i < this.settings.length; i++) {
        // get an array for all indexes of the password array
        digitsPositionArray.push(i)

        let upperCase = Math.round(Math.random() * 1)
        if (upperCase === 0) {
          passwordArray[i] =
            lettersSetArray[
              Math.floor(Math.random() * lettersSetArray.length)
            ].toUpperCase()
        } else {
          passwordArray[i] =
            lettersSetArray[Math.floor(Math.random() * lettersSetArray.length)]
        }
      }

      // Add digits to password
      for (let i = 0; i < this.settings.digits; i++) {
        let digit = Math.round(Math.random() * 9)
        let numberIndex =
          digitsPositionArray[
            Math.floor(Math.random() * digitsPositionArray.length)
          ]

        passwordArray[numberIndex] = digit

        /* remove position from digitsPositionArray so we make sure to the have the exact number of digits in our password
                    since without this step, numbers may override other numbers */

        let j = digitsPositionArray.indexOf(numberIndex)
        if (i != -1) {
          digitsPositionArray.splice(j, 1)
        }
      }

      // add special charachters "symbols"
      for (let i = 0; i < this.settings.symbols; i++) {
        let symbol =
          symbolsSetArray[Math.floor(Math.random() * symbolsSetArray.length)]
        let symbolIndex =
          digitsPositionArray[
            Math.floor(Math.random() * digitsPositionArray.length)
          ]

        passwordArray[symbolIndex] = symbol

        /* remove position from digitsPositionArray so we make sure to the have the exact number of digits in our password
                    since without this step, numbers may override other numbers */

        let j = digitsPositionArray.indexOf(symbolIndex)
        if (i != -1) {
          digitsPositionArray.splice(j, 1)
        }
      }
      this.password = passwordArray.join('')
    },
  },
}
</script>
<style scoped>
input[type='range'].input-score::-webkit-slider-thumb {
  width: 15px;
  -webkit-appearance: none;
  appearance: none;
  height: 15px;
  cursor: ew-resize;
  background: rgba(var(--color-neutral-700), 50%);
  border-radius: 50%;
}

input[type='range'].range-slider::-webkit-slider-thumb {
  width: 15px;
  -webkit-appearance: none;
  appearance: none;
  height: 15px;
  cursor: ew-resize;
  background: rgb(var(--color-primary-500));
  box-shadow: -405px 0 0 400px rgb(var(--color-primary-400));
  border-radius: 50%;
}
</style>
