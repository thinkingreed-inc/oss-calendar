<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" max-width="290px">
      <v-card>
        <v-card-text>
          削除しますか？
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <single-submit-button
            :disabled="errors.any()"
            flat
            :onclick="destroy"
          >
            <template v-slot:text>
              削除
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
        id: -1
      },
      // レコードの初期値
      defaultValue: {
        id: -1
      }
    }
  },
  computed: {
    // ダイアログのタイトルを作成と更新で使い分ける
    title() {
      return this.selected.id === -1 ? '新規登録' : '編集'
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
    // データ編集(読み出し)
    async edit() {
      this.selected.id = this.propsSelectedObj.id
      this.dialog = true
    },
    // データ削除
    async destroy() {
      try {
        await this.$axios.delete(this.propsModelUrl + this.selected.id)
        console.log('Deleted : id=' + this.selected.id)
        this.$emit('refresh')
        this.close()
      } catch (e) {
        console.log('Error : ' + e.response.data)
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
