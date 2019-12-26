/**
 * 状態を保持したい変数の管理
 * Mutations以外から更新されることは無い。
 * Stateを取得するためにはgettersを使う。
 */
export const state = () => ({
  title: 'OSSカレンダー',
  drawer: false,
  fixed: true,
  miniVariant: false,
  csrfToken: ''
})

// 同期処理
/**
 * mutationsは値の移り変わりの処理を実装
 * stateを更新するにはMutationsからcommitをする。
 * Mutationsは同期処理でなければならない。
 */
export const mutations = {
  toggleDrawer(state) {
    state.drawer = !state.drawer
  },
  toggleFixed(state) {
    console.log(state.fixed)
    state.fixed = !state.fixed
    console.log(state.fixed)
  },
  toggleMiniVariant(state) {
    state.miniVariant = !state.miniVariant
  },
  setDrawer(state, val) {
    state.drawer = val
  }
}

// 非同期処理
/**
 * actionsはmutationsを利用して，アクションの処理を実装
 * データの加工や非同期処理はActionsで行い、Mutationsからcommitして更新をする。
 * Actionsの呼び出しにはdispatchをする。
 */
export const actions = {}

/**
 * gettersはstateの値を取得するのに利用
 */
export const getters = {
  getTitle(state) {
    return state.title
  },
  getDrawer(state) {
    return state.drawer
  },
  getFixed(state) {
    return state.fixed
  },
  getMiniVariant(state) {
    return state.miniVariant
  }
}
