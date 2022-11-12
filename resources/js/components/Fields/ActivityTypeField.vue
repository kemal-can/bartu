<template>
  <form-field-group :field="field" :field-id="fieldId" :form="form">
    <i-form-label :for="fieldId" class="mb-1 block sm:hidden">
      {{ $t('activity.type.type') }}
    </i-form-label>
    <i-icon-picker
      v-model="value"
      :icons="icons"
      value-field="id"
      class="flex-nowrap overflow-auto sm:flex-wrap sm:overflow-visible"
      v-bind="field.attributes"
    />
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
import { useTypes } from '@/views/Activity/Composables/useTypes'
import { mapState } from 'vuex'

export default {
  mixins: [FormField],
  setup() {
    const { formatTypesForIcons } = useTypes()

    return { formatTypesForIcons }
  },
  computed: {
    isDirty() {
      return this.value !== this.realInitialValue.id
    },
    icons() {
      return this.formatTypesForIcons(this.types)
    },
    ...mapState({
      types: state => state.activities.types,
    }),
  },
  created() {
    this.field.label = ''
  },
}
</script>
