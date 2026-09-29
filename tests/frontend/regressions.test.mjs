import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { test } from 'node:test'
import vm from 'node:vm'
import { compileScript, parse } from '@vue/compiler-sfc'
import { computed, nextTick, reactive, ref, watch } from 'vue'

// Compile the actual SFC scripts; mock only browser/Inertia/HTTP boundaries.
function component(path, overrides = {}) {
  const { descriptor } = parse(readFileSync(new URL(`../../resources/js/${path}`, import.meta.url), 'utf8'))
  const compiled = compileScript(descriptor, { id: path, genDefaultAs: '__component' })
  const source = compiled.content.replace(/^import\s+.+?\s+from\s+['"].+?['"];?\s*$/gm, '')
  return vm.runInNewContext(`${source}\n__component`, {
    Head: {}, Link: {}, AdminLayout: {}, ToastContainer: {},
    ref, computed, watch, onBeforeUnmount() {},
    route: (name, id) => `${name}/${id ?? ''}`,
    ...overrides,
  })
}

function formHarness() {
  let payload
  let options
  let method
  let url
  function useForm(data) {
    let transform = value => value
    const form = {
      ...data,
      processing: false,
      errors: {},
      transform(callback) { transform = callback; return form },
      setError(key, message) { form.errors[key] = message },
    }
    for (const verb of ['post', 'put', 'patch']) {
      form[verb] = (destination, config) => {
        // Serialize to remove VM prototypes and methods, like the HTTP boundary.
        payload = JSON.parse(JSON.stringify(transform(form)))
        options = config
        method = verb
        url = destination
      }
    }
    return form
  }
  return { useForm, get payload() { return payload }, get options() { return options }, get method() { return method }, get url() { return url } }
}

function setup(options, props) {
  return options.setup(props, { expose() {} })
}

test('post edit retains selected taxonomy IDs and uses method-spoofed multipart POST', () => {
  const http = formHarness()
  const state = setup(component('Pages/Dashboard/Posts/Edit.vue', { useForm: http.useForm }), {
    post: { id: 7, title: 'Existing post', body: 'Content', status: 'draft', selected_category_ids: [2, 5], selected_genre_ids: [9] },
    allCategories: [], allGenres: [],
  })
  state.submit()
  assert.deepEqual(http.payload.category_ids, [2, 5])
  assert.deepEqual(http.payload.genre_ids, [9])
  assert.equal(http.method, 'post')
  assert.equal(http.payload._method, 'put')
  assert.equal(http.options.forceFormData, true)
  state.form.category_ids = []
  state.form.genre_ids = []
  state.submit()
  assert.equal(http.payload.category_ids, null, 'empty selections must survive FormData serialization')
  assert.equal(http.payload.genre_ids, null)
})

test('book edit preserves link IDs and explicitly submits an empty link list', () => {
  const http = formHarness()
  const state = setup(component('Pages/Dashboard/Books/Edit.vue', { useForm: http.useForm, router: {} }), {
    book: { id: 4, title: 'Book', category_ids: [], genre_ids: [], links: [{ id: 12, label: 'Seller', url: 'https://example.com/book', is_primary: true }] },
    genres: [], categories: [],
  })
  state.submit()
  assert.equal(http.payload.links[0].id, 12)
  state.form.links = []
  state.submit()
  assert.equal(http.payload.links, null)
})

test('achievement and event edit forms read the controller prop names', () => {
  const achievement = setup(component('Pages/Dashboard/About/Achievements/Edit.vue', { useForm: formHarness().useForm }), {
    achievement: { id: 3, title: 'Award', year: 2026, description_md: 'Award details' },
  })
  assert.equal(achievement.form.title, 'Award')
  assert.equal(achievement.form.year, 2026)

  const http = formHarness()
  const event = setup(component('Pages/Dashboard/About/Events/Edit.vue', { useForm: http.useForm }), {
    event: { id: 8, headline: 'Book launch', happened_at: '2026-09-29', is_published: true },
  })
  assert.equal(event.form.headline, 'Book launch')
  assert.equal(event.form.happened_at, '2026-09-29')
  event.submit()
  assert.equal(http.method, 'post')
  assert.equal(http.payload._method, 'put')
  assert.equal(http.options.forceFormData, true)
})

function analyticsDocument() {
  const elements = []
  const document = {
    createElement: tag => ({ tag, style: {}, appendChild() {} }),
    head: { appendChild: element => elements.push(element) },
    body: { insertBefore: element => elements.push(element), firstChild: null },
  }
  return { document, elements }
}

test('persisted malicious analytics IDs cannot create executable scripts', () => {
  const dom = analyticsDocument()
  const layout = component('Layouts/AppLayout.vue', { document: dom.document, router: {} })
  for (const value of ["123');globalThis.compromised=true;//", '<script>alert(1)</script>', 'not-an-id']) {
    layout.methods.injectAnalytics.call({ seo: { facebook_pixel_id: value, google_analytics_id: value, google_tag_manager_id: value } })
  }
  assert.equal(dom.elements.length, 0)
})

test('valid analytics IDs still generate syntactically valid tracking scripts', () => {
  const dom = analyticsDocument()
  const layout = component('Layouts/AppLayout.vue', { document: dom.document, router: {} })
  layout.methods.injectAnalytics.call({ seo: { facebook_pixel_id: '123456789', google_analytics_id: 'G-ABC123', google_tag_manager_id: 'GTM-ABC123' } })
  const scripts = dom.elements.filter(element => element.tag === 'script')
  assert.equal(scripts.length, 4)
  for (const script of scripts) if (script.textContent) assert.doesNotThrow(() => new vm.Script(script.textContent))
  assert.ok(scripts.some(script => script.textContent?.includes('fbq(\'init\',"123456789")')))
})

function likeHarness({ user = { id: 1, email_verified_at: '2026-09-29' }, post = async () => ({ data: { liked: true, count: 6 } }) } = {}) {
  const visits = []
  const calls = []
  const props = reactive({ type: 'post', id: 4, initialCount: 5, initiallyLiked: false })
  const options = component('Components/LikeButton.vue', {
    usePage: () => ({ props: { auth: { user } } }),
    router: { visit: url => visits.push(url) },
    axios: { post: (...args) => { calls.push(args); return post(...args) } },
  })
  return { state: setup(options, props), props, visits, calls }
}

test('likes use the JSON response, not Inertia page props', async () => {
  const { state, calls, props } = likeHarness()
  await state.toggle()
  assert.equal(calls[0][0], '/likes/toggle')
  assert.equal(calls[0][2].headers.Accept, 'application/json')
  assert.equal(state.liked.value, true)
  assert.equal(state.count.value, 6)
  assert.equal(state.loading.value, false)
  props.initialCount = 8
  props.initiallyLiked = true
  await nextTick()
  assert.equal(state.count.value, 8)
})

test('guest and unverified likes navigate to authentication without a JSON request', async () => {
  for (const [user, url] of [[null, '/login'], [{ id: 1, email_verified_at: null }, '/verify-email']]) {
    const { state, visits, calls } = likeHarness({ user })
    await state.toggle()
    assert.deepEqual(visits, [url])
    assert.equal(calls.length, 0)
  }
})

test('failed likes retain the previous count and release loading state', async () => {
  const { state } = likeHarness({ post: async () => { throw new Error('Network unavailable') } })
  await state.toggle()
  assert.equal(state.count.value, 5)
  assert.equal(state.liked.value, false)
  assert.equal(state.loading.value, false)
  assert.ok(state.error.value)
})

test('rapid double clicks submit only one like request', async () => {
  let resolve
  const { state, calls } = likeHarness({ post: () => new Promise(done => { resolve = done }) })
  const request = state.toggle()
  await state.toggle()
  assert.equal(calls.length, 1)
  resolve({ data: { liked: true, count: 6 } })
  await request
  assert.equal(state.loading.value, false)
})

test('authors do not see admin-only navigation', () => {
  const layout = component('Layouts/AdminLayout.vue', { router: {} })
  const authorItems = layout.computed.nav.call({ user: { role: 'author' } }).flatMap(group => group.items.map(item => item.label))
  const adminItems = layout.computed.nav.call({ user: { role: 'admin' } }).flatMap(group => group.items.map(item => item.label))
  for (const item of ['Homepage', 'Contacts', 'Users']) {
    assert.equal(authorItems.includes(item), false)
    assert.equal(adminItems.includes(item), true)
  }
})
