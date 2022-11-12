<template>
  <i-card :header="$t('company.companies')" no-body>
    <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
      <li class="px-4 py-4 sm:px-6">
        <div
          class="space-y-3 space-x-0 md:flex md:items-center md:justify-between md:space-y-0 lg:space-x-3"
        >
          <div>
            <h5
              class="font-medium leading-relaxed text-neutral-700 dark:text-neutral-100"
              v-t="'company.settings.automatically_associate_with_contacts'"
            />
            <p
              class="break-words text-sm text-neutral-600 dark:text-neutral-300"
              v-t="
                'company.settings.automatically_associate_with_contacts_info'
              "
            />
          </div>
          <div>
            <i-form-toggle
              :value="true"
              :unchecked-value="false"
              @change="submit"
              v-model="form.auto_associate_company_to_contact"
            />
          </div>
        </div>
      </li>
    </ul>
  </i-card>
</template>
<script>
import HandleSettingsForm from './HandleSettingsForm'
export default {
  mixins: [HandleSettingsForm],
}
</script>
