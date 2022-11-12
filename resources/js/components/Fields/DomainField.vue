<template>
  <form-field-group :field="field" :field-id="fieldId" :form="form">
    <form-field-input-group :field="field">
      <i-form-input
        :id="fieldId"
        v-model="value"
        @blur="ensureValidDomain"
        :disabled="isReadonly"
        v-bind="field.attributes"
        :class="{
          'pl-11': field.inputGroupPrepend,
          'pr-11': field.inputGroupAppend,
        }"
      />
    </form-field-input-group>
  </form-field-group>
</template>
<script>
const psl = require('psl')
import FormField from '@/components/Form/FormField'
import FormFieldInputGroup from '@/components/Form/FormFieldInputGroup'
export default {
  mixins: [FormField],
  components: { FormFieldInputGroup },
  methods: {
    ensureValidDomain() {
      // psl.get will return null if the domain is not valid
      this.value = psl.get(this.extractHostname(this.value))
    },
    extractHostname(url) {
      let hostname
      //find & remove protocol (http, ftp, etc.) and get hostname
      if (url.indexOf('//') > -1) {
        hostname = url.split('/')[2]
      } else {
        hostname = url.split('/')[0]
      }

      //find & remove port number
      hostname = hostname.split(':')[0]
      //find & remove "?"
      hostname = hostname.split('?')[0]

      return hostname
    },
  },
}
</script>
