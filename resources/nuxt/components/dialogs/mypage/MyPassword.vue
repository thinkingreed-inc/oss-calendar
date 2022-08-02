<template>
  <v-layout>
    <v-dialog v-model="dialog" persistent max-width="500px">
      <v-form lazy-validation>
        <v-card class="popupwrap-padding">
          <v-card-title>
            <span class="headline"
              ><slot name="formTitle">{{ title }}</slot></span
            >
          </v-card-title>
          <v-card-text>
            <v-container grid-list-md>
              <v-layout wrap>
                <v-flex class="xs12">
                  <v-text-field
                    v-model="selected.password"
                    v-validate="'required'"
                    label="パスワード"
                    data-vv-name="password"
                    :error-messages="errors.collect('password')"
                    counter="256"
                    type="password"
                  >
                    <template v-slot:label><required-label propsLabelName="パスワード" /></template>
                  </v-text-field>
                </v-flex>
              </v-layout>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <single-submit-button :disabled="errors.any()" flat :onclick="save">
              <template v-slot:text>
                保存
              </template>
            </single-submit-button>
            <v-btn color="blue darken-1" text @click="close()"
              >キャンセル</v-btn
            >
          </v-card-actions>
        </v-card>
      </v-form>
    </v-dialog>
  </v-layout>
</template>

<script>
import RequiredLabel from '~/components/label/RequiredLabel'
import SingleSubmitButton from '~/components/buttons/SingleSubmitButton'

export default {
  components: {
    RequiredLabel,
    SingleSubmitButton
  },
  props: {
    propsTitle: {
      type: String,
      default: ''
    },
    propsDialog: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      title: 'パスワード更新',
      dialog: false,

      // 選択中のレコードの値
      selected: {
        id: -1,
        password: ''
      },
      // レコードの初期値
      defaultValue: {
        id: -1,
        password: ''
      }
    }
  },
  watch: {
    propsDialog(after_val, before_val) {
      console.log('watch = ' + after_val)
      this.dialog = after_val
    }
  },
  methods: {
    close() {
      console.log('close')
      this.initialValidate()
      this.selected = this.deepCopy(this.defaultValue)
      this.$emit('close')
    },
    // 更新(書き込み)
    async update(info) {
      try {
        await this.$axios.patch('/api/mypage/password', info)
        console.log('Password Updated')
      } catch (e) {
        throw e
      }
    },
    // ダイアログの保存ボタン
    async save() {
      try {
        await this.localValidate()
        await this.update(this.selected)
        this.close()
      } catch (e) {
        await this.serverSideValidate()
      }
    }
  }
}
</script>
