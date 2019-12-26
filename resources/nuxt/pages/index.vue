<template>
  <v-layout column justify-center align-center class="calender-container">
    <!-- <v-flex>
      <v-select
        v-model="selectedGroup"
        item-text="text"
        item-value="value"
        :items="calendarGroup"
        label="カレンダー表示"
        return-object
      ></v-select>
    </v-flex> -->
    <full-calendar
      ref="fullCalendar"
      :all-day-slot="allDaySlot"
      :default-view="defaultView"
      :plugins="calendarPlugins"
      :aspect-ratio="aspectRatio"
      :editable="editable"
      :event-duration-editable="eventDurationEditable"
      :event-limit="eventLimit"
      :first-day="firstDay"
      :header="header"
      :locale="locale"
      :now-indicator="nowIndicator"
      :resource-label-text="resourceLabelText"
      :scheduler-license-key="schedulerLicenseKey"
      :select-helper="selectHelper"
      :selectable="selectable"
      :slot-event-overlap="slotEventOverlap"
      :views="views"
      :dates-render="datesRender"
      :event-sources="eventSources"
      :resources="resources"
      :event-data-transform="eventDataTransform"
      @select="select"
      @eventClick="eventClick"
      @eventDrop="eventDrop"
      @eventDragStart="eventDragStart"
      @eventDragStop="eventDragStop"
      @eventResize="eventResize"
      @eventResizeStart="eventResizeStart"
      @eventResizeStop="eventResizeStop"
      @dateClick="dateClick"
    />
    <schedule ref="schedule" @close="close" />
  </v-layout>
</template>
<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
// 時間ごとに予定が管理できるtimegridプラグイン
import timeGridPlugin from '@fullcalendar/timegrid'
// 横軸が時間、縦軸が人
import resourceTimelinePlugin from '@fullcalendar/resource-timeline'
import jaLocale from '@fullcalendar/core/locales/ja'
// データのやり取り(クリックアクションでデータをゲットするなど)interactionプラグイン
import interactionPlugin from '@fullcalendar/interaction'
// 繰り返しスケジュール
import rrulePlugin from '@fullcalendar/rrule'
// a popular library for parsing, formatting, and manipulating dates
import momentPlugin from '@fullcalendar/moment'
import moment from 'moment'

import Schedule from '../components/dialogs/Schedule'

export default {
  middleware: 'auth',
  components: {
    Schedule,
    FullCalendar // make the <full-calendar /> tag available
  },
  data() {
    return {
      calendarDialog: false,
      dateClickDate: '',

      schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source', //ライセンス非表示
      calendarPlugins: [
        dayGridPlugin,
        timeGridPlugin,
        interactionPlugin,
        resourceTimelinePlugin,
        rrulePlugin,
        momentPlugin
      ],
      locale: jaLocale,
      header: {
        left: 'title',
        right: 'today prev next'
      },
      resourceLabelText: 'ユーザー',
      selectable: true,
      selectHelper: true,
      editable: true, // イベントを編集
      allDaySlot: true, // 終日表示の枠を表示
      defaultView: 'dayGridMonth', // 表示カレンダータイプ
      eventDurationEditable: true, // イベント期間をドラッグしで変更
      slotEventOverlap: false, // イベントを重ねて表示
      nowIndicator: true, //現在の時刻に赤い線の目印を用意する
      eventLimit: true, // allow "more" link when too many events
      firstDay: 1, // 月曜から表示
      aspectRatio: window.innerWidth / (window.innerHeight - 150), // width-to-height aspect ratio of the calendar
      defaultTimedEventDuration: '10:00:00',
      views: {
        // 日表示
        timeGridDay: {
          slotDuration: '00:15', //15分区切りにする
          slotLabelInterval: '01:00'
        },
        // 週表示
        timeGridWeek: {
          columnHeaderHtml: function(date) {
            const weekdays = ['日', '月', '火', '水', '木', '金', '土']
            return `${date.getDate()}<br><span class="weekday">(${
              weekdays[date.getDay()]
            })</span>`
          }
        },
        // 週リスト表示
        resourceTimelineWeek: {
          slotDuration: '01:00', //24時間区切りにする
          slotLabelInterval: '03:00', //直感的な3時間区切りにする
          resourceAreaWidth: '15%',
          buttonText: 'グループ週'
        },
        // 日リスト表示
        resourceTimelineDay: {
          slotDuration: '01:00', //1時間区切りにする
          slotLabelInterval: '01:00',
          resourceAreaWidth: '15%',
          buttonText: 'グループ日'
        }
      },
      selectedGroup: { text: '自分のみ', value: '0' },
      calendarGroup: [{ text: '自分のみ', value: '0' }],
      eventSources: [],
      resources: []
    }
  },
  watch: {
    selectedGroup: function(after_val, before_val) {
      this.close()
    }
  },
  created() {
    this.defaultView = this.getfullCalendarType(this.$auth.user.home_page_id)
  },
  mounted() {
    console.log('Mounted')
    this.$setCalendar(this)
    this.handleResize()
    window.addEventListener('resize', this.handleResize, false)
  },
  destroyed() {
    console.log('destroyed')
    this.$setCalendar(false)
    window.removeEventListener('resize', this.handleResize, false)
  },
  methods: {
    handleResize() {
      console.log(
        'Height : ',
        document.getElementById('main-container-wrap').clientHeight
      )
      const self = this
      this.aspectRatio =
        document.getElementById('main-container-wrap').clientWidth /
        (window.innerHeight - 150)
      console.log('aspectRatio : ', this.aspectRatio)
      return (function(self) {
        return function() {
          // self.$refs.fullCalendar.getApi().setOption('aspectRatio', window.innerWidth/(window.innerHeight - 100))
        }
      })(self)
    },
    select: function(selectionInfo) {
      console.log('select')
      this.$refs.schedule.setInit(selectionInfo)
    },
    eventDataTransform: function(event) {
      // Eventレンダリング前処理
      if (event.allday) {
        // 終日設定の場合、終了日付に +1日 加算
        var calcDate = new Date(moment(event.end).add(1, 'days'))
        event.end = moment(calcDate).format('YYYY-MM-DD')
      }
      return event
    },
    async eventClick(info) {
      // 休祝日クリック時 return
      if (info.event.id == 0) return false

      if (this.$nuxt.$loading.start) this.$nuxt.$loading.start(true)
      // 予定クリック処理
      try {
        console.log('info', info)
        const res = await this.$axios.get('/api/schedule/' + info.event.id)
        this.$refs.schedule.setShowInit(res.data)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
      /**
         alert('Event: ' + info.event.title)
         alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY)
         alert('View: ' + info.view.type)
         // change the border color just for fun
         info.el.style.borderColor = 'red'
         **/
      // Custom Loading End
      if (this.$nuxt.$loading.finish) this.$nuxt.$loading.finish(true)
    },
    async eventDrop(info) {
      console.log('eventDrop', info)
      if (this.$nuxt.$loading.start) this.$nuxt.$loading.start(true)
      try {
        var params = {}
        this.$set(params, 'allDay', info.event.allDay)
        if (info.newResource === null || info.oldResource === null) {
          this.$set(params, 'eventType', 'sameUser')
        } else {
          this.$set(params, 'eventType', 'changeUser')
          this.$set(params, 'oldUserId', info.oldResource.id)
          this.$set(params, 'newUserId', info.newResource.id)
        }
        if (info.event.allDay) {
          // 終日スケジュール表示時にpage/index.vueのeventDataTransform関数で１日足しているので、変更時は１日引く処理を入れる
          var calcDate = new Date(moment(info.event.end).add(-1, 'days'))
          var endSubDay = moment(calcDate)._d
          this.$set(
            params,
            'startDate',
            this.calendarFormatDate(info.event.start)
          )
          this.$set(params, 'endDate', this.calendarFormatDate(endSubDay))
        } else {
          this.$set(
            params,
            'startDate',
            this.calendarFormatDate(info.event.start)
          )
          this.$set(params, 'endDate', this.calendarFormatDate(info.event.end))
          this.$set(
            params,
            'startTime',
            this.calendarFormatTime(info.event.start)
          )
          this.$set(params, 'endTime', this.calendarFormatTime(info.event.end))
        }
        await this.$axios.patch('/api/schedule/drop/' + info.event.id, params)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
      // 更新後にスケジュールを取得し直す
      // 理由は、グループ週表示のときに２つの関連スケジュールの片方を動かしたときに、もう片方が動かないため
      let calendarApi = this.$refs.fullCalendar.getApi()
      calendarApi.render()
      /**
        alert('Event id: ' + info.event.id)
        alert('Event title: ' + info.event.title)
        alert('Event allDay: ' + info.event.allDay)
        alert('Event start: ' + info.event.start)
        alert('Event end: ' + info.event.end)
        console.log(info.event)
        **/
    },
    async eventDragStart(info) {
      console.log('eventDragStart')
      console.log(info.event)
      /*
        alert('Event id: ' + info.event.id)
        alert('Event title: ' + info.event.title)
        alert('Event allDay: ' + info.event.allDay)
        alert('Event start: ' + info.event.start)
        alert('Event end: ' + info.event.end)
        */
    },
    async eventResize(info) {
      console.log('eventResize')
      console.log(info.event)
      if (this.$nuxt.$loading.start) this.$nuxt.$loading.start(true)
      try {
        var params = {}
        this.$set(params, 'allDay', info.event.allDay)
        this.$set(params, 'eventType', 'sameUser')
        if (info.event.allDay) {
          // 終日スケジュール表示時にpage/index.vueのeventDataTransform関数で１日足しているので、変更時は１日引く処理を入れる
          var calcDate = new Date(moment(info.event.end).add(-1, 'days'))
          var endSubDay = moment(calcDate)._d
          this.$set(
            params,
            'startDate',
            this.calendarFormatDate(info.event.start)
          )
          this.$set(params, 'endDate', this.calendarFormatDate(endSubDay))
        } else {
          this.$set(
            params,
            'startDate',
            this.calendarFormatDate(info.event.start)
          )
          this.$set(params, 'endDate', this.calendarFormatDate(info.event.end))
          this.$set(
            params,
            'startTime',
            this.calendarFormatTime(info.event.start)
          )
          this.$set(params, 'endTime', this.calendarFormatTime(info.event.end))
        }
        await this.$axios.patch('/api/schedule/drop/' + info.event.id, params)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
      // 更新後にスケジュールを取得し直す
      // 理由は、グループ週表示のときに２つの関連スケジュールの片方を動かしたときに、もう片方が動かないため
      let calendarApi = this.$refs.fullCalendar.getApi()
      calendarApi.render()
      /*
        alert('Event id: ' + info.event.id)
        alert('Event title: ' + info.event.title)
        alert('Event allDay: ' + info.event.allDay)
        alert('Event start: ' + info.event.start)
        alert('Event end: ' + info.event.end)
        */
    },
    async eventResizeStart(info) {
      console.log('eventResizeStart')
      console.log(info.event)
      /*
        alert('Event id: ' + info.event.id)
        alert('Event title: ' + info.event.title)
        alert('Event allDay: ' + info.event.allDay)
        alert('Event start: ' + info.event.start)
        alert('Event end: ' + info.event.end)
        */
    },
    async eventResizeStop(info) {
      console.log('eventResizeStop')
      console.log(info.event)
      /*
        alert('Event id: ' + info.event.id)
        alert('Event title: ' + info.event.title)
        alert('Event allDay: ' + info.event.allDay)
        alert('Event start: ' + info.event.start)
        alert('Event end: ' + info.event.end)
        */
    },
    async eventDragStop(info) {
      console.log('eventDragStop')
      console.log(info.event)
      /*
        alert('Event id: ' + info.event.id)
        alert('Event title: ' + info.event.title)
        alert('Event allDay: ' + info.event.allDay)
        alert('Event start: ' + info.event.start)
        alert('Event end: ' + info.event.end)
        */
    },
    dateClick(info) {
      // スマホ・タブレットの場合はselectイベントを実行させるためには長押しが必要になる。
      // 使い勝手が非常に悪いため、dateClickでもスケジュール登録画面が表示されるようにしておく。
      if (/(Android)|(iPhone)|(iPad)/.test(navigator.userAgent)) {
        this.$refs.schedule.setInit(info)
      }
      /**
        alert('Clicked on: ' + info.dateStr)
        alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY)
        alert('Current view: ' + info.view.type)
        info.dayEl.style.backgroundColor = 'red'
        **/
    },
    // 必要な範囲内の期間にあるスケジュール取得
    async datesRender(info) {
      // Custom Loading Start
      if (this.$nuxt.$loading.start) this.$nuxt.$loading.start(true)

      //----------------
      // 休日セット
      //----------------
      let holidayEventSources = []
      try {
        const params = {
          activeStart: this.calendarFormatDate(info.view.activeStart),
          activeEnd: this.calendarFormatDate(info.view.activeEnd)
        }
        const res = await this.$axios.get('/api/holiday/event', {
          params: params
        })
        holidayEventSources = res.data
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }

      //----------------
      // 予定セット
      //----------------
      var viewType = info.view.type
      try {
        const params = {
          viewType: viewType,
          activeStart: this.calendarFormatDate(info.view.activeStart),
          activeEnd: this.calendarFormatDate(info.view.activeEnd),
          selectedGroup: this.selectedGroup
        }
        const res = await this.$axios.get('/api/schedule', { params: params })
        // 予定取得
        let tmpEventSources = res.data.eventSources
        // 予定と休日のイベントをマージ
        this.eventSources = tmpEventSources.concat(holidayEventSources)
        this.resources = res.data.resources
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }

      // Custom Loading End
      if (this.$nuxt.$loading.finish) this.$nuxt.$loading.finish(true)
    },
    datesDestroy() {
      console.log('datesDestroy : ')
    },
    // メニューからグループをクリックした際に呼び出される
    async updateGroup(groupid) {
      this.selectedGroup = { value: groupid }
    },
    async close() {
      //await this.datesRender(info)
      await this.setUserOfEvent(this.selectedGroup)
      let calendarApi = this.$refs.fullCalendar.getApi()
      //await this.datesRender(calendarApi)
      calendarApi.render()
    },
    // 選択された表示設定(ドロップダウンリスト)により、表示させるスケジュールのユーザーを取得
    async setUserOfEvent(select_data) {
      try {
        console.log(select_data.value)

        var values = select_data.value.split(':')
        var model = values[0]
        var id = values[1]

        // ユーザー取得
        let userIds = []
        if (!id) {
          // 自分のみ
          userIds.push(this.$auth.user.id)
        } else {
          const option = { id: id, join_model: model }
          const res = await this.$axios.get('/api/user', { params: option })
          var users = res.data
          users.forEach(function(user) {
            userIds.push(user.id)
          })
        }
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
    },
    changeCalendarViewType(viewName) {
      let calendarApi = this.$refs.fullCalendar.getApi()
      calendarApi.changeView(viewName)
    }
  }
}
</script>
