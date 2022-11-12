<template>
  <i-modal
    id="boardSort"
    size="sm"
    form
    @submit="save"
    :ok-disabled="sortingBeingSaved"
    :ok-title="$t('app.apply')"
    :title="$t('deal.sort_by')"
  >
    <div class="flex">
      <div class="mr-2 grow">
        <i-form-select
          v-model="localValue.field"
          @change="$emit('update:modelValue', localValue)"
        >
          <option value="board_order" v-t="'board.sort_by'"></option>
          <option
            value="expected_close_date"
            v-t="'fields.deals.expected_close_date'"
          ></option>
          <option value="created_at" v-t="'app.creation_date'"></option>
          <option value="amount" v-t="'fields.deals.amount'"></option>
          <option value="name" v-t="'deal.name'"></option>
        </i-form-select>
      </div>
      <div>
        <i-form-select
          v-model="localValue.direction"
          @change="$emit('update:modelValue', localValue)"
        >
          <option value="asc">Asc (<span v-t="'app.ascending'"></span>)</option>
          <option value="desc">
            Desc (<span v-t="'app.descending'"></span>)
          </option>
        </i-form-select>
      </div>
    </div>
    <div class="mt-3">
      <i-form-checkbox
        id="remember_sort_selection"
        name="remember_sort_selection"
        v-model:checked="remember"
      >
        {{ $t('app.remember_selection') }}<br />
        <small
          v-text="
            $t('deal.pipeline.remember_sorting_info', { name: pipeline.name })
          "
        >
        </small>
      </i-form-checkbox>
      <div class="mt-3 ml-6" v-if="remember && pipeline.user_default_sort_data">
        <p
          class="text-sm text-danger-600"
          v-text="
            $t('deal.pipeline.previous_remember_sorting_note', {
              name: pipeline.name,
            })
          "
        ></p>
      </div>
    </div>
  </i-modal>
</template>
<script>
export default {
  emits: ['sort-applied', 'update:modelValue'],
  props: {
    modelValue: Object,
    pipeline: {
      required: true,
      type: Object,
    },
  },
  data: () => ({
    remember: false,
    localValue: {},
    sortingBeingSaved: false,
  }),
  watch: {
    modelValue: {
      handler: function (newVal, oldVal) {
        this.localValue = newVal
      },
      immediate: true,
    },
  },
  methods: {
    hideModal() {
      this.$iModal.hide('boardSort')
    },
    /**
     * Save sort options
     *
     * @return {Void}
     */
    save() {
      if (this.remember) {
        this.sortingBeingSaved = true
        Innoclapps.request()
          .post('/deals/board/' + this.pipeline.id + '/sort', this.localValue)
          .then(({ data }) => {
            this.remember = false

            this.$store.commit('pipelines/UPDATE', {
              id: data.id,
              item: data,
            })
            this.hideModal()
          })
          .finally(() => (this.sortingBeingSaved = false))
      } else {
        this.hideModal()
      }

      this.$emit('sort-applied')
    },
  },
}
</script>
