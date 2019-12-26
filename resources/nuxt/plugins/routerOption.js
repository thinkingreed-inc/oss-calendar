import Vue from 'vue'

export default async ({ app, store }) => {
  // ページ遷移後に処理する
  app.router.afterEach((to, from) => {
    const params = store.getters['params/getParams']
    // 画面遷移前から遷移後のページに渡すパラメータ
    Vue.prototype.transferParams = params
    store.dispatch('params/clearParams')
  })

  // ページ遷移前に処理する
  app.router.beforeEach((to, from, next) => {
    Vue.prototype.transferParams = {}
    next()
  })
}
