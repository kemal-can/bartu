<template>
  <form @keydown="form.onKeydown($event)" @submit.prevent="">
    <i-form-group label-for="name" :label="$t('mail.templates.name')" required>
      <i-form-input v-model="form.name" />
      <form-error :form="form" field="name" />
    </i-form-group>
    <i-form-group
      label-for="subject"
      :label="$t('mail.templates.subject')"
      required
    >
      <i-form-input v-model="form.subject" />
      <form-error :form="form" field="subject" />
    </i-form-group>
    <i-form-group label-for="body" :label="$t('mail.templates.body')" required>
      <editor
        v-model="form.body"
        :placeholders="placeholders"
        :placeholders-disabled="true"
      />
      <form-error :form="form" field="body" />
    </i-form-group>
    <i-form-group>
      <i-form-checkbox
        id="is_shared"
        name="is_shared"
        v-model:checked="form.is_shared"
        :label="$t('mail.templates.is_shared')"
      />
      <form-error :form="form" field="is_shared" />
    </i-form-group>
    <slot name="bottom"></slot>
  </form>
</template>
<script>
import Editor from '@/components/MailEditor'
import debounce from 'lodash/debounce'
import { mapState } from 'vuex'
export default {
  components: { Editor },
  props: {
    form: {
      type: Object,
      default: () => {},
    },
  },
  computed: {
    ...mapState({
      placeholders: state => state.fields.placeholders,
    }),
  },
  created() {
    this.$store.dispatch('fields/fetchPlaceholders')
  },
}
</script>
