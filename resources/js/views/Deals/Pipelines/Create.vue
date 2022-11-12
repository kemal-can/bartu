<template>
  <i-modal
    size="sm"
    @hidden="goBack"
    form
    @keydown="form.onKeydown($event)"
    @submit="store"
    @shown="() => $refs.inputName.focus()"
    :visible="true"
    :ok-title="$t('app.create')"
    :ok-disabled="form.busy"
    :title="$t('deal.pipeline.create')"
  >
    <i-form-group label-for="name" :label="$t('deal.pipeline.name')" required>
      <i-form-input
        v-model="form.name"
        id="name"
        ref="inputName"
        name="name"
        type="text"
      />
      <form-error :form="form" field="name" />
    </i-form-group>
  </i-modal>
</template>
<script>
import Form from '@/components/Form/Form'

export default {
  data: () => ({
    form: new Form({
      name: null,
    }),
  }),
  methods: {
    /**
     * Store pipeline in storage
     *
     * @return {Void}
     */
    store() {
      this.$store.dispatch('pipelines/store', this.form).then(pipeline => {
        this.resetStoreState()
        this.$router.push('/settings/deals/pipelines/' + pipeline.id + '/edit')
      })
    },
  },
}
</script>
