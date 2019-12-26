<template>
  <v-btn
    :disabled="disabled || processing"
    :loading="processing"
    :color="color"
    @click="handleClick"
  >
    <slot name="text">Submit</slot>
  </v-btn>
</template>

<script>
export default {
  name: 'SingleSubmitButton',
  props: {
    // A function which returns Promise.
    onclick: {
      type: Function,
      required: true
    },
    disabled: {
      type: Boolean,
      default: false
    },
    color: {
      type: String,
      default: 'primary'
    }
  },
  data() {
    return {
      processing: false
    }
  },
  methods: {
    handleClick(event) {
      if (this.processing) return
      this.processing = true
      this.onclick(event).then(() => {
        this.processing = false
      })
    }
  }
}
</script>
<style scoped>
.custom-loader {
  animation: loader 1s infinite;
  display: flex;
}
@-moz-keyframes loader {
  from {
    transform: rotate(0);
  }
  to {
    transform: rotate(360deg);
  }
}
@-webkit-keyframes loader {
  from {
    transform: rotate(0);
  }
  to {
    transform: rotate(360deg);
  }
}
@-o-keyframes loader {
  from {
    transform: rotate(0);
  }
  to {
    transform: rotate(360deg);
  }
}
@keyframes loader {
  from {
    transform: rotate(0);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
