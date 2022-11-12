<template>
  <div
    style="bottom: 0; right: 0"
    class="h-dropper fixed left-0 border border-neutral-300 bg-white shadow-sm dark:border-neutral-900 dark:bg-neutral-900 sm:left-56"
  >
    <i-modal
      size="sm"
      id="markAsLostModal"
      :title="$t('deal.actions.mark_as_lost')"
      form
      @submit="markAsLost(markingAsLostID)"
      :ok-disabled="markAsLostForm.busy"
      :ok-title="$t('deal.actions.mark_as_lost')"
      ok-variant="danger"
      @hidden="markAsLostModalHidden"
    >
      <i-form-group
        :label="$t('deal.lost_reasons.lost_reason')"
        label-for="lost_reason"
        optional
      >
        <lost-reason-field v-model="markAsLostForm.lost_reason" />
        <form-error field="lost_reason" :form="markAsLostForm" />
      </i-form-group>
    </i-modal>
    <div class="flex justify-end">
      <div
        class="h-dropper relative w-1/3 border-t-2 border-neutral-800 sm:w-1/5"
      >
        <draggable
          v-model="movedToDeleteList"
          :item-key="item => item.id"
          @change="movedToDelete"
          class="h-dropper dropper-delete dropper"
          :group="{ name: 'delete', put: () => true, pull: false }"
        >
          <template #item="{ element }"><div></div></template>
          <template #header>
            <div
              class="dropper-header h-dropper absolute inset-0 flex place-content-center items-center font-medium text-neutral-800 dark:text-neutral-200"
              v-t="'app.delete'"
            />
          </template>
        </draggable>
      </div>
      <div
        class="h-dropper relative w-1/3 border-t-2 border-danger-500 sm:w-1/5"
      >
        <draggable
          @change="movedToLost"
          v-model="movedToLostList"
          :item-key="item => item.id"
          class="h-dropper dropper-lost dropper"
          :group="{ name: 'lost', put: () => true, pull: false }"
        >
          <template #item="{ element }"><div></div></template>
          <template #header>
            <div
              class="dropper-header h-dropper absolute inset-0 flex place-content-center items-center font-medium text-neutral-800 dark:text-neutral-200"
              v-t="'deal.status.lost'"
            />
          </template>
        </draggable>
      </div>
      <div
        class="h-dropper relative w-1/3 border-t-2 border-success-500 sm:w-1/5"
      >
        <draggable
          @change="movedToWon"
          v-model="movedToLostWon"
          :item-key="item => item.id"
          class="h-dropper dropper-won dropper"
          :group="{ name: 'won', put: () => true, pull: false }"
        >
          <template #item="{ element }"><div></div></template>
          <template #header>
            <div
              class="dropper-header h-dropper absolute inset-0 flex place-content-center items-center font-medium text-neutral-800 dark:text-neutral-200"
              v-t="'deal.status.won'"
            />
          </template>
        </draggable>
      </div>
    </div>
  </div>
</template>
<script>
// https://stackoverflow.com/questions/51619243/vue-draggable-delete-item-by-dragging-into-designated-region
import draggable from 'vuedraggable'
import findIndex from 'lodash/findIndex'
import Form from '@/components/Form/Form'
import LostReasonField from '@/views/Deals/LostReasonField'
import { throwConfetti } from '@/utils'

export default {
  emits: ['deleted', 'won', 'refresh-requested'],
  components: { draggable, LostReasonField },
  props: {
    resourceId: { required: true },
    pipeline: { type: Object, required: true },
  },
  data: () => ({
    movedToDeleteList: [],
    movedToLostList: [],
    movedToLostWon: [],
    markingAsLostID: null,
    markAsLostForm: new Form({
      lost_reason: null,
    }),
  }),
  methods: {
    /**
     * Handle deal moved to delete dropper
     *
     * @param  {Object} e
     *
     * @return {Void}
     */
    movedToDelete(e) {
      if (e.added) {
        this.$store
          .dispatch('deals/destroy', e.added.element.id)
          .catch(error => {
            let deletedIndex = findIndex(this.movedToDeleteList, [
              'id',
              Number(e.added.element.id),
            ])

            this.movedToDeleteList.splice(deletedIndex, 1)

            this.requestRefresh()
          })
          .then(() => this.$emit('deleted', e.added.element))
      }
    },

    /**
     * Request board refresh
     *
     * @return {Void}
     */
    requestRefresh() {
      this.$emit('refresh-requested')
    },

    /**
     * Handle deal moved to lost dropper
     *
     * @param  {Object} e
     *
     * @return {Void}
     */
    movedToLost(e) {
      if (e.added) {
        this.markingAsLostID = e.added.element.id
        this.$iModal.show('markAsLostModal')
      }
    },

    /**
     * Handle the mark as lost modal hidden event
     *
     * @return {Void}
     */
    markAsLostModalHidden() {
      this.lostReason = null
      this.markingAsLostID = null
      this.requestRefresh()
    },

    /**
     * Mark the deal as lost
     *
     * @param {Integer} id
     *
     * @return {Void}
     */
    markAsLost(id) {
      this.markAsLostForm
        .put(`/deals/${id}/status/lost`)
        .then(() => this.$iModal.hide('markAsLostModal'))
    },

    /**
     * Mark the deal as lost
     *
     * @param {Integer} id
     *
     * @return {Void}
     */
    markAsWon(id) {
      Innoclapps.request()
        .put(`/deals/${id}/status/won`)
        .then(({ data }) => {
          throwConfetti()
          this.$emit('won', data)
          this.requestRefresh()
        })
    },

    /**
     * Handle deal moved to won dropper
     *
     * @param  {Object} e
     *
     * @return {Void}
     */
    movedToWon(e) {
      if (e.added) {
        this.markAsWon(e.added.element.id)
      }
    },
  },
}
</script>
<style>
.h-dropper {
  height: 75px;
}

.dropper .bottom-hidden {
  display: none;
}

.dropper-delete .sortable-chosen.sortable-ghost::before {
  background: black;
  content: ' ';
  min-height: 55px;
  min-width: 100%;
  display: block;
}

.dropper-lost .sortable-chosen.sortable-ghost::before {
  background: red;
  content: ' ';
  min-height: 55px;
  min-width: 100%;
  display: block;
}

.dropper-won .sortable-chosen.sortable-ghost::before {
  background: green;
  content: ' ';
  min-height: 55px;
  min-width: 100%;
  display: block;
}
</style>
