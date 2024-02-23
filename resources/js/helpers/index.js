export function setAll(obj, val) {
    Object.keys(obj).forEach(key => obj[key] = val)
}
