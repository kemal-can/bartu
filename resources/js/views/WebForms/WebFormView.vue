<template>
  <div
    :class="['m-auto', { 'w-full': isEmbedded, 'max-w-2xl': !isEmbedded }]"
    :style="{ '--primary-contrast': getContrast(primaryColor) }"
    dusk="web-form"
  >
    <i-card
      :rounded="!isEmbedded"
      :shadow="!isEmbedded"
      :class="{
        'my-5': !isEmbedded,
      }"
      class="m-4 sm:m-8 sm:p-3"
    >
      <!--     <div
          id="testDiv"
          class="flex text-white space-x-2 rounded-md items-center"
        ></div> -->
      <div v-if="showSuccessMessage">
        <h4
          class="text-lg text-neutral-800"
          v-text="submitData.success_title"
        />
        <div
          class="wysiwyg-text"
          v-show="submitData.success_message"
          v-html="submitData.success_message"
        />
      </div>
      <i-alert
        variant="warning"
        class="border border-warning-200"
        v-else-if="!hasDefinedSections"
      >
        {{ $t('form.no_sections') }}
      </i-alert>
      <form @submit.prevent="submit" novalidate="true" v-else>
        <component
          :is="section.type"
          v-for="(section, index) in computedSections"
          :key="index"
          :form="form"
          :section="section"
        />
      </form>
    </i-card>
  </div>
</template>
<script>
import hexRgb from 'hex-rgb'
import FieldSection from '@/views/WebForms/DisplaySections/FieldSection'
import FileSection from '@/views/WebForms/DisplaySections/FileSection'
import IntroductionSection from '@/views/WebForms/DisplaySections/IntroductionSection'
import MessageSection from '@/views/WebForms/DisplaySections/MessageSection'
import SubmitButtonSection from '@/views/WebForms/DisplaySections/SubmitButtonSection'
import Form from '@/components/Form/Form'
import filter from 'lodash/filter'
import map from 'lodash/map'
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import { lightenDarkenColor, getContrast } from '@/utils'

export default {
  mixins: [InteractsWithResourceFields],
  components: {
    FieldSection,
    FileSection,
    IntroductionSection,
    MessageSection,
    SubmitButtonSection,
  },
  props: {
    sections: { required: true, type: Array },
    styles: { required: true, type: Object },
    submitData: { required: true, type: Object },
    publicUrl: { required: true, type: String },
  },
  data: () => ({
    form: new Form({}),
    showSuccessMessage: false,
    localSections: [],
  }),
  computed: {
    /**
     * Get the sections mapped with their actual fields
     */
    computedSections() {
      return map(this.sections, section => {
        if (this.isFieldSection(section)) {
          section.field = this.fields.find(section.requestAttribute)
        }
        return section
      })
    },

    /** Indicates whether the web form has defined section */
    hasDefinedSections() {
      return this.sections.length > 0
    },

    /**
     * Get the form background color
     *
     * @return {String}
     */
    bgColor() {
      if (this.$route.query.hasOwnProperty('bgColor')) {
        return this.$route.query.bgColor
      }

      return this.styles.background_color
    },

    /**
     * Get the form primary color
     *
     * @return {String}
     */
    primaryColor() {
      if (this.$route.query.hasOwnProperty('primaryColor')) {
        return '#' + this.$route.query.primaryColor
      }

      return this.styles.primary_color
    },

    /**
     * Get the sections with fields
     *
     * @return {Array}
     */
    sectionsWithFields() {
      return filter(this.sections, section => this.isFieldSection(section))
    },

    /**
     * Check whether the form is embedded in an iframe
     *
     * @return {Boolean}
     */
    isEmbedded() {
      return this.$route.query.e === 'true'
    },
  },
  methods: {
    getContrast,
    lightenDarkenColor,

    /**
     * Convert the given hex color to Tailwind compatible rgb
     *
     * @param  {String} hex
     *
     * @return {String}
     */
    colorForTailwind(hex) {
      const rgb = hexRgb(hex, { format: 'array' })

      return rgb[0] + ', ' + rgb[1] + ', ' + rgb[2]
    },

    /**
     * Check whether the given section is field section
     *
     * @param  {OBject}  section
     *
     * @return {Boolean}
     */
    isFieldSection(section) {
      return section.type === 'field-section'
    },

    /**
     * Submit the form
     *
     * @return {Void}
     */
    submit() {
      this.fillFormFields(this.form)

      this.form.post(this.publicUrl).then(data => {
        if (this.submitData.action === 'redirect') {
          if (window.top) {
            window.top.location.href = this.submitData.success_redirect_url
          } else {
            window.location.href = this.submitData.success_redirect_url
          }
        } else {
          this.showSuccessMessage = true
        }
      })
    },
  },
  created() {
    window.addEventListener('DOMContentLoaded', e => {
      document.body.style.backgroundColor = this.bgColor
      document.getElementById('app').style.backgroundColor = this.bgColor
    })

    let fields = []
    // First, we will get the fields from the sections
    this.sectionsWithFields.forEach(section => {
      fields.push(section.field)
    })

    // Next, we will set the fields that are taken from the sections so the collection is created
    this.setFields(fields)

    // https://codepen.io/yonatankra/pen/POvYoG
    // https://css-tricks.com/snippets/javascript/lighten-darken-color/
    let originalStyles = new WeakMap() //  or a plain object storing ids

    let nativeSupport = (function () {
      let bodyStyles = window.getComputedStyle(document.body)
      let fooBar = bodyStyles.getPropertyValue('--color-primary-50') // some variable from CSS
      return !!fooBar
    })()

    // Based on https://gist.github.com/tmanderson/98bbd05899995fd35443
    function processCSSVariables(input) {
      let styles = Array.prototype.slice.call(
          document.querySelectorAll('style'),
          0
        ),
        defRE = /(\-\-[-\w]+)\:\s*(.*?)\;/g,
        overwrites = input || {}

      if (nativeSupport) {
        Object.keys(overwrites).forEach(function (property) {
          document.body.style.setProperty('--' + property, overwrites[property])
        })
        return
      }

      function refRE(name) {
        return new RegExp('var\\(\s*' + name + '\s*\\)', 'gmi')
      }

      styles.forEach(function (styleElement) {
        let content =
            originalStyles[styleElement] ||
            (originalStyles[styleElement] = styleElement.textContent),
          vars

        while ((vars = defRE.exec(content))) {
          content = content.replace(
            refRE(vars[1]),
            overwrites[vars[1].substr(2)] || vars[2]
          )
        }

        styleElement.textContent = content
      })
    }

    const { primaryColor, colorForTailwind } = this

    let c = i => {
      try {
        return colorForTailwind(lightenDarkenColor(this.primaryColor, i))
      } catch (err) {
        // When error is thrown because the color is too light or dark and in
        // this case, the hext won't be correct he colorForTailwind function will
        // throw an error for the hex, to be sure, just use the primary color
        return colorForTailwind(this.primaryColor)
      }
    }

    processCSSVariables({
      'color-primary-50': c(100),
      'color-primary-100': c(80),
      'color-primary-200': c(60),
      'color-primary-300': c(40),
      'color-primary-400': c(20),
      'color-primary-500': c(10),
      'color-primary-600': c(0),
      'color-primary-700': c(-10),
      'color-primary-800': c(-20),
      'color-primary-900': c(-30),
    })
  },
  mounted() {
    // Colors test code
    /*let htmlTest = ''
    ;[50, 100, 200, 300, 400, 500, 600, 700, 800, 900].forEach(key => {
      htmlTest += `<div class="h-10 w-10 rounded mb-4 text-center pt-2 bg-primary-${key}">${key}</div>`
    })
    document.getElementById('testDiv').innerHTML = htmlTest*/
  },
}
</script>
