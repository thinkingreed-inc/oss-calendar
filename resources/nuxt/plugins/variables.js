export default ({ app }, inject) => {
  let calendar = null
  const setCalendar = cal => {
    calendar = cal
  }
  const getCalendar = () => {
    return calendar
  }
  inject('setCalendar', cal => {
    setCalendar(cal)
  })
  inject('getCalendar', () => {
    return getCalendar()
  })

  // モバイル判定をwidthで行う
  let mobileFlag = null
  const isMobile = () => {
    const w = window.innerWidth
    mobileFlag = w > 700 ? false : true
    return mobileFlag
  }
  inject('isMobile', () => {
    return isMobile()
  })
}
