<template>
  <div v-if="deal.status === 'open'">
    <div class="inline-flex">
      <i-button
        variant="success"
        v-if="deal.status !== 'won'"
        class="mr-2 px-5"
        :loading="requestInProgress['won']"
        :disabled="requestInProgress['won']"
        size="sm"
        v-i-tooltip="$t('deal.actions.mark_as_won')"
        @click="changeStatus('won')"
      >
        {{ $t('deal.status.won') }}
      </i-button>
      <i-popover
        :auto-hide="false"
        ref="lostPopover"
        v-if="deal.status !== 'lost'"
      >
        <template #title="{ hide }">
          <div class="flex justify-between">
            <p
              v-t="'deal.actions.mark_as_lost'"
              class="font-medium text-neutral-800 dark:text-neutral-100"
            />
            <a
              href="#"
              @click.prevent="hide"
              class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-300 dark:hover:text-neutral-100"
              ><icon icon="X" class="h-5 w-5"
            /></a>
          </div>
        </template>
        <i-button
          variant="danger"
          size="sm"
          class="px-5"
          v-i-tooltip="$t('deal.actions.mark_as_lost')"
        >
          {{ $t('deal.status.lost') }}
        </i-button>
        <template #popper>
          <div class="flex w-80 max-w-full flex-col">
            <i-form-group
              :label="$t('deal.lost_reasons.lost_reason')"
              label-for="lost_reason"
              optional
            >
              <lost-reason-field v-model="markAsLostForm.lost_reason" />
              <form-error field="lost_reason" :form="markAsLostForm" />
            </i-form-group>
            <i-button
              variant="danger"
              size="sm"
              block
              class="mt-4"
              :loading="requestInProgress['lost']"
              :disabled="requestInProgress['lost']"
              @click="changeStatus('lost')"
            >
              <span v-t="'deal.actions.mark_as_lost'"></span>
            </i-button>
          </div>
        </template>
      </i-popover>
    </div>
  </div>
  <div class="flex items-center" v-else>
    <i-badge size="lg" :variant="deal.status === 'won' ? 'success' : 'danger'">
      <icon
        icon="BadgeCheck"
        v-if="deal.status === 'won'"
        class="mr-1 h-4 w-4 text-current"
      />

      <icon
        icon="X"
        v-else-if="deal.status === 'lost'"
        class="mr-1 h-4 w-4 text-current"
      />

      {{ $t('deal.status.' + deal.status) }}
    </i-badge>
    <div>
      <i-button
        size="sm"
        class="ml-2 px-5"
        :disabled="requestInProgress['open']"
        :loading="requestInProgress['open']"
        variant="white"
        @click="changeStatus('open')"
      >
        <span v-t="'deal.reopen'"></span>
      </i-button>
    </div>
  </div>
</template>
<script>
import LostReasonField from '@/views/Deals/LostReasonField'
import Form from '@/components/Form/Form'
import { throwConfetti } from '@/utils'

export default {
  components: {
    LostReasonField,
    Form,
  },
  props: {
    deal: { type: Object, required: true },
    isFloating: { default: false, type: Boolean },
  },
  data: () => ({
    markAsLostForm: new Form({
      lost_reason: null,
    }),
    requestInProgress: {
      won: false,
      lost: false,
      open: false,
    },
  }),
  methods: {
    /**
     * Update the record in store
     */
    updateRecordInStore(record) {
      this.$store.dispatch('deals/updateRecordWhenViewing', {
        deal: record,
        isFloating: this.isFloating,
      })
    },

    /**
     * Change the deal status
     *
     * @param  {string} status
     *
     * @return {Void}
     */
    changeStatus(status) {
      if (status === 'lost') {
        this.markAsLost()
        return
      }

      this.requestInProgress[status] = true

      Innoclapps.request()
        .put(`/deals/${this.deal.id}/status/${status}`)
        .then(({ data }) => {
          this.updateRecordInStore(data)

          if (status === 'won') {
            throwConfetti()
          }
        })
        .finally(() => (this.requestInProgress[status] = false))
    },

    /**
     * Mark the deal as lost
     */
    markAsLost() {
      this.requestInProgress['lost'] = true

      this.markAsLostForm
        .put(`/deals/${this.deal.id}/status/lost`)
        .then(data => {
          this.$refs.lostPopover.hide()
          this.markAsLostForm.reset()
          this.updateRecordInStore(data)
        })
        .finally(() => (this.requestInProgress['lost'] = false))
    },
  },
}
</script>
