import { useMemo, useState } from 'react'
import type { FormEvent } from 'react'
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom'
import { ArrowLeft, ArrowUpDown, ArrowUpRight, CheckCircle2, HeartPulse, Plus, Users, UserPlus } from 'lucide-react'
import { Button, EmptyState, LinkButton, PageFooter, PageHeader, Panel, StatusBadge, TableRegion } from '../components/ui'
import { age, departments, formatDate, initialPatients, initials, type Patient } from './patientData'
import Pagination from '../components/Pagination'
import { SearchField, SelectField } from '../components/FormControls'
import './PatientsModule.css'

const pageSize = 6
type SortKey = 'name' | 'registered' | 'department' | 'status'
const medicalHistory = ['Seasonal allergies (synthetic example)', 'Routine wellness review (synthetic example)']
const visitHistory = [{ date: '2026-11-02', department: 'General Medicine', note: 'Routine follow-up (synthetic example)' }, { date: '2026-08-14', department: 'Cardiology', note: 'Wellness consultation (synthetic example)' }]

export default function PatientsModule() {
  const [patients, setPatients] = useState(initialPatients)
  const [params, setParams] = useSearchParams()
  const [dialog, setDialog] = useState<'register' | 'edit' | 'deactivate' | null>(null)
  const [tab, setTab] = useState<'details' | 'medical' | 'visits'>('details')
  const [formError, setFormError] = useState('')
  const { patientId } = useParams()
  const navigate = useNavigate()
  const query = params.get('q') ?? ''
  const department = params.get('department') ?? ''
  const status = params.get('status') ?? ''
  const sort = (params.get('sort') ?? 'registered') as SortKey
  const direction = params.get('direction') === 'asc' ? 'asc' : 'desc'
  const requestedPage = Number(params.get('page'))
  const page = Number.isSafeInteger(requestedPage) && requestedPage > 0 ? requestedPage : 1
  const selected = patients.find(patient => patient.id === patientId)
  const directoryPath = `/patients${params.size ? `?${params}` : ''}`
  const profilePath = (id: string) => `/patients/${id}${params.size ? `?${params}` : ''}`
  function updateParams(values: Record<string, string>, replace = true) {
    setParams(previous => { const next = new URLSearchParams(previous); for (const [key, value] of Object.entries(values)) { if (value) next.set(key, value); else next.delete(key) } next.delete('page'); return next }, { replace })
  }
  const filtered = useMemo(() => patients.filter(patient =>
    (!department || patient.department === department) && (!status || patient.status === status) &&
    [patient.name, patient.id, patient.phone, patient.email].some(value => value.toLowerCase().includes(query.trim().toLowerCase())))
    .sort((a, b) => String(a[sort]).localeCompare(String(b[sort])) * (direction === 'asc' ? 1 : -1)), [patients, department, status, query, sort, direction])
  const pages = Math.max(1, Math.ceil(filtered.length / pageSize))
  const currentPage = Math.min(page, pages)
  function resetFilters() { setParams({}) }
  function toggleSort(key: SortKey) { updateParams({ sort: key, direction: sort === key && direction === 'asc' ? 'desc' : 'asc' }) }
  function savePatient(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    const data = new FormData(event.currentTarget)
    const name = String(data.get('name') ?? '').trim(), email = String(data.get('email') ?? '').trim(), phone = String(data.get('phone') ?? '').trim(), dob = String(data.get('dob') ?? '')
    if (name.length < 2 || !/^\S+@\S+\.\S+$/.test(email) || !/^[+\d()\s-]{7,20}$/.test(phone) || !dob || new Date(dob) >= new Date()) { setFormError('Enter a valid name, email, phone number and past date of birth.'); return }
    const current = selected
    const record: Patient = { id: current?.id ?? `PT-DEMO-${String(Date.now()).slice(-6)}`, name, email, phone, dob, gender: String(data.get('gender')), address: String(data.get('address') ?? '').trim(), bloodGroup: String(data.get('bloodGroup')), department: String(data.get('department')), status: current?.status ?? 'Active', registered: current?.registered ?? new Date().toISOString().slice(0, 10), lastVisit: current?.lastVisit ?? null, allergies: String(data.get('allergies') ?? '').trim() || 'None reported (demo)', emergencyContact: current?.emergencyContact ?? 'Demo contact · 000-000-0099' }
    setPatients(existing => current ? existing.map(patient => patient.id === current.id ? record : patient) : [record, ...existing]); setDialog(null); setFormError('')
    if (!current) navigate(profilePath(record.id))
  }
  function deactivate() { if (!selected) return; setPatients(existing => existing.map(patient => patient.id === selected.id ? { ...patient, status: 'Inactive' } : patient)); setDialog(null) }
  if (patientId && !selected) return <><PageHeader eyebrow="PATIENT SERVICES" title="Patient not found" description="This synthetic patient does not exist in the preview." actions={<LinkButton to={directoryPath}>Back to directory</LinkButton>} /><PageFooter /></>
  return <div className="patients-module">
    <PageHeader eyebrow="PATIENT SERVICES" title={selected ? 'Patient profile' : 'Patients management'} description={selected ? 'Synthetic patient details and care overview.' : 'Search and manage the synthetic patient directory.'}
      actions={selected ? <><Button onClick={() => setDialog('edit')}>Edit patient</Button>{selected.status === 'Active' && <Button onClick={() => setDialog('deactivate')}>Deactivate</Button>}<LinkButton className="secondary-button" to={directoryPath}><ArrowLeft size={16} />Back to directory</LinkButton></> : <Button variant="primary" onClick={() => { setFormError(''); setDialog('register') }}><Plus size={16} />Register patient</Button>} />
    <div className="patient-demo-note"><CheckCircle2 size={17} /><span>Synthetic demo only · Changes stay in this browser session and are not saved to hospital records.</span></div>
    {!selected && <>
      <section className="patient-stats" aria-label="Patient statistics">{[{ label: 'Total patients', value: patients.length, icon: Users, tone: 'blue' }, { label: 'Active patients', value: patients.filter(patient => patient.status === 'Active').length, icon: HeartPulse, tone: 'teal' }, { label: 'Awaiting first visit', value: patients.filter(patient => !patient.lastVisit).length, icon: UserPlus, tone: 'purple' }].map(({ label, value, icon: Icon, tone }) => <article className="stat-card" key={label}><div className={`stat-icon ${tone}`}><Icon size={21} /></div><div><p className="stat-label">{label}</p><strong className="stat-value">{value}</strong></div></article>)}</section>
      <Panel className="patient-directory" aria-labelledby="directory-title"><div className="panel-heading"><div><h2 id="directory-title">Patient directory</h2><p>Browse synthetic records and explore patient profiles</p></div><span className="department-tag">{patients.length} demo records</span></div>
        <div className="patient-filters"><SearchField className="patient-search" label="Search patients" placeholder="Name, ID, email or phone" value={query} onChange={event => updateParams({ q: event.target.value })} /><SelectField label="Department" value={department} onChange={event => updateParams({ department: event.target.value })}><option value="">All departments</option>{departments.map(item => <option key={item}>{item}</option>)}</SelectField><SelectField label="Status" value={status} onChange={event => updateParams({ status: event.target.value })}><option value="">All statuses</option><option>Active</option><option>Inactive</option></SelectField><Button onClick={resetFilters}>Reset filters</Button></div>
        <p className="patient-result-count" role="status">{filtered.length} {filtered.length === 1 ? 'patient' : 'patients'} found</p>
        {filtered.length ? <><TableRegion className="patient-table" label="Patient directory, scroll horizontally for more columns"><table><caption className="sr-only">Synthetic patient directory. Activate a column heading to sort.</caption><thead><tr>{[['name','Patient'],['registered','Registered'],['department','Department'],['status','Status']].map(([key,label]) => <th scope="col" key={key}><button className="sort-button" onClick={() => toggleSort(key as SortKey)}>{label}<ArrowUpDown size={13} aria-hidden="true" /></button></th>)}<th scope="col">Age / Gender</th><th scope="col">Last visit</th><th scope="col">Profile</th></tr></thead><tbody>{filtered.slice((currentPage - 1) * pageSize, currentPage * pageSize).map(patient => <tr key={patient.id}><td><div className="patient-cell"><div className="patient-avatar blue" aria-hidden="true">{initials(patient.name)}</div><div><Link className="patient-name" to={profilePath(patient.id)}>{patient.name}</Link><small>{patient.id}</small></div></div></td><td>{formatDate(patient.registered)}</td><td><span className="department-tag">{patient.department}</span></td><td><StatusBadge tone={patient.status === 'Active' ? 'green' : 'neutral'}>{patient.status}</StatusBadge></td><td>{age(patient.dob)} years / {patient.gender}</td><td>{patient.lastVisit ? formatDate(patient.lastVisit) : 'No visits yet'}</td><td><LinkButton className="text-link" to={profilePath(patient.id)} aria-label={`View profile for ${patient.name}`}>View <ArrowUpRight size={16} /></LinkButton></td></tr>)}</tbody></table></TableRegion><Pagination page={currentPage} pageSize={pageSize} total={filtered.length} onChange={value => setParams(previous => { const next = new URLSearchParams(previous); next.set('page', String(value)); return next })} label="Patient directory pages" /></> : <EmptyState title="No patients found" description="Try another name or clear your filters to see all patients."><Button onClick={resetFilters}>Clear search and filters</Button></EmptyState>}
      </Panel>
    </>}
    {selected && <><section className="panel patient-profile-header"><div className="patient-avatar blue" aria-hidden="true">{initials(selected.name)}</div><div><h2>{selected.name}</h2><p>{selected.id} · Registered {formatDate(selected.registered)}</p></div><StatusBadge tone={selected.status === 'Active' ? 'green' : 'neutral'}>{selected.status}</StatusBadge></section>
      <div className="profile-tabs" role="tablist" aria-label="Patient profile sections">{(['details','medical','visits'] as const).map(value => <button key={value} role="tab" aria-selected={tab === value} onClick={() => setTab(value)}>{value === 'details' ? 'Patient details' : value === 'medical' ? 'Medical history' : 'Visit history'}</button>)}</div>
      {tab === 'details' && <div className="patient-profile-grid"><section className="panel"><h2>Personal & contact details</h2><dl>{[['Date of birth', `${formatDate(selected.dob)} (${age(selected.dob)} years)`], ['Gender', selected.gender], ['Blood group', selected.bloodGroup], ['Email', selected.email], ['Phone', selected.phone], ['Address', selected.address || 'Not recorded'], ['Emergency contact', selected.emergencyContact]].map(([label,value]) => <div key={label}><dt>{label}</dt><dd>{value}</dd></div>)}</dl></section><section className="panel"><h2>Care overview</h2><dl>{[['Department', selected.department], ['Last visit', selected.lastVisit ? formatDate(selected.lastVisit) : 'No visits yet'], ['Reported allergies', selected.allergies]].map(([label,value]) => <div key={label}><dt>{label}</dt><dd>{value}</dd></div>)}</dl><div className="patient-care-note"><HeartPulse size={23} /><h3>Demonstration record</h3><p>All information is synthetic and provided for interface demonstration.</p></div></section></div>}
      {tab === 'medical' && <Panel><h2>Medical history</h2><p className="history-disclaimer">Synthetic examples only. No clinical record is connected.</p><ul className="history-list">{medicalHistory.map(item => <li key={item}><StatusBadge>{selected.allergies}</StatusBadge><span>{item}</span></li>)}</ul></Panel>}
      {tab === 'visits' && <Panel><h2>Visit history</h2><p className="history-disclaimer">Synthetic example visits for interface review.</p><TableRegion label="Synthetic visit history"><table><thead><tr><th>Date</th><th>Department</th><th>Visit note</th></tr></thead><tbody>{visitHistory.map(visit => <tr key={visit.date}><td>{formatDate(visit.date)}</td><td>{visit.department}</td><td>{visit.note}</td></tr>)}</tbody></table></TableRegion></Panel>}
    </>}
    <PageFooter label="MedTech HMS · Patient services" />
    {dialog && <div className="patient-dialog-backdrop"><section className="patient-dialog" role="dialog" aria-modal="true" aria-labelledby="patient-dialog-title"><h2 id="patient-dialog-title">{dialog === 'register' ? 'Register patient' : dialog === 'edit' ? 'Edit patient details' : 'Deactivate patient?'}</h2>{dialog === 'deactivate' ? <><p>Deactivate {selected?.name}? The synthetic record will remain in the directory with inactive status.</p><div className="patient-form-actions"><Button onClick={() => setDialog(null)}>Cancel</Button><Button variant="primary" onClick={deactivate}>Deactivate</Button></div></> : <form onSubmit={savePatient} noValidate><div className="patient-form-grid">{[['name','Full name','text',selected?.name ?? ''],['email','Email','email',selected?.email ?? ''],['phone','Phone','tel',selected?.phone ?? ''],['dob','Date of birth','date',selected?.dob ?? '']].map(([name,label,type,value]) => <label key={name}>{label}<input required name={name} type={type} defaultValue={value} /></label>)}<SelectField label="Gender" name="gender" defaultValue={selected?.gender ?? 'Female'}><option>Female</option><option>Male</option><option>Other</option><option>Prefer not to say</option></SelectField><SelectField label="Blood group" name="bloodGroup" defaultValue={selected?.bloodGroup ?? 'O+'}>{['O+','O-','A+','A-','B+','B-','AB+','AB-'].map(item => <option key={item}>{item}</option>)}</SelectField><SelectField label="Department" name="department" defaultValue={selected?.department ?? departments[0]}>{departments.map(item => <option key={item}>{item}</option>)}</SelectField><label>Address<input name="address" defaultValue={selected?.address ?? ''} /></label><label className="patient-wide">Allergies / notes<input name="allergies" defaultValue={selected?.allergies ?? ''} /></label></div>{formError && <p className="form-error" role="alert">{formError}</p>}<div className="patient-form-actions"><Button onClick={() => setDialog(null)}>Cancel</Button><Button variant="primary" type="submit">{dialog === 'register' ? 'Create synthetic patient' : 'Save changes'}</Button></div></form>}</section></div>}
  </div>
}
