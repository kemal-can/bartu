<template>
  <span
    class="inline-block"
    v-i-tooltip="isDisabled ? callDropdownTooltip : null"
  >
    <i-dropdown
      v-if="hasMorePhoneNumbers"
      :disabled="isDisabled"
      size="sm"
      icon="Phone"
      variant="secondary"
      :text="$t('call.make')"
    >
      <i-dropdown-item
        v-for="(phoneNumber, index) in phoneNumbers"
        :key="phoneNumber.phoneNumber + phoneNumber.type + index"
        @click="requestNewCall(phoneNumber.phoneNumber)"
        :icon="phoneNumber.type == 'mobile' ? 'DeviceMobile' : 'Phone'"
      >
        {{ phoneNumber.phoneNumber }} ({{ phoneNumber.resourceDisplayName }})
      </i-dropdown-item>
    </i-dropdown>
    <i-button
      v-else
      size="sm"
      icon="Phone"
      variant="secondary"
      :disabled="!hasPhoneNumbers || isDisabled"
      @click="requestNewCall(onlyPhoneNumbers[0])"
      >{{ $t('call.make') }}</i-button
    >
  </span>
</template>
<script>
import castArray from 'lodash/castArray'

export default {
  emits: ['call-requested'],
  props: {
    resourceName: {
      required: true,
      type: String,
    },
  },
  computed: {
    /**
     * Get the call dropdown tooltip
     *
     * @return {String}
     */
    callDropdownTooltip() {
      if (!this.hasVoIPClient) {
        return this.$t('app.integration_not_configured')
      } else if (this.$gate.userCant('use voip')) {
        return this.$t('call.no_voip_permissions')
      }

      return ''
    },

    /**
     * Check whether the MakCallButton is disabled
     *
     * @return {Boolean}
     */
    isDisabled() {
      return this.$gate.userCant('use voip') || !this.hasVoIPClient
    },

    /**
     * Resource record the make call button is added for
     *
     * @return {Object}
     */
    resourceRecord() {
      return this.$store.state[this.resourceName].record
    },

    /**
     * Total available phone number
     *
     * @return {Number}
     */
    totalPhoneNumbers() {
      return this.onlyPhoneNumbers.length
    },

    /**
     * Indicates whether there are phone numbers to be called
     *
     * @return {Boolean}
     */
    hasPhoneNumbers() {
      return this.totalPhoneNumbers > 0
    },

    /**
     * Indicates whether there is more then 1 number
     *
     * @return {Boolean}
     */
    hasMorePhoneNumbers() {
      return this.totalPhoneNumbers > 1
    },

    /**
     * Get only phone numbers without additional information
     *
     * @return {Array}
     */
    onlyPhoneNumbers() {
      return this.phoneNumbers.map(phone => phone.phoneNumber)
    },

    /**
     * The available phone numbers information
     *
     * @return {Array}
     */
    phoneNumbers() {
      let phoneNumbers = []

      phoneNumbers.push(
        ...this.getPhoneNumbersFromResource(this.resourceRecord)
      )

      switch (this.resourceName) {
        case 'contacts':
          phoneNumbers.push(
            ...this.getPhoneNumbersFromResource(
              this.resourceRecord.companies || []
            )
          )
          break
        case 'companies':
          phoneNumbers.push(
            ...this.getPhoneNumbersFromResource(
              this.resourceRecord.contacts || []
            )
          )
          break
        case 'deals':
          phoneNumbers.push(
            ...this.getPhoneNumbersFromResource(
              this.resourceRecord.companies || []
            )
          )
          phoneNumbers.push(
            ...this.getPhoneNumbersFromResource(
              this.resourceRecord.contacts || []
            )
          )
          break
      }

      return phoneNumbers
    },
  },
  methods: {
    /**
     * Request for new call
     *
     * @param  {String} phoneNumber
     *
     * @return {Void}
     */
    requestNewCall(phoneNumber) {
      this.$emit('call-requested', phoneNumber)
    },

    /**
     * Get the phone numbers from the given resource
     *
     * @param  {Object} resource
     *
     * @return {Array}
     */
    getPhoneNumbersFromResource(resource) {
      let numbers = []
      castArray(resource).forEach(resource => {
        numbers = numbers.concat(
          ...(resource.phones || []).map(phone => {
            return {
              type: phone.type,
              phoneNumber: phone.number,
              resourceDisplayName: resource.display_name,
            }
          })
        )
      })

      return numbers
    },
  },
}
</script>
