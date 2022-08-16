<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" persistent max-width="600px">
      <v-form lazy-validation>
        <v-card>
          <v-card-title>
            <span class="headline"
              ><slot name="title">{{ title }}</slot></span
            >
          </v-card-title>
          <v-card-text>
            <v-container grid-list-md>
              <v-layout wrap>
                <v-flex class="xs8">
                  <v-text-field
                    v-model="selected.username"
                    v-validate="'required'"
                    label="ユーザ名"
                    data-vv-name="username"
                    :error-messages="errors.collect('username')"
                    counter="16"
                    required
                  >
                    <template v-slot:label><required-label propsLabelName="ユーザ名" /></template>
                  </v-text-field>
                </v-flex>
                <v-flex class="xs6">
                  <v-text-field
                    v-model="selected.lastname"
                    v-validate="'required'"
                    label="苗字"
                    data-vv-name="lastname"
                    :error-messages="errors.collect('lastname')"
                    required
                  >
                    <template v-slot:label><required-label propsLabelName="苗字" /></template>
                  </v-text-field>
                </v-flex>
                <v-flex class="xs6">
                  <v-text-field
                    v-model="selected.firstname"
                    v-validate="'required'"
                    label="名前"
                    data-vv-name="firstname"
                    :error-messages="errors.collect('firstname')"
                    required
                  >
                    <template v-slot:label><required-label propsLabelName="名前" /></template>
                  </v-text-field>
                </v-flex>
                <v-flex class="xs12">
                  <v-text-field
                    v-model="selected.email"
                    v-validate="'required' | 'email'"
                    label="メールアドレス"
                    data-vv-name="email"
                    :error-messages="errors.collect('email')"
                    required
                  >
                    <template v-slot:label><required-label propsLabelName="メールアドレス" /></template>
                  </v-text-field>
                </v-flex>

                <!-- 所属部署 -->
                <v-flex class="xs12">
                  <two-department-select
                    ref="department"
                    :props-default-departments="selected.departments"
                    @setSelectedDepartments="setSelectedDepartments"
                  />
                </v-flex>

                <!-- ホーム画面 -->
                <v-flex class="xs8">
                  <v-select
                    v-model="selected.home_page_id"
                    :items="getCalendarTypes()"
                    label="ホーム画面"
                  >
                    <template v-slot:label><required-label propsLabelName="ホーム画面" /></template>
                  </v-select>
                </v-flex>

                <v-flex class="xs6">
                  <v-select
                    v-model="selected.role_id"
                    v-validate="'required' | 'role_id'"
                    :items="roleDatas"
                    label="権限"
                    data-vv-name="role_id"
                    :error-messages="errors.collect('role_id')"
                    outline
                  >
                    <template v-slot:label><required-label propsLabelName="権限" /></template>
                  </v-select>
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
import RequiredLabel from '~/components/label/RequiredLabel'
import SingleSubmitButton from '~/components/buttons/SingleSubmitButton'
import TwoDepartmentSelect from '~/components/TwoDepartmentSelect'

export default {
  inject: ['$validator'],
  components: {
    RequiredLabel,
    SingleSubmitButton,
    TwoDepartmentSelect
  },
  props: {
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
      color: 'blue darken-1',
      dialog: false,

      roleDatas: [
        { text: '管理者', value: 1 },
        { text: '一般', value: 2 }
      ],
      // 選択中のレコードの値
      selected: {},
      // レコードの初期値
      defaultValue: {
        id: -1,
        username: '',
        lastname: '',
        firstname: '',
        email: '',
        role_id: 1,
        is_enable: true,
        departments: [],
        home_page_id: 1
      }
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
      if (after_val) {
        if (Object.keys(this.propsSelectedObj).length === 0) {
          this.create()
        } else {
          this.edit()
        }
      }
    }
  },
  created() {
    this.selected = this.deepCopy(this.defaultValue)
  },
  methods: {
    // データ作成(読み出し)
    async create() {
      this.selected = this.deepCopy(this.defaultValue)
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
        const res = await this.$axios.post('/api/user/admin', this.selected)
        this.selected = res.data
        console.log('stored : id=' + res.data.id)
        this.$emit('refresh')
      } catch (e) {
        throw e
      }
    },
    // データ編集(読み出し)
    async edit() {
      try {
        const res = await this.$axios.get(
          '/api/user/admin/' + this.propsSelectedObj.id
        )
        this.copyByRefToTargetObj(this.selected, res.data)
        this.dialog = true
        console.log('Edit : id=' + this.selected.id)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
      // 所属部署取得
      this.getDepartmentOfUser(this.propsSelectedObj.id)
    },
    // データ編集(書き込み)
    async update() {
      try {
        const res = await this.$axios.put(
          '/api/user/admin/' + this.selected.id,
          this.selected
        )
        // 対象一覧の更新
        this.$emit('passByReference', this.propsSelectedObj, res.data)
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
      } catch (e) {
        await this.serverSideValidate()
        console.log('validate Error : ' + e.response.data)
        console.log('validate this.dialog: ' + this.dialog)
      }
    },
    close() {
      console.log('close')
      this.initialValidate()
      this.selected = this.deepCopy(this.defaultValue)
      this.$refs.department.setInit(this.defaultValue.departments) //２つのセレクトボックスの初期化
      this.dialog = false
      this.$emit('close')
    },
    // 部署選択設定
    setSelectedDepartments(departments) {
      // セット
      this.selected.departments = departments
    },
    // 所属部署取得
    async getDepartmentOfUser(user_id) {
      try {
        // ユーザー部署取得
        const option = { user_id: user_id, join_model: 'user' }
        const res = await this.$axios.get('/api/department', { params: option })
        var departments = res.data
        // 部署を画面セット用に変換
        var departmentItems = []
        departments.forEach(function(department) {
          departmentItems.push({
            value: department.id,
            text: department.name
          })
        })

        // 部署をセット
        this.$refs.department.setInit(departmentItems)
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
