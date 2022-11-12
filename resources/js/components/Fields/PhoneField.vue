<template>
  <form-field-group
    :field="field"
    :field-id="fieldId"
    class="multiple"
    :form="form"
  >
    <i-form-group
      class="rounded-md"
      v-for="(phone, index) in value"
      :key="index"
      v-show="!phone._delete"
    >
      <div class="flex">
        <div class="relative flex grow items-stretch focus-within:z-10">
          <div
            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
          >
            <icon
              :icon="phone.type == 'mobile' ? 'DeviceMobile' : 'Phone'"
              class="h-5 w-5 text-neutral-500 dark:text-neutral-300"
            ></icon>
          </div>
          <i-form-input
            :rounded="false"
            class="rounded-l-md pl-10"
            @input="
              form.errors.clear(field.attribute + '.' + index + '.number')
            "
            :name="field.attribute + '.' + index + '.number'"
            v-model="value[index].number"
          ></i-form-input>
        </div>
        <i-dropdown auto-size="min" :full="false">
          <template #toggle>
            <i-button
              variant="white"
              class="relative -ml-px justify-between px-4 py-2 text-sm focus:z-10"
              :rounded="false"
              :size="false"
            >
              {{
                value[index].type
                  ? field.types[value[index].type]
                  : $t('fields.phones.types.type')
              }}
              <icon icon="ChevronDown" class="link h-4 w-4" />
            </i-button>
          </template>
          <i-dropdown-item
            v-for="(label, id) in field.types"
            :key="id"
            @click="value[index].type = id"
            :text="label"
          />
        </i-dropdown>
        <i-button-close
          :rounded="false"
          @click="removePhone(index)"
          variant="white"
          class="relative -ml-px rounded-r-md"
        />
      </div>
      <form-error
        :field="field.attribute + '.' + index + '.number'"
        :form="form"
      />
    </i-form-group>
    <div class="text-right">
      <a
        href="#"
        @click.prevent="newPhone"
        class="link mr-2 text-sm"
        v-t="'fields.phones.add'"
      ></a>
    </div>
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
import cloneDeep from 'lodash/cloneDeep'
import isEqual from 'lodash/isEqual'
import reject from 'lodash/reject'
export default {
  mixins: [FormField],
  watch: {
    /**
     * Don't allow the phone field to have zero inputs available
     * When the user delete the last one, add new
     */
    totalPhones: function (newVal, oldVal) {
      if (newVal === 0) {
        this.newPhone()
      }
    },
  },
  computed: {
    /**
     * Check whether the field is dirty
     *
     * @return {Boolean}
     */
    isDirty() {
      if (!this.value) {
        return false
      }

      if (this.totalForDelete > 0) {
        return true
      }

      if (this.totalForInsert > 0) {
        return true
      }

      return !isEqual(this.value, this.realInitialValue)
    },

    /**
     * Delete queue
     *
     * @return {Number}
     */
    totalForDelete() {
      return this.value.filter(phone => phone._delete).length
    },

    /**
     * Total for insert
     *
     * @return {Array}
     */
    totalForInsert() {
      return this.value.filter(
        phone =>
          !phone.id &&
          phone.number &&
          // Has only prefix value but the user did not added any number
          (!this.callingPrefix ||
            (this.callingPrefix && this.callingPrefix !== phone.number))
      ).length
    },

    /**
     * Total number of phone numbers
     *
     * @return {Number}
     */
    totalPhones() {
      return this.value.length
    },

    /**
     * Get the predefined calling prefix
     *
     * @return {String|null}
     */
    callingPrefix() {
      return this.field.callingPrefix
    },
  },
  methods: {
    /**
     * Provide a function that fills a passed form object with the
     *
     * field's internal value attribute
     */
    fill(form) {
      if (!this.callingPrefix) {
        form.fill(this.field.attribute, this.value)
        return
      }

      // Remove phones with only prefix
      form.fill(
        this.field.attribute,
        reject(cloneDeep(this.value), phone => {
          return (
            !phone._delete &&
            this.callingPrefix &&
            phone.number.trim() === this.callingPrefix.trim()
          )
        })
      )
    },

    /**
     * Set the field initial value
     */
    setInitialValue() {
      this.value = cloneDeep(this.field.value ? this.field.value : [])
    },

    /**
     * Handle the field value change
     *
     * @param  {mixed} value
     *
     * @return {Void}
     */
    handleChange(value) {
      this.value = cloneDeep(value)

      if (this.totalPhones === 0) {
        this.newPhone()
      }

      this.realInitialValue = cloneDeep(this.value)
    },

    /**
     * Remove phone
     *
     * @param  {Number} index
     *
     * @return {Void}
     */
    removePhone(index) {
      if (!this.value[index].id) {
        this.value.splice(index, 1)
      } else {
        this.value[index]._delete = true
      }

      this.form.errors.clear(this.field.attribute + '.' + index + '.number')

      if (
        this.totalPhones - this.totalForDelete === 0 ||
        (this.totalPhones - this.totalForDelete === 0 &&
          this.totalForDelete > 0)
      ) {
        this.newPhone()
      }
    },

    /**
     * Add new phone
     *
     * @return {Void}
     */
    newPhone() {
      this.value.push({
        number: this.callingPrefix || '',
        type: this.field.type,
      })
    },
  },
  mounted() {
    this.initialize()

    this.$nextTick(() => {
      if (this.totalPhones === 0) {
        this.newPhone()
        this.realInitialValue = cloneDeep(this.value)
      }
    })
  },
}
</script>
