<template>
  <template-form :form="form">
    <template #bottom>
      <div
        class="space-x-2 divide-y divide-neutral-200 border-t border-neutral-200 pt-3 text-right dark:divide-neutral-700 dark:border-neutral-700"
      >
        <i-button variant="white" @click="$emit('cancel-requested')">{{
          $t('app.cancel')
        }}</i-button>
        <i-button variant="primary" @click="update" type="submit">{{
          $t('app.save')
        }}</i-button>
      </div>
    </template>
  </template-form>
</template>
<script>
import TemplateForm from './Form'
import Form from '@/components/Form/Form'
import pick from 'lodash/pick'

export default {
  emits: ['updated', 'cancel-requested'],
  components: { TemplateForm },
  props: {
    id: {
      required: true,
      type: Number,
    },
  },
  data: () => ({
    form: {},
  }),
  methods: {
    /**
     * Update template in storage
     *
     * @return {Void}
     */
    update() {
      this.$store
        .dispatch('predefinedMailTemplates/update', {
          form: this.form,
          id: this.id,
        })
        .then(template => {
          this.$emit('updated', template)
          Innoclapps.success(this.$t('mail.templates.updated'))
        })
    },
    /**
     * Prepare component
     *
     * @return {Void}
     */
    prepareComponent() {
      this.form = new Form(
        pick(this.$store.getters['predefinedMailTemplates/getById'](this.id), [
          'subject',
          'body',
          'name',
          'is_shared',
        ])
      )
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
