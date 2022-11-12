<template>
  <div>
    <form-folder
      v-for="(folder, index) in folders"
      :folder="folder"
      :index="index"
      :folders="folders"
      :key="index + folder.name"
    />
  </div>
</template>
<script>
import FormFolder from './EmailAccountFormFolder'
export default {
  components: { FormFolder },
  props: {
    folders: Array,
  },
}
</script>
