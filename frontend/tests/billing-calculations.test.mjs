import test from 'node:test'
import assert from 'node:assert/strict'
import { invoiceTotalPaise, paidPaise, outstandingPaise, invoiceStatus, initialInvoices } from '../src/billing/billingData.ts'

test('invoice totals apply line taxes with paise rounding and payments reduce balance', () => {
  const invoice = initialInvoices[0]
  assert.equal(invoiceTotalPaise(invoice.items), 257250)
  assert.equal(paidPaise(invoice.payments), 100000)
  assert.equal(outstandingPaise(invoice), 157250)
  assert.equal(invoiceStatus(invoice, '2026-11-03'), 'Partially Paid')
})

test('fully covered invoice is paid and expired balance is overdue', () => {
  assert.equal(invoiceStatus(initialInvoices[2], '2026-11-03'), 'Paid')
  assert.equal(invoiceStatus(initialInvoices[1], '2026-11-03'), 'Overdue')
})
