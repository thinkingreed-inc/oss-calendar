export default ({ app }, inject) => {
  const ADMIN = 1
  const USER = 2
  const acl = {
    /*
    article: {
      list: (user) => {
        if (!user) return false
        return true
      },
      create: (user) => {
        if (!user) return false
        if (user.role_id === USER) return false
        return true
      },
      edit: (user, article) => {
        if (!user) return false
        if (user.role_id === ADMIN) return true
        if (user.role_id === USER && user.id === article.user_id) return true
        return false
      }
    },
    */
    adminMenu: {
      show: user => {
        if (!user) return false
        if (user.role_id === USER) return false
        if (user.role_id === ADMIN) return true
        return false
      }
    }
  }
  const can = (user, verb, subject, ...args) => {
    return acl[subject][verb](user, ...args)
  }
  inject('can', (verb, subject, ...args) => {
    const user = app.store.state.auth.user
    return can(user, verb, subject, ...args)
  })
}
