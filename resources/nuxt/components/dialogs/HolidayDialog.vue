<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" persistent max-width="600px">
      <v-card>
        <v-card-title>
          <span class="headline">
            <slot name="title">アップロード</slot>
          </span>
        </v-card-title>
        <v-card-text>
          <v-flex class="xs10">
            <v-file-input
              v-model="holiday_files"
              color="deep-purple accent-4"
              label="ファイル選択"
              multiple
              placeholder="ファイルを選択してください"
              prepend-icon="mdi-paperclip"
              :error-messages="errors.collect('holiday_files')"
              single-line
            ></v-file-input>
          </v-flex>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-flex class="xs3">
              <v-card-actions>
                <v-btn color="primary" @click="returnConfirmDialog"
                  >アップロード</v-btn
                >
              </v-card-actions>
            </v-flex>
            <v-btn color="blue darken-1" text @click="close">キャンセル</v-btn>
          </v-card-actions>
        </v-card-text>

        <message-dialog
          ref="message"
          :props-dialog="messageDialog"
          :props-title="messageTitle"
          @returnMessageDialog="returnMessageDialog"
        ></message-dialog>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
import MessageDialog from '~/components/dialogs/MessageDialog'

export default {
  components: {
    MessageDialog
  },
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
      dialog: false,
      holiday_files: [],
      messageDialog: false,
      messageTitle: '',
      options: {}
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
      this.initialValidate()
      this.selected = this.deepCopy(this.defaultValue)
      this.dialog = false
      this.$emit('close')
    },
    uploadClick() {
      this.confirmDialog = false
      // ファイルチェック
      var fileName
      if (this.holiday_files.length > 0) {
        fileName = this.holiday_files[0].name
      } else {
        this.messageTitle = 'ファイルを選択してください'
        this.messageDialog = true
        return false
      }

      // 拡張子チェック
      var type = fileName.split('.')
      if (type[type.length - 1].toLowerCase() != 'csv') {
        this.messageTitle = 'CSVファイルを選択してください'
        this.messageDialog = true
        return false
      }

      // アップロード処理実行
      this.uploadCSV()
    },
    async uploadCSV() {
      try {
        let options = new FormData()
        options.append('holiday_files', this.holiday_files[0])
        let config = {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
        await this.$axios.post('/api/holiday/admin/upload', options, config)
        this.messageTitle = 'CSVファイルの取り込みが完了しました'
        this.messageDialog = true
        console.log('UproadeCSV')
        this.$emit('refresh')
        this.close()
      } catch (e) {
        console.log('Error : ' + e.response.data)
        this.messageTitle = 'CSVファイルの取り込みに失敗しました'
        this.messageDialog = true
      }
    },

    returnConfirmDialog() {
      this.uploadClick()
      this.confirmDialog = false
    },

    returnMessageDialog() {
      this.messageDialog = false
    }
  }
}
</script>
