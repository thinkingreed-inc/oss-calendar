<template>
  <v-layout wrap>
    <v-flex class="xs4">
      <v-switch v-model="selected_reminder" label="通知"></v-switch>
    </v-flex>
    <v-flex class="xs8">
      <v-select
        v-model="selected_reminder_minutes"
        :items="getReminderMinutes()"
        :value="selected_reminder_minutes"
        :error-messages="validator.errors.collect(propsValidateColumn)"
        label="何分前"
      ></v-select>
    </v-flex>
  </v-layout>
</template>

<script>
export default {
  inject: ['validator'],
  props: {
    propsReminder: {
      type: Number,
      default: 0
    },
    propsReminderMinutes: {
      type: Number,
      default: 0
    },
    propsValidateColumn: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      selected_reminder: this.propsReminder,
      selected_reminder_minutes: this.propsReminderMinutes
    }
  },
  watch: {
    selected_reminder() {
      if (this.selected_reminder) {
        if (this.selected_reminder_minutes == null)
          this.selected_reminder_minutes = this.propsReminderMinutes
      } else {
        this.selected_reminder_minutes = null
      }
      this.$emit(
        'setSelectedReminders',
        this.selected_reminder,
        this.selected_reminder_minutes,
        this.propsValidateColumn
      )
    },
    selected_reminder_minutes() {
      this.$emit(
        'setSelectedReminders',
        this.selected_reminder,
        this.selected_reminder_minutes,
        this.propsValidateColumn
      )
    }
  },
  async mounted() {
    // 初期、呼び出し元から値をセット
    this.$emit('setInitReminder')
  },
  methods: {
    //再び初期化するときに実行する関数
    async setInit(reminder, reminder_minutes) {
      this.selected_reminder = reminder
      this.selected_reminder_minutes = reminder_minutes
    }
  }
}
</script>
