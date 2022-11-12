<template>
  <i-modal
    size="sm"
    @hidden="goBack"
    @shown="() => $refs.inputTitle.focus()"
    :ok-title="$t('app.create')"
    :ok-disabled="form.busy"
    :visible="true"
    static-backdrop
    :title="$t('form.create')"
    form
    @submit="create"
    @keydown="form.onKeydown($event)"
  >
    <i-form-group
      label-for="title"
      :description="$t('form.title_visibility_info')"
      :label="$t('form.title')"
      required
    >
      <i-form-input ref="inputTitle" v-model="form.title" id="title" />
      <form-error :form="form" field="title" />
    </i-form-group>
    <div class="mb-2">
      <h5
        class="mb-3 font-medium text-neutral-700 dark:text-neutral-300"
        v-t="'form.style.style'"
      />
      <i-form-group :label="$t('form.style.primary_color')">
        <i-color-swatches
          :allow-remove="false"
          v-model="form.styles.primary_color"
          :swatches="swatches"
        />
        <form-error :form="form" field="styles.primary_color" />
      </i-form-group>
      <i-form-group :label="$t('form.style.background_color')">
        <i-color-swatches
          :allow-remove="false"
          v-model="form.styles.background_color"
          :swatches="swatches"
        />
        <form-error :form="form" field="styles.background_color" />
      </i-form-group>
    </div>
  </i-modal>
</template>
<script>
import Form from '@/components/Form/Form'

export default {
  data: () => ({
    swatches: Innoclapps.config.favourite_colors,
    form: new Form({
      title: null,
      styles: {
        primary_color: '#4f46e5',
        background_color: '#F3F4F6',
      },
    }),
  }),
  methods: {
    /**
     * Create new web form
     *
     * @return {Void}
     */
    create() {
      this.$store.dispatch('webForms/store', this.form).then(form =>
        this.$router.push({
          name: 'web-form-edit',
          params: {
            id: form.id,
          },
        })
      )
    },
  },
}
</script>
