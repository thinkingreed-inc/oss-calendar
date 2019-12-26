<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" persistent max-width="290px">
      <v-card class="minipopupwrap-padding">
        <v-card-text>
          {{ selected.title }}
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <single-submit-button :disabled="errors.any()" flat :onclick="yes">
            <template v-slot:text>
              {{ selected.yes }}
            </template>
          </single-submit-button>
          <v-btn color="blue darken-1" text @click="close">{{
            selected.no
          }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
import SingleSubmitButton from '~/components/buttons/SingleSubmitButton'

export default {
  components: {
    SingleSubmitButton
  },
  props: {
    propsDialog: {
      type: Boolean,
      default: false
    },
    propsTitle: {
      type: String,
      default: ''
    },
    propsYes: {
      type: String,
      default: ''
    },
    propsNo: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      dialog: false,

      // 選択中のレコードの値
      selected: {},
      // レコードの初期値
      defaultValue: {
        title: this.propsTitle,
        yes: this.propsYes,
        no: this.propsNo
      }
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
  created() {
    this.selected = this.deepCopy(this.defaultValue)
  },
  methods: {
    async edit() {
      this.dialog = true
    },
    async yes() {
      this.$emit('returnConfirmDialog', true)
      this.close
    },
    close() {
      console.log('close')
      this.selected = this.deepCopy(this.defaultValue)
      this.dialog = false
      this.initialValidate()
      this.$emit('returnConfirmDialog')
    }
  }
}
</script>
