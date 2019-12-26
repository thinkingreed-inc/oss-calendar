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
                <!-- ホーム画面 -->
                <v-flex class="xs8">
                  <v-select
                    v-model="selected.home_page_id"
                    :items="getCalendarTypes()"
                    label="ホーム画面"
                  ></v-select>
                </v-flex>

                <!-- 通知 -->
                <v-flex class="xs12">
                  <reminder-setting
                    ref="reminder"
                    :props-reminder="selected.default_reminders_method_id"
                    :props-reminder-minutes="selected.overrides_minutes"
                    props-validate-column="overrides_minutes"
                    @setInitReminder="setInitReminder"
                    @setSelectedReminders="setSelectedReminders"
                  />
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
      <!-- Messageダイアログのレイアウト(OKボタン)  -->
      <message-dialog
        ref="message"
        :props-dialog="messageDialog"
        :props-title="messageTitle"
        @returnMessageDialog="returnMessageDialog"
      ></message-dialog>
    </v-dialog>
  </v-layout>
</template>
<script>
import SingleSubmitButton from '~/components/buttons/SingleSubmitButton'
import ReminderSetting from '~/components/ReminderSetting'
import MessageDialog from '~/components/dialogs/MessageDialog'

export default {
  components: {
    SingleSubmitButton,
    ReminderSetting,
    MessageDialog
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
      messageDialog: false, // Messageダイアログの表示フラグ
      messageTitle: '',

      title: '個人設定',
      dialog: false,
      // 選択中のレコードの値
      selected: {},
      // レコードの初期値
      defaultValue: {
        id: this.$auth.user.id,
        home_page_id: 1,
        default_reminders_method_id: 1,
        overrides_minutes: 10,
        departments: []
      }
    }
  },
  watch: {
    propsDialog(after_val, before_val) {
      console.log('watch = ' + after_val)
      this.edit()
      this.dialog = after_val
    }
  },
  created() {
    this.selected = this.deepCopy(this.defaultValue)
  },
  methods: {
    // データ編集(読み出し)
    async edit() {
      try {
        const res = await this.$axios.get('/api/myself')
        // 個人設定セット
        this.copyByRefToTargetObj(this.selected, res.data)
        // 通知設定セット
        this.getReminder(this.selected.id)
        // 所属部署セット
        this.getDepartmentOfUser(this.selected.id)
        console.log('Edit Myself : ')
      } catch (e) {
        console.log('Error Myself : ' + e.response.data)
      }
    },
    close() {
      console.log('close')
      this.initialValidate()
      this.selected = this.deepCopy(this.defaultValue)
      this.$refs.reminder.setInit(
        this.defaultValue.default_reminders_method_id,
        this.defaultValue.overrides_minutes
      )
      this.$emit('close')
    },
    returnMessageDialog() {
      this.messageDialog = false
      this.close()
    },
    // 更新(書き込み)
    async update(info) {
      try {
        await this.$axios.patch('/api/mypage/setting', info)
        console.log('MySetting Updated')
      } catch (e) {
        throw e
      }
    },
    // ダイアログの保存ボタン
    async save() {
      try {
        await this.localValidate()
        await this.update(this.selected)

        this.messageTitle = '個人設定が完了しました'
        this.messageDialog = true
      } catch (e) {
        await this.serverSideValidate()
      }
    },
    // 通知設定取得
    async getReminder(user_id) {
      try {
        const res = await this.$axios.get('/api/mypage/reminder/' + user_id)
        var reminder = res.data
        this.setSelectedReminders(
          reminder.default_reminders_method_id,
          reminder.overrides_minutes
        )
        this.setInitReminder()
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    // 通知設定(初期セット)
    setInitReminder() {
      if (this.$refs.reminder) {
        this.$refs.reminder.setInit(
          this.selected.default_reminders_method_id,
          this.selected.overrides_minutes
        )
      }
    },
    // 通知設定セット
    setSelectedReminders(reminder, reminder_minutes, validate_column = null) {
      if (validate_column) {
        this.$validator.errors.remove(validate_column)
      }
      this.selected.default_reminders_method_id = reminder
      this.selected.overrides_minutes = reminder_minutes
    },
    // 所属部署取得
    async getDepartmentOfUser(user_id) {
      try {
        // ユーザー部署取得
        const option = { user_id: user_id, join_model: 'user' }
        const res = await this.$axios.get('/api/department', { params: option })
        // 部署を画面セット用に変換
        var departmentItems = []
        res.data.forEach(function(department) {
          departmentItems.push({
            value: department.id,
            text: department.name
          })
        })
        // セット
        this.selected.departments = departmentItems
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    }
  },
  provide() {
    return {
      // validator
      validator: this.$validator
    }
  }
}
</script>
