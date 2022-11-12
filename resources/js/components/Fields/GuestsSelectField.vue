<template>
  <form-field-group :field="field" :form="form" :field-id="fieldId">
    <div class="block">
      <div class="inline-block">
        <guests-select
          v-model="value"
          ref="guestsSelect"
          :guests="
            view === 'update' || view === 'detail'
              ? field.activity.guests
              : undefined
          "
          :contacts="contacts"
        />
      </div>
    </div>
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
import GuestsSelect from '@/views/Activity/GuestsSelect'
export default {
  mixins: [FormField],
  components: { GuestsSelect },
  computed: {
    /**
     * Available contacts for guests
     *
     * @return {Array}
     */
    contacts() {
      if (!this.viaResource) {
        return []
      }

      return this.viaResource === 'contacts'
        ? [this.resourceRecord]
        : this.resourceRecord.contacts || []
    },

    /**
     * The resource record the activity is created for
     *
     * @return {Object}
     */
    resourceRecord() {
      return this.$store.state[this.viaResource].record || {}
    },
  },
  methods: {
    /**
     * Update the field's internal value
     */
    handleChange(value) {
      this.value = value
      this.realInitialValue = value

      // Checking it the ref set selected guest is visible as when
      // the form is resetting the field via the handleChange method the
      // field may be destroyed already if within v-if statement and will not exists
      this.$nextTick(
        () =>
          this.$refs.guestsSelect && this.$refs.guestsSelect.setSelectedGuests()
      )
    },

    /*
     * Set the initial value for the field
     */
    setInitialValue() {
      if (this.view === 'create' && this.viaResource === 'contacts') {
        // as hidden by default no need to set the initial value
        /*   this.value = {
          contacts: [this.resourceRecord],
          users: [],
        }*/
      } else {
        this.value = !(
          this.field.value === undefined || this.field.value === null
        )
          ? this.field.value
          : {
              contacts: [],
              users: [],
            }
      }
    },
  },
}
</script>
