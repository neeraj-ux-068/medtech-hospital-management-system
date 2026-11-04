import test from 'node:test'
import assert from 'node:assert/strict'
import { initialPatients } from '../src/patients/patientData.ts'
import { initialDoctors } from '../src/doctors/doctorData.ts'
import { initialAppointments } from '../src/appointments/appointmentData.ts'
import { initialInvoices } from '../src/billing/billingData.ts'
import { initialDepartments } from '../src/departments/departmentData.ts'

test('synthetic appointments and invoices reference existing fixture records', () => {
  const patients = new Set(initialPatients.map(patient => patient.id))
  const doctors = new Set(initialDoctors.map(doctor => doctor.id))
  const appointments = new Set(initialAppointments.map(appointment => appointment.id))
  for (const appointment of initialAppointments) {
    assert.ok(patients.has(appointment.patientId), `Unknown synthetic patient ${appointment.patientId}`)
    assert.ok(doctors.has(appointment.doctorId), `Unknown synthetic doctor ${appointment.doctorId}`)
  }
  for (const invoice of initialInvoices) {
    assert.ok(patients.has(invoice.patientId), `Unknown synthetic patient ${invoice.patientId}`)
    if (invoice.appointmentId) assert.ok(appointments.has(invoice.appointmentId), `Unknown synthetic appointment ${invoice.appointmentId}`)
  }
})

test('synthetic departments resolve assigned heads to doctors in the same department', () => {
  for (const department of initialDepartments) {
    const head = initialDoctors.find(doctor => doctor.id === department.headId)
    assert.ok(head, `Unknown synthetic department head ${department.headId}`)
    assert.equal(head.department, department.name)
  }
})
