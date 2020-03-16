<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" persistent max-width="300px">
      <v-form lazy-validation @submit.prevent>
        <v-card>
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
                    @keyup.enter="save()"
                  ></v-text-field>
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
            <v-btn color="blue darken-1" text @click="close">キャンセル</v-btn>
          </v-card-actions>
        </v-card>
      </v-form>
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
      if (after_val) {
        this.edit()
      }
    }
  },
  methods: {
    // パスワード更新(読み出し)
    async edit() {
      try {
        this.selected.id = this.propsSelectedObj.id
        console.log('Password Edited : id=' + this.selected.id)
        this.dialog = true
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    // パスワード更新(書き込み)
    async update() {
      try {
        await this.$axios.patch(
          '/api/user/password/' + this.selected.id,
          this.selected
        )
        console.log('Password Updated : id=' + this.selected.id)
      } catch (e) {
        throw e
      }
    },
    // ダイアログの保存ボタン
    async save() {
      try {
        await this.localValidate()
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
