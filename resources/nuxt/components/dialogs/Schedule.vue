<template>
  <v-layout row justify-center>
    <!-- 登録編集画面 開始-->
    <v-dialog v-model="dialog" persistent max-width="600px">
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
                <v-flex class="xs12 sm4">
                  <v-select
                    v-model="selected.event_type_id"
                    v-validate="'required'"
                    data-vv-as="予定タイプ"
                    :error-messages="errors.collect('event_type_id')"
                    :items="types"
                    label="予定タイプ"
                    data-vv-name="event_type_id"
                    name="event_type_id"
                  >
                    <template v-slot:label><required-label propsLabelName="予定タイプ" /></template>
                  </v-select>
                </v-flex>
                <v-flex class="xs12 sm8">
                  <v-text-field
                    v-model="selected.summary"
                    v-validate="'required'"
                    data-vv-as="件名"
                    label="件名"
                    name="summary"
                    data-vv-name="summary"
                    :error-messages="errors.collect('summary')"
                  >
                    <template v-slot:label><required-label propsLabelName="件名" /></template>
                  </v-text-field>
                </v-flex>
                <v-flex sm3 xs6>
                  <v-menu
                    ref="startDateMenu"
                    v-model="startDateMenu"
                    :close-on-content-click="false"
                    :nudge-right="40"
                    :return-value.sync="selected.start_date"
                    transition="scale-transition"
                    offset-y
                    full-width
                    min-width="290px"
                  >
                    <template v-slot:activator="{ on }">
                      <v-text-field
                        v-model="selected.start_date"
                        label="開始日"
                        prepend-icon="event"
                        readonly
                        v-on="on"
                      ></v-text-field>
                    </template>
                    <v-date-picker
                      v-model="selected.start_date"
                      no-title
                      scrollable
                    >
                      <v-spacer></v-spacer>
                      <v-btn text color="primary" @click="startDateMenu = false"
                        >キャンセル</v-btn
                      >
                      <v-btn
                        text
                        color="primary"
                        @click="$refs.startDateMenu.save(selected.start_date)"
                        >OK</v-btn
                      >
                    </v-date-picker>
                  </v-menu>
                  <v-combobox
                    v-show="isTimeView"
                    v-model="selected.start_date_time"
                    :items="getTimes()"
                    :item-value="selected.start_date_time"
                    prepend-icon="access_time"
                    label="開始時刻"
                    type="tel"
                  ></v-combobox>
                </v-flex>
                <v-flex sm3 xs6>
                  <v-menu
                    ref="endDateMenu"
                    v-model="endDateMenu"
                    :close-on-content-click="false"
                    :nudge-right="40"
                    :return-value.sync="selected.end_date"
                    transition="scale-transition"
                    offset-y
                    full-width
                    min-width="290px"
                  >
                    <template v-slot:activator="{ on }">
                      <v-text-field
                        v-model="selected.end_date"
                        label="終了日"
                        prepend-icon="event"
                        readonly
                        v-on="on"
                      ></v-text-field>
                    </template>
                    <v-date-picker
                      v-model="selected.end_date"
                      no-title
                      scrollable
                    >
                      <v-spacer></v-spacer>
                      <v-btn text color="primary" @click="endDateMenu = false"
                        >キャンセル</v-btn
                      >
                      <v-btn
                        text
                        color="primary"
                        @click="$refs.endDateMenu.save(selected.end_date)"
                        >OK</v-btn
                      >
                    </v-date-picker>
                  </v-menu>
                  <v-combobox
                    v-show="isTimeView"
                    v-model="selected.end_date_time"
                    :items="getTimes()"
                    :item-value="selected.end_date_time"
                    prepend-icon="access_time"
                    label="終了時刻"
                    type="tel"
                  ></v-combobox>
                </v-flex>
                <v-flex sm2 xs4>
                  <v-checkbox
                    v-model="selected.allday"
                    label="終日"
                  ></v-checkbox>
                </v-flex>
                <v-flex v-show="recurrenceSelect" sm4 xs>
                  <v-select
                    v-model="selected.recurrence.recurrence_id"
                    v-validate="'required'"
                    :error-messages="errors.collect('recurrence_id')"
                    :items="recurrenceDates"
                    data-vv-name="recurrence_id"
                    :disabled="recurrenceSelectDisabled"
                    @input="openRelu"
                  >
                    <template v-slot:label><required-label propsLabelName="繰り返し設定" /></template>
                  </v-select>
                </v-flex>
              </v-layout>

              <!-- 詳細入力 開始-->
              <v-layout v-show="isDetailView" wrap>
                <v-flex class="xs12">
                  <v-text-field
                    v-model="selected.location"
                    prepend-icon="location_on"
                    label="場所"
                  ></v-text-field>
                </v-flex>
                <v-flex class="xs12">
                  <v-textarea
                    v-model="selected.description"
                    prepend-icon="comment"
                    name="description"
                    label="メモ"
                    rows="2"
                  ></v-textarea>
                </v-flex>
                <!-- 通知 -->
                <v-flex class="xs12">
                  <reminder-setting
                    v-show="isTimeView"
                    ref="reminder"
                    :props-reminder="selected.reminder"
                    :props-reminder-minutes="selected.reminder_minutes"
                    props-validate-column="reminder_minutes"
                    @setInitReminder="setInitReminder"
                    @setSelectedReminders="setSelectedReminders"
                  />
                </v-flex>
                <v-flex class="xs12">
                  <event-publish
                    ref="event"
                    :props-visibility-id="selected.visibility_id"
                    :props-public-setting-id="selected.public_setting_id"
                    @setInitEventPublish="setInitEventPublish"
                    @setEventPublish="setEventPublish"
                  />
                </v-flex>
                <v-flex class="xs12">
                  <two-user-select
                    ref="users"
                    :props-default-users="selected.users"
                    @setSelectedUsers="setSelectedUsers"
                  />
                  <span
                    v-if="errors.has('users')"
                    dense
                    outlined
                    type="error"
                    class="error--text"
                  >
                    {{ errors.first('users')[0] }}
                  </span>
                </v-flex>
              </v-layout>
              <!-- 詳細入力 終了-->
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              v-show="!isDetailView"
              color="blue darken-1"
              text
              @click="isDetailView = true"
              >詳細入力</v-btn
            >
            <single-submit-button flat :onclick="save">
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
    <relu
      ref="relu"
      @clearSelectedRecurrence="clearSelectedRecurrence"
      @setSelectedRecurrence="setSelectedRecurrence"
    >
    </relu>
    <!-- 登録編集画面 終了-->
    <!-- 詳細画面 開始-->
    <v-dialog v-model="detailDialog" max-width="400px" persistent>
      <v-form lazy-validation>
        <v-card class="popupwrap-padding">
          <v-card-title>
            <span class="headline"><slot name="formTitle">詳細</slot></span>
          </v-card-title>
          <v-card-text>
            <v-container grid-list-md>
              <v-layout wrap>
                <v-list width="100%">
                  <v-list-item-group>
                    <v-list-item disabled>
                      <v-list-item-icon
                        ><v-icon>subtitles</v-icon></v-list-item-icon
                      >
                      <v-list-item-content
                        >{{ getFilterdText(types, selected.event_type_id) }}
                        {{ selected.summary }}</v-list-item-content
                      >
                    </v-list-item>
                    <v-list-item disabled>
                      <v-list-item-icon
                        ><v-icon>event</v-icon></v-list-item-icon
                      >
                      <v-list-item-content class="display_datetime">
                        <div>
                          <span>{{ selected.start_date }}</span>
                          <span v-show="!selected.allday">{{
                            selected.start_date_time
                          }}</span>
                        </div>
                        <div>
                          <span>～</span>
                        </div>
                        <div>
                          <span>{{ selected.end_date }}</span>
                          <span v-show="!selected.allday">{{
                            selected.end_date_time
                          }}</span>
                        </div>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item
                      v-show="
                        selected !== undefined &&
                          selected.recurrence.recurrence_id === 2
                      "
                      disabled
                    >
                      <v-list-item-icon
                        ><v-icon>subtitles</v-icon></v-list-item-icon
                      >
                      <v-list-item-content>
                        {{ selected.recurrence.recurrence_interval }}
                        {{
                          getFilterdText(
                            getRecurrenceUnits(),
                            selected.recurrence.recurrence_unit
                          )
                        }}
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item disabled>
                      <v-list-item-icon
                        ><v-icon>location_on</v-icon></v-list-item-icon
                      >
                      <v-list-item-content>{{
                        selected.location
                      }}</v-list-item-content>
                    </v-list-item>
                    <v-list-item disabled>
                      <v-list-item-icon
                        ><v-icon>comment</v-icon></v-list-item-icon
                      >
                      <v-list-item-content>{{
                        selected.description
                      }}</v-list-item-content>
                    </v-list-item>
                    <v-list-item v-show="selected.reminder" disabled>
                      <v-list-item-icon><v-icon>info</v-icon></v-list-item-icon>
                      <v-list-item-content>{{
                        getFilterdText(
                          getReminderMinutes(),
                          selected.reminder_minutes
                        )
                      }}</v-list-item-content>
                    </v-list-item>
                  </v-list-item-group>
                </v-list>
              </v-layout>
              <!-- 詳細入力 終了-->
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" @click="deleteDialog()">削除</v-btn>
            <v-btn color="primary" @click="editDialog()">編集画面</v-btn>
            <v-btn color="blue darken-1" text @click="detailClose()"
              >閉じる</v-btn
            >
          </v-card-actions>

          <!-- 新規登録、編集のダイアログのレイアウト -->
          <confirm-dialog
            :props-dialog="confirmDialog"
            props-title="削除しますか？"
            props-yes="はい"
            props-no="キャンセル"
            @returnConfirmDialog="returnConfirmDialog"
          ></confirm-dialog>
          <!-- 繰り返し予定削除のダイアログのレイアウト -->
          <recurrence-dialog
            :props-dialog="recurrenceEditDialog"
            props-title="繰り返し編集しますか？"
            props-all="全て編集"
            props-part="一日のみ編集"
            props-no="キャンセル"
            @returnRecurrenceDialog="returnRecurrenceEditDialog"
          ></recurrence-dialog>
          <!-- 繰り返し予定編集のダイアログのレイアウト -->
          <recurrence-dialog
            :props-dialog="recurrenceDeleteDialog"
            props-title="繰り返し削除しますか？"
            props-all="全て削除"
            props-part="一日のみ削除"
            props-no="キャンセル"
            @returnRecurrenceDialog="returnRecurrenceDeleteDialog"
          ></recurrence-dialog>
        </v-card>
      </v-form>
    </v-dialog>
    <!-- 詳細画面 最後-->
  </v-layout>
</template>

<script>
import RequiredLabel from '~/components/label/RequiredLabel'
import SingleSubmitButton from '~/components/buttons/SingleSubmitButton'
import TwoUserSelect from '~/components/TwoUserSelect'
import Relu from '~/components/dialogs/Relu'
import moment from 'moment'
import ReminderSetting from '~/components/ReminderSetting'
import ConfirmDialog from '~/components/dialogs/ConfirmDialog'
import RecurrenceDialog from './RecurrenceDialog'
import EventPublish from '~/components/EventPublish'

export default {
  components: {
    RequiredLabel,
    Relu,
    SingleSubmitButton,
    TwoUserSelect,
    ReminderSetting,
    ConfirmDialog,
    RecurrenceDialog,
    EventPublish
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
      startDateMenu: false, //開始日のポップアップカレンダーの表示フラグ
      endDateMenu: false, //終了日のポップアップカレンダーの表示フラグ
      isTimeView: false, //終日チェックを外したら時刻選択を表示するフラグ

      isDetailView: false, //詳細入力フォームの表示フラグ

      dialog: false, //スケジュールダイアログの表示フラグ
      detailDialog: false, //スケジュールダイアログの表示フラグ
      confirmDialog: false, // YesNoダイアログの表示フラグ
      recurrenceEditDialog: false,
      recurrenceDeleteDialog: false,
      // recurrenceDialog: false,
      recurrenceSelect: true,
      recurrenceSelectDisabled: false,
      // 選択中のレコードの値
      selected: {},
      // レコードの初期値
      defaultValue: {
        id: -1,
        event_type_id: null,
        summary: '',
        start_date: '',
        end_date: '',
        start_date_time: '',
        end_date_time: '',
        allday: 1,
        location: '',
        description: '',
        reminder: 1,
        reminder_minutes: 10,
        visibility_id: '1',
        public_setting_id: '0',
        one_day_edit: 0,
        deleted: 0,
        users: this.defaultUserValue(),
        recurrence: {
          recurrence_id: 1,
          recurrence_interval: 1,
          recurrence_unit: 'DAILY',
          start_start_date: '',
          start_end_date: '',
          end_date: ''
        }
      },
      recurrenceDates: [
        {
          value: 1,
          text: '繰り返さない'
        },
        {
          value: 2,
          text: '繰り返し'
        }
      ],
      types: []
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
      //console.log('watch = ' + after_val)
      this.dialog = after_val
    },
    propsDateClickDate(after_val, before_val) {
      //console.log('watch = ' + after_val)
      //this.selected.start_date = after_val
      //this.selected.end_date = after_val
    },
    'selected.allday'(after_val, before_val) {
      // console.log('watch = ' + after_val)
      this.isTimeView = !after_val
    },
    'selected.recurrence.recurrence_id'(after_val, before_val) {
      this.setRecurrenceDates()
      //繰り返しさせないときの初期化対応
      if (after_val === 1) {
        this.selected.recurrence = this.deepCopy(this.defaultValue.recurrence)
      }
    },
    'selected.recurrence.recurrence_interval'(after_val, before_val) {
      console.log('recurrence_interval = ' + after_val)
      this.setRecurrenceDates()
    },
    'selected.recurrence.recurrence_unit'(after_val, before_val) {
      console.log('recurrence_unit = ' + after_val)
      this.setRecurrenceDates()
    },
    'selected.recurrence.end_date'(after_val, before_val) {
      console.log('end_date = ' + after_val)
      this.setRecurrenceDates()
    }
  },
  created() {
    this.selected = this.deepCopy(this.defaultValue)
    this.getDefaultReminder(this.$auth.user.id)
  },
  mounted() {
    this.setTypes()
  },
  methods: {
    defaultUserValue() {
      let default_user = [
        {
          value: this.$auth.user.id,
          text: this.$auth.user.lastname + ' ' + this.$auth.user.firstname
        }
      ]
      return default_user
    },
    setInit(info) {
      console.log(info)
      this.selected = this.deepCopy(this.defaultValue)
      var startDate = moment(info.startStr)._d
      var endDate = null
      if (info.allDay) {
        endDate = moment(info.endStr).add(-1, 'days')._d
        this.selected.allday = 1
      } else {
        endDate = moment(info.endStr)._d
        this.selected.allday = null
      }
      this.selected.start_date = this.calendarFormatDate(startDate)
      this.selected.end_date = this.calendarFormatDate(endDate)
      this.selected.start_date_time = this.calendarFormatTime(startDate)
      this.selected.end_date_time = this.calendarFormatTime(endDate)
      this.selected.recurrence.end_date = this.calendarFormatDate(startDate)
      // グループカレンダーにおいて、自分以外のカレンダーをクリックしたとき
      if (info.resource !== undefined) {
        //ログインユーザーでない人のカレンダーをクリックした場合
        if (this.$auth.user.id !== Number(info.resource.id)) {
          var selectedUser = {
            value: Number(info.resource.id),
            text: info.resource.title
          }
          this.selected.users.push(selectedUser)
          if (this.$refs.users) {
            this.$refs.users.setInit(this.selected.users)
          }
        }
      } else {
        // 自分自身のカレンダーをクリックした場合、ログインユーザー(デフォルト値)をセット
        if (this.$refs.users) {
          this.defaultValue.users = this.defaultUserValue()
          this.$refs.users.setInit(this.defaultValue.users)
        }
      }
      this.dialog = true
    },
    setShowInit(selected) {
      this.selected = this.deepCopy(this.defaultValue)
      this.copyByRefToTargetObj(this.selected, selected)
      console.log('selected = ', selected)
      console.log('this.selected = ', this.selected)
      var date = moment(selected.start_date)._d
      var start_date = this.calendarFormatDate(date)
      var start_date_time = this.calendarFormatTime(date)
      date = moment(selected.end_date)._d
      var end_date = this.calendarFormatDate(date)
      var end_date_time = this.calendarFormatTime(date)
      this.selected.start_date = start_date
      this.selected.start_date_time = start_date_time
      this.selected.end_date = end_date
      this.selected.end_date_time = end_date_time
      this.setInitAtendeeOfSchedule(this.selected.id)
      this.setInitEventPublish()
      this.setInitReminder()
      if (this.selected.recurrence.end_date.length > 0) {
        this.recurrenceSelectDisabled = true
      }

      this.detailDialog = true
    },
    // 繰返しスケジュールを開く
    openRelu() {
      if (this.selected.recurrence.recurrence_id === 2) {
        this.$refs.relu.setInit(
          this.selected.recurrence,
          this.defaultValue.recurrence
        )
      }
    },
    // データ作成(読み出し)
    async create() {
      try {
        console.log('Created : ')
        this.dialog = true
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    // データ作成(書き込み)
    async store() {
      try {
        const res = await this.$axios.post('/api/schedule', this.selected)
        this.copyByRefToTargetObj(this.selected, res.data)
        console.log('stored : id=' + res.data.id)
      } catch (e) {
        throw e
      }
    },
    // データ編集(読み出し)
    async edit() {
      try {
        const res = await this.$axios.get('/api/schedule/' + this.selected.id)
        this.copyByRefToTargetObj(this.selected, res.data)
        this.dialog = true
        console.log('Edit : id=' + this.selected.id)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    // データ編集(書き込み)
    async update() {
      try {
        const res = await this.$axios.put(
          '/api/schedule/' + this.selected.id,
          this.selected
        )
        // 対象一覧の更新
        this.$emit('passByReference', this.selected, res.data)
        console.log('Updated : id=' + res.data.id)
        // 自分自身の場合、fetchUserを行う
        if (this.$auth.user.id === res.data.id) {
          this.$auth.fetchUser()
        }
      } catch (e) {
        throw e
      }
    },
    // ダイアログの保存ボタン
    async save() {
      try {
        await this.localValidate()
        if (this.selected.id === -1) {
          // 作成
          await this.store()
        } else {
          // 更新
          await this.update()
        }
        this.close()
        this.$emit('close')
      } catch (e) {
        await this.serverSideValidate()
        console.log('validate Error : ')
        console.log(e.response.data)
        console.log('validate this.dialog: ' + this.dialog)
      }
    },

    close() {
      console.log('close')
      this.initialValidate()
      this.selected = this.deepCopy(this.defaultValue)
      this.isDetailView = false
      this.$refs.users.setInit(this.defaultValue.users) //２つのセレクトボックスの初期化
      this.$refs.reminder.setInit(
        this.defaultValue.reminder,
        this.defaultValue.reminder_minutes
      )
      this.dialog = false
      this.recurrenceSelect = true
      this.recurrenceSelectDisabled = false
      this.confirmDialog = false
    },
    //繰り返し予定かどうかで編集ダイアログの切り替え
    editDialog() {
      console.log(this.selected.id)
      console.log(this.selected.recurrence.recurrence_id)
      if (this.selected.recurrence.recurrence_id == 2) {
        this.recurrenceEditDialog = true
      } else {
        this.detailDialog = false
        this.dialog = true
      }
    },
    //繰り返し予定かどうかで削除ダイアログの切り替え
    deleteDialog() {
      console.log(this.selected.id)
      console.log(this.selected.recurrence.recurrence_id)
      if (this.selected.recurrence.recurrence_id == 2) {
        this.recurrenceDeleteDialog = true
      } else {
        this.confirmDialog = true
      }
    },
    detailClose() {
      console.log('close')
      this.initialValidate()
      this.selected = this.deepCopy(this.defaultValue)
      this.isDetailView = false
      this.detailDialog = false
    },
    getTimes() {
      let times = []
      let mm = ['00', '15', '30', '45']
      for (let number = 0; number < 24; number++) {
        let hh = ('00' + number).slice(-2)
        for (let i = 0; i < mm.length; i++) {
          times.push(hh + ':' + mm[i])
        }
      }
      return times
    },
    async setTypes() {
      var types = []
      try {
        const res = await this.$axios.get('/api/event_type')
        const data = res.data.data
        for (let number = 0; number < data.length; number++) {
          types[number] = {
            value: data[number].id,
            text: data[number].name
          }
        }
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
      this.types = types
    },
    // 日付、時間の差を見る
    checkDateDiff(field) {
      const start_datetime = `${this.selected.start_date} ${this.selected.start_date_time}`
      const end_datetime = `${this.selected.end_date} ${this.selected.end_date_time}`

      return 'foo'
      console.log(start_datetime, end_datetime)
    },
    // 公開設定
    async setInitEventPublish() {
      if (this.$refs.event) {
        this.$refs.event.setInit(
          this.selected.visibility_id,
          this.selected.public_setting_id
        )
      }
    },
    // 参加者
    async setInitAtendeeOfSchedule(schedule_id) {
      var userItems = []
      try {
        // 所属ユーザー取得
        const option = { id: schedule_id, join_model: 'attendee' }
        const res = await this.$axios.get('/api/user', { params: option })
        const attendees = res.data
        attendees.forEach(function(user) {
          userItems.push({
            value: user.id,
            text: user.lastname + ' ' + user.firstname
          })
        })
        this.selected.users = userItems
        if (this.$refs.users) {
          this.$refs.users.setInit(userItems)
        }
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
      return userItems
    },
    // デフォルト通知設定取得
    async getDefaultReminder(user_id) {
      try {
        const res = await this.$axios.get('/api/mypage/reminder/' + user_id)
        var reminder = res.data
        // 通知設定をセット
        this.defaultValue.reminder = reminder.default_reminders_method_id
        this.defaultValue.reminder_minutes = reminder.overrides_minutes
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    // ユーザー選択設定
    setSelectedUsers(users) {
      this.selected.users = users
      this.errors.remove('users')
    },
    // 公開範囲 設定
    setEventPublish(visibility_id, public_setting_id) {
      this.selected.visibility_id = visibility_id
      this.selected.public_setting_id = public_setting_id
    },
    // 削除確認ダイアログ戻り処理
    returnConfirmDialog(yes) {
      console.log(yes)
      if (yes) {
        this.delete()
      }
      this.confirmDialog = false
    },
    // 繰り返し編集選択ダイアログ戻り処理
    returnRecurrenceEditDialog(ret) {
      console.log('edit')
      console.log(this.selected.recurrence.recurrence_id)
      if (ret == 2) {
        this.selected.one_day_edit = true
        this.selected.deleted = false
        this.selected.recurrence = this.deepCopy(this.defaultValue.recurrence)
        this.detailDialog = false
        this.recurrenceSelect = false
        this.dialog = true
      } else if (ret == 1) {
        this.selected.start_date = this.selected.recurrence.start_start_date
        this.selected.end_date = this.selected.recurrence.start_end_date
        this.detailDialog = false
        this.dialog = true
      }
      this.recurrenceEditDialog = false
    },
    // 繰り返し削除選択ダイアログ戻り処理
    returnRecurrenceDeleteDialog(ret) {
      console.log(ret)
      if (ret == 2) {
        this.oneDayDelete()
      } else if (ret == 1) {
        this.delete()
      }
      this.recurrenceDeleteDialog = false
    },
    // スケジュール削除
    async delete() {
      try {
        await this.$axios.delete('/api/schedule/' + this.selected.id)
        console.log('Deleted : id=' + this.selected.id)
        this.detailClose()
        this.$emit('close')
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    //繰り返しの一日のみ削除の処理(削除フラグ付きの予定を新規登録する)
    async oneDayDelete() {
      try {
        this.selected.one_day_edit = true
        this.selected.recurrence.recurrence_id = 1
        this.selected.deleted = true
        const res = await this.$axios.put(
          '/api/schedule/' + this.selected.id,
          this.selected
        )
        // 対象一覧の更新
        this.$emit('passByReference', this.selected, res.data)
        console.log('OneDayDeleted : id=' + res.data.id)
        // 自分自身の場合、fetchUserを行う
        if (this.$auth.user.id === res.data.id) {
          this.$auth.fetchUser()
        }
        this.detailClose()
        this.$emit('close')
      } catch (e) {
        throw e
      }
    },
    // 通知設定
    setInitReminder() {
      if (this.$refs.reminder) {
        this.$refs.reminder.setInit(
          this.selected.reminder,
          this.selected.reminder_minutes
        )
      }
    },
    setSelectedReminders(reminder, reminder_minutes, validate_column = null) {
      if (validate_column) {
        this.$validator.errors.remove(validate_column)
      }
      this.selected.reminder = reminder
      this.selected.reminder_minutes = reminder_minutes
    },
    // 繰返しスケジュール設定
    setSelectedRecurrence(selectedRecurrence) {
      this.selected.recurrence = selectedRecurrence
    },
    // 繰返しさせないスケジュール設定
    clearSelectedRecurrence() {
      this.selected.recurrence = this.deepCopy(this.defaultValue.recurrence)
    },
    setRecurrenceDates() {
      if (
        this.selected !== undefined &&
        this.selected.recurrence.recurrence_id === 2
      ) {
        var interval = this.selected.recurrence.recurrence_interval
        var recurrence_unit = this.getFilterdText(
          this.getRecurrenceUnits(),
          this.selected.recurrence.recurrence_unit
        )
        this.recurrenceDates = [
          {
            value: 1,
            text: '繰り返さない'
          },
          {
            value: 2,
            text: interval + recurrence_unit
          }
        ]
      } else {
        this.recurrenceDates = [
          {
            value: 1,
            text: '繰り返さない'
          },
          {
            value: 2,
            text: '繰り返し'
          }
        ]
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

<style scoped>
.display_datetime span {
  flex: none;
}
</style>
