<template>
  <form-field-group :field="field" :field-id="fieldId" :form="form">
    <editor
      :disabled="isReadonly"
      v-model="value"
      ref="editor"
      v-bind="field.attributes"
    >
    </editor>
  </form-field-group>
</template>
<script>
import Editor from '@/components/Editor'
import FormField from '@/components/Form/FormField'
import { randomString } from '@/utils'
export default {
  components: { Editor },
  mixins: [FormField],
  computed: {
    /**
     * Determine the field id
     *
     * Note: We do use pass the field id as editor id
     * some fields may have same name e.q. on resource profile and the editor
     * won't be initialized, in this case, custom editor id will be generated automatically
     *
     * @return {String}
     */
    fieldId() {
      return randomString(8)
    },
  },
  // Do this in created method because sometimes is not taking the value
  created() {
    this.initialize()
  },
}
</script>
