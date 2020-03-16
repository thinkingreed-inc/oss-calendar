<template>
  <div class="list">
    <h2>{{ title }}</h2>
    <div class="management-tablewrap">
      <!-- データテーブル部分
        rows-per-page-text="" 1ページあたり件数テキストの非表示
        :rows-per-page-items="[]" 1ページあたり件数セレクトの非表示
      -->
      <v-data-table
        :headers="headers"
        :items="dataList"
        :options.sync="options"
        class="elevation-1"
        :loading="tableLoading"
        :server-items-length="totalCount"
      >
        <template v-slot:top>
          <v-toolbar flat color="white">
            <!-- 追加ボタン フォームは下部 -->
            <v-btn
              small
              color="dark"
              class="mb-2"
              @click="
                selectedObj = {}
                dialogProfile = true
              "
            >
              追加
            </v-btn>

            <!-- アップロードボタン フォームは下部 -->
            <v-btn
              small
              color="dark"
              class="mb-2 upload_btn"
              @click="
                selectedObj = {}
                dialogUpload = true
              "
            >
              アップロード
            </v-btn>

            <v-spacer></v-spacer>

            <!-- 年選択 -->
            <v-flex class="xs1">
              <v-select
                v-model="holiday_year"
                :items="holiday_years"
                label="休祝日年"
              ></v-select>
            </v-flex>

            <v-text-field
              v-model="search"
              append-icon="search"
              label="検索"
              placeholder="検索"
              single-line
            ></v-text-field>

            <v-spacer></v-spacer>

            <!-- リスト更新ボタン -->
            <v-icon @click="refresh()">refresh</v-icon>
          </v-toolbar>
        </template>

        <template v-slot:item.is_enable="props">
          {{ getFilterdText(isEnableDatas, props.item.is_enable) }}
        </template>

        <template v-slot:item.action="props">
          <!-- 編集ボタン -->
          <v-icon
            class="mr-2"
            @click="
              selectedObj = props.item
              dialogProfile = true
            "
          >
            edit
          </v-icon>
          <!-- 有効無効ボタン -->
          <v-icon
            @click="
              selectedObj = props.item
              dialogEnableDisable = true
            "
          >
            how_to_reg
          </v-icon>
          <!-- 削除ボタン -->
          <v-icon
            @click="
              selectedObj = props.item
              dialogDestroy = true
            "
          >
            delete
          </v-icon>
        </template>

        <!-- 検索結果なし -->
        <template v-slot:no-results :value="true">
          "{{ search }}" の検索結果なし
        </template>
        <!-- データなしの時の表示 -->
        <template v-slot:no-data>
          データはありません。
        </template>

        <!-- フッターの件数表示 -->
        <template v-slot:pageText="props">
          {{ props.itemsLength }} 件中 {{ props.pageStart }} 件目 〜
          {{ props.pageStop }} 件目
        </template>
      </v-data-table>

      <!-- 削除確認ダイアログのレイアウト -->
      <data-destroy
        props-model-url="/api/holiday/admin/"
        :props-dialog="dialogDestroy"
        :props-selected-obj="selectedObj"
        @passByReference="copyByRefToTargetObj"
        @refresh="indexDatas"
        @close="close"
      ></data-destroy>

      <!-- 新規登録、編集のダイアログのレイアウト -->
      <holiday-profile
        :props-dialog="dialogProfile"
        :props-selected-obj="selectedObj"
        @passByReference="copyByRefToTargetObj"
        @refresh="indexDatas"
        @close="close"
      ></holiday-profile>

      <!-- アップロードダイアログのレイアウト -->
      <holiday-dialog
        :props-dialog="dialogUpload"
        :props-selected-obj="selectedObj"
        @passByReference="copyByRefToTargetObj"
        @refresh="indexDatas"
        @close="close"
      ></holiday-dialog>

      <!-- 有効無効ダイアログのレイアウト -->
      <data-enable-disable
        props-model-url="/api/holiday/admin/"
        props-model-name="Holiday"
        :props-dialog="dialogEnableDisable"
        :props-selected-obj="selectedObj"
        @passByReference="copyByRefToTargetObj"
        @close="close"
      ></data-enable-disable>

      <!-- 新規登録、編集のダイアログのレイアウト(Yes, No ボタン) -->
      <confirm-dialog
        ref="confirm"
        :props-dialog="confirmDialog"
        props-title="アップロードを実行しますか？"
        props-yes="はい"
        props-no="キャンセル"
        @returnConfirmDialog="returnConfirmDialog"
      ></confirm-dialog>

      <!-- Messageダイアログのレイアウト(OKボタン)  -->
      <message-dialog
        ref="message"
        :props-dialog="messageDialog"
        :props-title="messageTitle"
        @returnMessageDialog="returnMessageDialog"
      ></message-dialog>
    </div>
  </div>
</template>

<script>
import HolidayProfile from '~/components/dialogs/holiday/admin/HolidayProfile'
import DataEnableDisable from '~/components/dialogs/DataEnableDisable'
import DataDestroy from '~/components/dialogs/DataDestroy'
import ConfirmDialog from '~/components/dialogs/ConfirmDialog'
import MessageDialog from '~/components/dialogs/MessageDialog'
import HolidayDialog from '~/components/dialogs/HolidayDialog'

export default {
  middleware: 'auth',
  components: {
    HolidayProfile,
    DataEnableDisable,
    DataDestroy,
    ConfirmDialog,
    MessageDialog,
    HolidayDialog
  },
  data() {
    return {
      title: '休祝日設定',
      // データ一覧が入る配列
      dataList: [],
      // テーブルヘッダ
      headers: [
        { text: '休祝日名', align: 'center', value: 'summary' },
        { text: '年月日', align: 'center', value: 'holiday' },
        { text: '表示順', align: 'center', value: 'rank' },
        { text: '状態', align: 'center', value: 'is_enable' },
        { text: '操作', align: 'center', value: 'action', sortable: false }
      ],
      holiday_years: [],
      holiday_year: null,
      isEnableDatas: [
        { text: '有効', value: true },
        { text: '無効', value: false }
      ],

      totalCount: 1,
      tableLoading: true,
      options: {},

      dialogProfile: false,
      dialogEnableDisable: false,
      dialogDestroy: false,
      confirmDialog: false, // YesNoダイアログの表示フラグ
      messageDialog: false, // Messageダイアログの表示フラグ
      messageTitle: '',
      dialogUpload: false,

      // 選択中のオブジェクト
      selectedObj: {},
      search: '', // フィルタリング検索キーワード
      holiday_files: []
    }
  },
  watch: {
    options: {
      handler() {
        this.indexDatas()
      },
      deep: true
    },
    holiday_year(after_val, before_val) {
      this.indexDatas()
      // 前後数年のプルダウン内容セット
      this.holiday_years = this.getHolidayYears(after_val)
    },
    search(after_val, before_val) {
      this.indexDatas(after_val)
    }
  },
  mounted() {
    this.setInit()
    this.indexDatas()
  },

  methods: {
    setInit() {
      // 現在年取得
      var clickDate = new Date()
      if (!this.holiday_year) {
        this.holiday_year = this.calendarFormatYear(clickDate)
      }
      // 前後数年のプルダウン内容セット
      this.holiday_years = this.getHolidayYears(this.holiday_year)
    },

    // データ一覧
    async indexDatas(search = null) {
      console.log('indexDatas start')

      const { sortBy, sortDesc, page, itemsPerPage } = this.options
      const _itemsPerPage = itemsPerPage === '-1' ? -1 : itemsPerPage

      const options = {
        holiday_year: this.holiday_year,
        sortBy: sortBy[0],
        sortDesc: sortDesc[0],
        page: page,
        itemsPerPage: _itemsPerPage,
        search: search
      }
      this.tableLoading = true
      try {
        const res = await this.$axios.get('/api/holiday/admin', {
          params: options
        })
        this.dataList = res.data.data
        this.totalCount = res.data.total
        console.log('Index : record num=' + this.dataList.length)
      } catch (e) {
        console.log('Error : ' + e.response.data)
      }
      this.tableLoading = false
    },
    uploadClick() {
      this.confirmDialog = false
      // ファイルチェック
      var fileName
      if (this.holiday_files.length > 0) {
        fileName = this.holiday_files[0].name
      } else {
        this.messageTitle = 'ファイルを選択してください'
        this.messageDialog = true
        return false
      }

      // 拡張子チェック
      var type = fileName.split('.')
      if (type[type.length - 1].toLowerCase() != 'csv') {
        this.messageTitle = 'CSVファイルを選択してください'
        this.messageDialog = true
        return false
      }

      // アップロード処理実行
      this.uploadCSV()
    },
    // 一覧の最新化
    refresh() {
      console.log('Refresh')
      console.log('refleshed!!!!!')
      this.indexDatas()
    },
    // ダイアログのキャンセルボタン
    close() {
      this.dialogProfile = false
      this.dialogEnableDisable = false
      this.dialogDestroy = false
      this.confirmDialog = false
      this.messageDialog = false
      this.dialogUpload = false

      this.initialValidate()
      this.selectedObj = {}

      setTimeout(() => {
        // this.selected = Object.assign({}, this.defaultValue)
      }, 500)
    },
    async uploadCSV() {
      try {
        let options = new FormData()
        options.append('holiday_files', this.holiday_files[0])
        let config = {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
        await this.$axios.post('/api/holiday/admin/upload', options, config)
        this.messageTitle = 'CSVファイルの取り込みが完了しました'
        this.messageDialog = true
        this.refresh()
      } catch (e) {
        console.log('Error : ' + e.response.data)
        this.messageTitle = 'CSVファイルの取り込みに失敗しました'
        this.messageDialog = true
      }
    },
    // 確認ダイアログ戻り処理
    returnConfirmDialog(yes) {
      if (yes) {
        this.uploadClick()
      }
      this.confirmDialog = false
    },
    returnMessageDialog() {
      this.$refs.confirm.close()
      this.messageDialog = false
    }
  }
}
</script>
