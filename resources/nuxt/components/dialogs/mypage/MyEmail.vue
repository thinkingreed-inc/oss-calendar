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
                    v-model="selected.email"
                    v-validate="'required|email'"
                    label="メールアドレス"
                    data-vv-name="email"
                    :error-messages="errors.collect('email')"
                    counter="256"
                    type="tel"
                    data-vv-as="メールアドレス"
                  >
                    <template v-slot:label><required-label propsLabelName="メールアドレス" /></template>
                  </v-text-field>
                </v-flex>
                <v-flex
                  v-if="confirm_email !== null && confirm_email.length > 0"
                  class="xs12"
                >
                  <p>
                    {{ confirm_email }}
                    宛に「メールアドレスの変更」メールが送信されています。
                  </p>
                  <p><a @click="resend">確認メールの再送</a></p>
                  <p><a @click="cancel">確認メールの取り消し</a></p>
                </v-flex>
              </v-layout>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <single-submit-button :disabled="errors.any()" flat :onclick="save">
              <template v-slot:text>
                変更
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
      title: 'メールアドレスの変更',
      dialog: false,
      confirm_email: '',

      // 選択中のレコードの値
      selected: {
        email: ''
      },
      // レコードの初期値
      defaultValue: {
        email: ''
      }
    }
  },
  watch: {
    propsDialog(after_val, before_val) {
      console.log('watch = ' + after_val)
      this.dialog = after_val
    }
  },
  created() {
    this.getConfirmEmail()
  },
  methods: {
    close() {
      console.log('close')
      this.initialValidate()
      this.selected = this.deepCopy(this.defaultValue)
      this.$emit('close')
    },
    // 更新(書き込み)
    async update() {
      try {
        const res = await this.$axios.patch(
          '/api/mypage/email/edit',
          this.selected
        )
        this.$store.dispatch('message/setMessage', res.data.message)
        this.$store.dispatch('message/setSuccess')
        console.log('Email Updated')
      } catch (e) {
        throw e
      }
    },
    // ダイアログの保存ボタン
    async save() {
      try {
        await this.localValidate()
        await this.update()
        this.getConfirmEmail()
        this.close()
      } catch (e) {
        await this.serverSideValidate()
      }
    },
    // 再送
    async resend() {
      try {
        const res = await this.$axios.patch('/api/mypage/email/resend')
        this.getConfirmEmail()
        this.$store.dispatch('message/setMessage', res.data.message)
        this.$store.dispatch('message/setSuccess')
        this.close()
      } catch (e) {
        await this.serverSideValidate()
      }
    },
    // 取消
    async cancel() {
      try {
        const res = await this.$axios.delete('/api/mypage/email/cancel')
        this.getConfirmEmail()
        this.$store.dispatch('message/setMessage', res.data.message)
        this.$store.dispatch('message/setSuccess')
        this.close()
      } catch (e) {
        await this.serverSideValidate()
      }
    },
    // 確認メールの取得
    async getConfirmEmail() {
      try {
        const res = await this.$axios.get('/api/mypage/email/confirm')
        this.confirm_email = res.data.email
      } catch (e) {
        await this.serverSideValidate()
      }
    }
  }
}
</script>
