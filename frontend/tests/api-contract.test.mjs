import test from 'node:test'
import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'

const spec = JSON.parse(readFileSync(new URL('../../docs/api/v1/openapi.json', import.meta.url), 'utf8'))

test('API design stays read-only with explicit role boundaries and response headers', () => {
  assert.equal(spec.openapi, '3.1.0')
  assert.equal(spec.servers[0].url, '/api/v1')
  for (const [path, resource] of Object.entries(spec.paths)) {
    assert.ok(!resource.post && !resource.put && !resource.patch && !resource.delete)
    assert.deepEqual(resource.get['x-allowed-roles'], path === '/auth/session' ? ['patient', 'doctor', 'admin'] : ['admin'])
    assert.deepEqual(spec.security, [{ phpSession: [] }])
    assert.equal(resource.get.responses['200'].headers['Cache-Control'].schema.const, 'no-store')
    assert.ok(resource.get.responses['401'] && resource.get.responses['403'])
    assert.ok(resource.head)
    assert.ok(!resource.head.responses['200'].content)
  }
})

test('OpenAPI local references resolve and patient fields exclude clinical/financial data', () => {
  function visit(value) {
    if (!value || typeof value !== 'object') return
    if ('$ref' in value) {
      assert.ok(value.$ref.startsWith('#/'))
      const resolved = value.$ref.slice(2).split('/').reduce((node, key) => node?.[key], spec)
      assert.ok(resolved, `Missing reference ${value.$ref}`)
    }
    Object.values(value).forEach(visit)
  }
  visit(spec)
  assert.deepEqual(Object.keys(spec.components.schemas.PatientDetail.properties).sort(),
    ['address', 'city', 'email', 'fullName', 'gender', 'id', 'registeredAt'])
  const params = spec.paths['/patients'].parameters
  assert.ok(!params.some(parameter => ['q', 'email', 'phone', 'department', 'status'].includes(parameter.name)))
  assert.equal(params.find(parameter => parameter.name === 'pageSize').schema.maximum, 100)
})
