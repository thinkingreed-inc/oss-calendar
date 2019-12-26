<template>
  <!-- 削除確認ダイアログのレイアウト -->
  <v-layout row justify-center>
    <v-dialog v-model="dialog" max-width="290px">
      <v-card>
        <v-card-text>
          {{ mgsEnableDisableBody }}
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <single-submit-button :disabled="errors.any()" flat :onclick="save">
            <template v-slot:text>
              {{ mgsEnableDisableAction }}
            </template>
          </single-submit-button>
          <v-btn color="blue darken-1" text @click="close">キャンセル</v-btn>
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
    propsModelUrl: {
      type: String,
      default: ''
    },
    propsModelName: {
      type: String,
      default: ''
    },
    propsTitle: {
      type: String,
      default: ''
    },
    propsDialog: {
      type: Boolean,
      default: false
    },
    propsSelectedObj: {
      type: Object,
      default() {
        return {}
      }
    }
  },
  data() {
    return {
      dialog: false,

      // 選択中のレコードの値
      selected: {
        id: -1,
        is_enable: true
      },
      // レコードの初期値
      defaultValue: {
        id: -1,
        is_enable: true
      }
    }
  },
  computed: {
    mgsEnableDisableBody() {
      return this.selected.is_enable === true
        ? '無効にしますか？'
        : '有効にしますか？'
    },
    mgsEnableDisableAction() {
      return this.selected.is_enable === true ? '無効にします' : '有効にします'
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
    // 有効無効更新(読み出し)
    async edit() {
      try {
        const res = await this.$axios.get(
          this.propsModelUrl + this.propsSelectedObj.id
        )
        this.copyByRefToTargetObj(this.selected, res.data)
        this.dialog = true
        console.log('EnableDisable Created : id=' + this.selected.id)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    // 有効無効更新(書き込み)
    async update() {
      try {
        const res = await this.$axios.patch(
          '/api/enable_disable/' +
            this.propsModelName +
            '/' +
            this.propsSelectedObj.id
        )
        this.$emit('passByReference', this.propsSelectedObj, res.data)
        console.log('EnableDisable Updated : id=' + this.selected.id)
      } catch (e) {
        throw e
      }
    },
    // ダイアログの保存ボタン
    async save() {
      try {
        await this.update()
        this.close()
      } catch (e) {
        await this.serverSideValidate()
      }
    },
    close() {
      console.log('close')
      this.selected = this.deepCopy(this.defaultValue)
      this.dialog = false
      this.initialValidate()
      this.$emit('close')
    }
  }
}
</script>
