<template>
  <div>{{ propsLabelName }}<span class="required_symbol">*</span></div>
</template>

<script>
export default {
  props: {
    propsLabelName: {
      type: String,
      default: ''
    }
  }
}
</script>

<style scoped>
.required_symbol {
  color: red;
}
</style>
