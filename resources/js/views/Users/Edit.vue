<template>
  <i-slideover
    @hidden="$emit('hidden')"
    :ok-title="$t('app.save')"
    :ok-disabled="form.busy"
    :ok-loading="form.busy"
    @submit="update"
    form
    :title="$t('user.edit')"
    :visible="true"
    static-backdrop
  >
    <form-fields-placeholder v-if="!componentReady" />
    <div v-show="componentReady">
      <user-form-fields :form="form" is-edit />
    </div>
  </i-slideover>
</template>
<script>
import UserFormFields from '@/views/Users/UserFormFields'
import Form from '@/components/Form/Form'
import reduce from 'lodash/reduce'

export default {
  emits: ['updated', 'hidden'],
  components: { UserFormFields },
  data: () => ({
    form: new Form({}),
    componentReady: false,
    originalLocale: null,
  }),
  methods: {
    /**
     * Update user in storage
     *
     * @return {Void}
     */
    update() {
      this.$store
        .dispatch('users/update', {
          form: this.form,
          id: this.$route.params.id,
        })
        .then(user => {
          Innoclapps.success(this.$t('resource.updated'))

          if (
            user.locale !== this.originalLocale &&
            user.id == this.currentUser.id
          ) {
            window.location.reload(true)
          } else {
            this.$emit('updated', user)
            this.goBack()
          }
        })
        .catch(e => {
          if (e.response.status === 422) {
            Innoclapps.error(
              this.$t('app.form_validation_failed_with_sections'),
              3000
            )
          }
          return Promise.reject(e)
        })
    },

    /**
     * Prepare the component for edit
     *
     * @return {Void}
     */
    prepareComponent() {
      this.$store.dispatch('users/get', this.$route.params.id).then(user => {
        this.originalLocale = user.locale

        this.form.set({
          name: user.name,
          email: user.email,
          roles: user.roles.map(role => role.name),

          password: null,
          password_confirmation: null,

          timezone: user.timezone,
          locale: user.locale,
          date_format: user.date_format,
          time_format: user.time_format,
          first_day_of_week: user.first_day_of_week,

          notifications: reduce(
            user.notifications.settings,
            function (obj, val) {
              obj[val.key] = val.availability
              return obj
            },
            {}
          ),
          super_admin: user.super_admin,
          access_api: user.access_api,
        })

        this.componentReady = true
      })
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
