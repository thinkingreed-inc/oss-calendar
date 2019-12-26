import Vue from 'vue'
import VeeValidate, { Validator } from 'vee-validate'
import ja from 'vee-validate/dist/locale/ja'

Vue.use(VeeValidate)
Validator.localize('ja', ja)
Validator.localize({
  ja: {
    attributes: {
      email: 'メールアドレス',
      password: 'パスワード',
      password_confirmation: '再度入力したパスワード',
      username: 'ユーザー名',
      lastname: '苗字',
      firstname: '名前'
    }
  }
})
