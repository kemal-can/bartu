<template>
  <i-slideover
    @hidden="$emit('hidden')"
    @shown="() => $refs.form.$refs.name.focus()"
    :ok-disabled="form.busy"
    :ok-loading="form.busy"
    :ok-title="$t('app.create')"
    @keydown="form.onKeydown($event)"
    @submit="store"
    form
    :visible="true"
    static-backdrop
    :title="$t('user.create')"
  >
    <user-form-fields ref="form" :form="form" />
  </i-slideover>
</template>
<script>
import UserFormFields from '@/views/Users/UserFormFields'
import Form from '@/components/Form/Form'
import reduce from 'lodash/reduce'

export default {
  emits: ['created', 'hidden'],
  components: { UserFormFields },
  data: () => ({
    form: new Form({}),
  }),
  methods: {
    /**
     * Store user in storage
     *
     * @return {Void}
     */
    async store() {
      const user = await this.$store
        .dispatch('users/store', this.form)
        .catch(e => {
          if (e.response.status === 422) {
            Innoclapps.error(
              this.$t('app.form_validation_failed_with_sections'),
              3000
            )
          }
          return Promise.reject(e)
        })

      this.$emit('created', user)
      Innoclapps.success(this.$t('resource.created'))

      this.goBack()
    },

    /**
     * Prepare the component
     *
     * @return {Void}
     */
    prepareComponent() {
      this.form.set({
        name: null,
        email: null,
        roles: [],

        password: null,
        password_confirmation: null,

        timezone: moment.tz.guess(),
        locale: 'en',
        date_format: undefined, // default value is auto configured from settings
        time_format: undefined, // default value is auto configured from settings
        first_day_of_week: this.$store.state.settings.first_day_of_week,

        notifications: reduce(
          Innoclapps.config.notifications_information,
          function (obj, val) {
            let channels = {}
            val.channels.forEach(channel => (channels[channel] = true))
            obj[val.key] = channels
            return obj
          },
          {}
        ),

        super_admin: false,
        access_api: true,

        avatar: null,
      })
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
