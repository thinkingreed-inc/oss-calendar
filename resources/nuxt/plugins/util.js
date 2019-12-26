import Vue from 'vue'

Vue.mixin({
  methods: {
    /**
     * 配列内のオブジェクト検索
     *
     * 使用例：
     * roleDatas = [{ text: '管理者', value: 1 },{ text: '一般', value: 2 }]
     * valueが1のテキストを取得するためには、
     * getFilterdText(roleDatas, 1)
     *
     * @param targetDatas
     * @param targetValue
     * @param value
     * @param value
     * @returns {string|*}
     */
    getFilterdText(targetDatas, targetValue, value = 'value', text = 'text') {
      const filtered = targetDatas.filter(function(obj) {
        return obj[value] === targetValue
      })
      if (filtered.length === 1) {
        return filtered[0][text]
      }
      return ''
    },

    /**
     * 対象オブジェクトに参照渡しコピーする
     *
     * @param targetObj
     * @param obj
     */
    copyByRefToTargetObj(targetObj, obj) {
      for (let key in targetObj) {
        if (targetObj[key] !== undefined && obj[key] !== undefined) {
          if (targetObj[key] != null && typeof targetObj[key] === 'object') {
            this.copyByRefToTargetObj(targetObj[key], obj[key])
          } else {
            targetObj[key] = obj[key]
          }
        }
      }
    },

    /**
     * 次のページにパラメータを渡す
     *
     * @param params
     */
    setTransferParams(params) {
      this.$store.dispatch('params/setParams', params)
    },

    /**
     * Object.assignでは、2階層目にObjectがあると参照渡しになってしまうため、再帰的な値渡しの独自関数
     *
     * @param object
     * @returns {any}
     */
    deepCopy(object) {
      let node
      if (object === null) {
        node = object
      } else if (Array.isArray(object)) {
        node = object.slice(0) || []
        node.forEach(n => {
          if ((typeof n === 'object' && n !== {}) || Array.isArray(n)) {
            n = this.deepCopy(n)
          }
        })
      } else if (typeof object === 'object') {
        node = Object.assign({}, object)
        Object.keys(node).forEach(key => {
          if (typeof node[key] === 'object' && node[key] !== {}) {
            node[key] = this.deepCopy(node[key])
          }
        })
      } else {
        node = object
      }
      return node
    },

    getRecurrenceUnits() {
      var recurrence_unit = [
        {
          value: 'DAILY',
          text: '日ごと'
        },
        {
          value: 'WEEKLY',
          text: '週間ごと'
        },
        {
          value: 'MONTHLY',
          text: '月ごと'
        },
        {
          value: 'YEARLY',
          text: '年ごと'
        }
      ]
      return recurrence_unit
    },

    calendarFormatYear(date) {
      const year = date.getFullYear()
      return year
    },

    calendarFormatDate(date) {
      if (date === null) return ''
      const year = date.getFullYear()
      const month = date.getMonth() + 1
      const day = date.getDate()
      return year + '-' + month + '-' + day
    },

    calendarFormatTime(date, not_view_seconds = true) {
      if (date === null) return ''
      var hours = date.getHours()
      hours = ('00' + hours).slice(-2)
      var minutes = date.getMinutes()
      minutes = ('00' + minutes).slice(-2)
      var seconds = date.getSeconds()
      seconds = ('00' + seconds).slice(-2)
      if (not_view_seconds) {
        return hours + ':' + minutes
      } else {
        return hours + ':' + minutes + ':' + seconds
      }
    },

    getReminderMinutes() {
      let minutes = [
        { text: '10分前', value: 10 },
        { text: '30分前', value: 30 },
        { text: '60分前', value: 60 }
      ]
      return minutes
    },

    getCalendarTypes() {
      let types = [
        { text: '月表示(個人)', value: 1 },
        { text: '週表示(個人)', value: 2 },
        { text: '日表示(個人)', value: 3 }
      ]
      return types
    },
    getfullCalendarType(type_id) {
      // getCalendarTypes の value値とfullcalendarの表示タイプを合わせる
      let types = {
        1: 'dayGridMonth',
        2: 'timeGridWeek',
        3: 'timeGridDay'
      }
      return types[type_id]
    },
    getHolidayYears(year) {
      var years = []
      // 前後2年の年をセット
      for (var i = -2; i <= 2; i++) {
        years.push({ text: year + i, value: year + i })
      }
      return years
    }
  }
})
