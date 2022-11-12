<template>
  <template-form :form="form">
    <template #bottom>
      <div
        class="space-x-2 divide-y divide-neutral-200 border-t border-neutral-200 pt-3 text-right dark:divide-neutral-700 dark:border-neutral-700"
      >
        <i-button variant="white" @click="$emit('cancel-requested')">{{
          $t('app.cancel')
        }}</i-button>
        <i-button variant="primary" @click="store" type="submit">{{
          $t('app.create')
        }}</i-button>
      </div>
    </template>
  </template-form>
</template>
<script>
import TemplateForm from './Form'
import Form from '@/components/Form/Form'

export default {
  emits: ['created', 'cancel-requested'],
  components: { TemplateForm },
  data: () => ({
    form: new Form({
      name: null,
      body: null,
      subject: null,
      is_shared: true,
    }),
  }),
  methods: {
    /**
     * Create new predefined template
     *
     * @return {Void}
     */
    store() {
      this.$store
        .dispatch('predefinedMailTemplates/store', this.form)
        .then(template => {
          this.$emit('created', template)
          Innoclapps.success(this.$t('mail.templates.created'))
        })
    },
  },
}
</script>
