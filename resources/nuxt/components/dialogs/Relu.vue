<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" persistent max-width="400px">
      <v-form lazy-validation>
        <v-card>
          <v-card-title>
            <span class="headline"><slot name="formTitle">繰り返し</slot></span>
          </v-card-title>
          <v-card-text>
            <v-container grid-list-md>
              <v-layout wrap>
                <v-flex class="sm8">
                  <v-text-field
                    v-model="selected.recurrence_interval"
                    v-validate="'required'"
                    :error-messages="errors.collect('recurrence_interval')"
                    data-vv-name="recurrence_interval"
                    min="1"
                    type="number"
                  >
                    <template v-slot:prepend><required-label propsLabelName="繰り返す間隔:" /></template>
                  </v-text-field>
                </v-flex>
                <v-flex class="sm4">
                  <v-autocomplete
                    v-model="selected.recurrence_unit"
                    v-validate="'required'"
                    :items="recurrence_unit"
                    data-vv-name="recurrence_unit"
                    :error-messages="errors.collect('type_id')"
                  ></v-autocomplete>
                </v-flex>

                <v-flex xs12>
                  <v-menu
                    ref="endDateMenu"
                    v-model="endDateMenu"
                    :close-on-content-click="false"
                    :nudge-right="40"
                    :return-value.sync="selected.end_date"
                    full-width
                    min-width="290px"
                    offset-y
                    transition="scale-transition"
                  >
                    <template v-slot:activator="{ on }">
                      <v-text-field
                        v-model="selected.end_date"
                        label="終了日"
                        prepend-icon="event"
                        readonly
                        v-on="on"
                      >
                        <template v-slot:label><required-label propsLabelName="終了日" /></template>
                      </v-text-field>
                    </template>
                    <v-date-picker
                      v-model="selected.end_date"
                      no-title
                      scrollable
                    >
                      <v-spacer></v-spacer>
                      <v-btn color="primary" text @click="endDateMenu = false"
                        >キャンセル</v-btn
                      >
                      <v-btn
                        color="primary"
                        text
                        @click="$refs.endDateMenu.save(selected.end_date)"
                        >OK</v-btn
                      >
                    </v-date-picker>
                  </v-menu>
                </v-flex>
              </v-layout>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" @click="save">OK</v-btn>
            <v-btn color="blue darken-1" text @click="clear()"
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

export default {
  components: {
    RequiredLabel
  },
  props: {
    propsReluDialog: {
      type: Boolean,
      default: false
    },
    propsDateClickDate: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      startDateMenu: false,
      endDateMenu: false,

      dialog: false,
      recurrence_unit: this.getRecurrenceUnits(),

      // 選択中のレコードの値
      selected: {},
      // レコードの初期値
      defaultValue: {}
    }
  },
  watch: {
    propsReluDialog(after_val, before_val) {
      console.log('watch = ' + after_val)
      this.dialog = after_val
    },
    propsDateClickDate(after_val, before_val) {
      console.log('watch = ' + after_val)
      this.selected.start_date = after_val
      this.selected.end_date = after_val
    }
  },
  methods: {
    setInit(selectedRecurrence, defaultValueRecurrence) {
      this.selected = selectedRecurrence
      this.defaultValue = defaultValueRecurrence
      this.dialog = true
    },
    // ダイアログの保存ボタン
    save() {
      this.dialog = false
      this.$emit('setSelectedRecurrence', this.selected)
    },
    // ダイアログの保存ボタン
    clear() {
      this.dialog = false
      this.$emit('clearSelectedRecurrence')
      this.selected = this.deepCopy(this.defaultValue)
    }
  }
}
</script>
