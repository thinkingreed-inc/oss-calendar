<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" persistent max-width="290px">
      <v-card class="minipopupwrap-padding">
        <v-card-text>
          {{ propsTitle }}
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="close">OK</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
export default {
  props: {
    propsDialog: {
      type: Boolean,
      default: false
    },
    propsTitle: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      dialog: false
    }
  },
  watch: {
    propsDialog(after_val, before_val) {
      console.log('watch = ' + after_val)
      if (after_val) {
        this.edit()
      }
    }
  },
  methods: {
    async edit() {
      this.dialog = true
    },
    close() {
      console.log('close')
      this.dialog = false
      this.$emit('returnMessageDialog')
    }
  }
}
</script>
