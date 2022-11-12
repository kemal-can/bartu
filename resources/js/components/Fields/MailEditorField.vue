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
import Editor from '@/components/MailEditor'
import FormField from '@/components/Form/FormField'
export default {
  components: { Editor },
  mixins: [FormField],
  // Do this in created method because sometimes is not taking the value
  created() {
    this.initialize()
  },
}
</script>
