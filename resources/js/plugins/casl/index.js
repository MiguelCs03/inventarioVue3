import { createMongoAbility } from '@casl/ability'
import { abilitiesPlugin } from '@casl/vue'

export default function (app) {
  try {
    // Read raw cookie value and ensure we pass a plain JS array to createMongoAbility
    const rawRules = useCookie('userAbilityRules').value
    const rules = Array.isArray(rawRules) ? rawRules : (rawRules ? rawRules : [])

    const initialAbility = createMongoAbility(rules)

    app.use(abilitiesPlugin, initialAbility, {
      useGlobalProperties: true,
    })
  } catch (err) {
    // Fail gracefully - register plugin without initial ability if something goes wrong
    console.error('[casl] Error initializing abilities, falling back to empty rules', err)
    const initialAbility = createMongoAbility([])
    app.use(abilitiesPlugin, initialAbility, { useGlobalProperties: true })
  }
}
