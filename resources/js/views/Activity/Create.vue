<template>
  <i-slideover
    @hidden="goBack"
    :title="$t('activity.create')"
    :visible="true"
    static-backdrop
    form
    @submit="store"
  >
    <form-fields-placeholder v-if="!fieldsConfigured" />

    <focus-able-fields-generator
      ref="fields"
      :form="form"
      view="create"
      :is-floating="true"
      :fields="fields"
    />

    <template #modal-ok>
      <i-dropdown-button-group
        placement="top-end"
        :disabled="form.busy"
        :loading="form.busy"
        :text="$t('app.create')"
        type="submit"
      >
        <i-dropdown-item
          @click="storeAndAddAnother"
          :text="$t('app.create_and_add_another')"
        />
        <i-dropdown-item
          @click="storeAndGoToList"
          :text="$t('app.create_and_go_to_list')"
        />
      </i-dropdown-button-group>
    </template>
  </i-slideover>
</template>
<script>
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import Form from '@/components/Form/Form'

export default {
  emits: ['created'],
  mixins: [InteractsWithResourceFields],
  data: () => ({
    form: new Form(),
  }),
  methods: {
    /**
     * Create new activity
     *
     * @return {Void}
     */
    store() {
      this.request().then(activity => this.goBack())
    },

    /**
     * Create new activity and add another
     *
     * @return {Void}
     */
    storeAndAddAnother() {
      this.request().then(activity => this.resetFormFields(this.form))
    },

    /**
     * Create new activity and go to list
     *
     * @return {Void}
     */
    storeAndGoToList() {
      this.request().then(activity => this.$router.push('/activities'))
    },

    /**
     * Make a create activity request
     *
     * @return {Promise}
     */
    request() {
      return this.$store
        .dispatch('activities/store', this.fillFormFields(this.form))
        .then(activity => {
          Innoclapps.success(this.$t('activity.created'))
          this.$emit('created', activity)
        })
        .catch(e => {
          if (e.response.status === 422) {
            Innoclapps.error(this.$t('app.form_validation_failed'), 3000)
          }
          return Promise.reject(e)
        })
    },
  },
  created() {
    /**
     * Initialize the create fields
     */
    return this.$store
      .dispatch('fields/getForResource', {
        resourceName: Innoclapps.config.fields.groups.activities,
        view: Innoclapps.config.fields.views.create,
      })
      .then(fields => this.setFields(fields))
  },
}
</script>
