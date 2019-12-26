<template>
  <v-layout wrap>
    <v-container grid-list-xl>
      <v-layout>
        <v-card-title class="subtitle-1">
          公開
        </v-card-title>
        <v-radio-group
          v-model="selected_visibility_id"
          data-vv-name="visibility_id"
          row
        >
          <v-radio label="一般公開" value="1"></v-radio>
          <v-radio label="限定公開" value="2"></v-radio>
        </v-radio-group>
      </v-layout>

      <v-layout v-show="isSettingView" wrap>
        <v-card-title class="subtitle-1">
          公開設定
        </v-card-title>
        <v-radio-group
          v-model="selected_public_setting_id"
          data-vv-name="public_setting_id"
          row
        >
          <v-radio label="非公開" value="1"></v-radio>
          <v-radio label="予定あり" value="2"></v-radio>
        </v-radio-group>
      </v-layout>
    </v-container>
  </v-layout>
</template>

<script>
export default {
  props: {
    propsVisibilityId: {
      type: [Number, String],
      default: '0'
    },
    propsPublicSettingId: {
      type: [Number, String],
      default: '0'
    }
  },
  data() {
    return {
      isSettingView: false,
      selected_visibility_id: this.propsVisibilityId,
      selected_public_setting_id: this.propsPublicSettingId
    }
  },
  watch: {
    selected_visibility_id() {
      this.changeIds(
        this.selected_visibility_id,
        this.selected_public_setting_id
      )
    },
    selected_public_setting_id() {
      console.log(
        'watch: this.selected_public_setting_id',
        this.selected_public_setting_id
      )
      this.changeIds(
        this.selected_visibility_id,
        this.selected_public_setting_id
      )
    }
  },
  async mounted() {
    // 初期、呼び出し元から値をセット
    this.$emit('setInitEventPublish')
  },
  methods: {
    //再び初期化するときに実行する関数
    async setInit(visibility_id, public_setting_id) {
      if (visibility_id) {
        this.selected_visibility_id = visibility_id
      }
      if (public_setting_id) {
        this.selected_public_setting_id = public_setting_id
      }
      // 限定公開の場合、公開設定を表示
      if (this.selected_visibility_id == '2') {
        this.isSettingView = true
      } else {
        this.selected_public_setting_id = '0'
        this.isSettingView = false
      }
    },
    async changeIds(visibility_id, public_setting_id) {
      // 限定公開の場合、公開設定を表示
      if (visibility_id == '2') {
        this.isSettingView = true
      } else {
        this.selected_public_setting_id = '0'
        this.isSettingView = false
      }
      //console.log("visi = "+this.selected_visibility_id+" / pub = "+this.selected_public_setting_id);
      this.$emit('setEventPublish', visibility_id, public_setting_id)
    }
  }
}
</script>
