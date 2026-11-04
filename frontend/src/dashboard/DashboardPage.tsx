import { useMemo } from 'react'
import { useOutletContext } from 'react-router-dom'
import { ArrowUpRight, CalendarDays, FileText, MoreHorizontal, Plus, Stethoscope, Users, Wallet, ChevronRight } from 'lucide-react'
import { LinkButton, PageFooter, PageHeader, StatusBadge, TableRegion } from '../components/ui'

const stats = [
  {
    label: 'Total Patients',
    value: '2,845',
    change: '+12.8%',
    description: 'Compared to last month',
    icon: Users,
    tone: 'blue',
    trend: 'up',
  },
  {
    label: 'Total Doctors',
    value: '48',
    change: '+4.2%',
    description: 'Compared to last month',
    icon: Stethoscope,
    tone: 'teal',
    trend: 'up',
  },
  {
    label: "Today's Appointments",
    value: '126',
    change: '+8.4%',
    description: 'Compared to yesterday',
    icon: CalendarDays,
    tone: 'purple',
    trend: 'up',
  },
  {
    label: 'Total Revenue',
    value: '₹8.42L',
    change: '+10.6%',
    description: 'Compared to last month',
    icon: Wallet,
    tone: 'orange',
    trend: 'up',
  },
]

const appointments = [
  { name: 'Aarav Sharma', initials: 'AS', doctor: 'Dr. Priya Reddy', department: 'Cardiology', time: '09:00 AM', status: 'Confirmed', tone: 'green' },
  { name: 'Ananya Patel', initials: 'AP', doctor: 'Dr. Rahul Mehta', department: 'Neurology', time: '09:30 AM', status: 'Pending', tone: 'yellow' },
  { name: 'Rohan Kumar', initials: 'RK', doctor: 'Dr. Sneha Rao', department: 'Orthopedics', time: '10:00 AM', status: 'Confirmed', tone: 'green' },
  { name: 'Diya Reddy', initials: 'DR', doctor: 'Dr. Arjun Das', department: 'Pediatrics', time: '10:30 AM', status: 'Cancelled', tone: 'red' },
]

const patients = [
  { name: 'Aarav Sharma', id: 'PT-2026-0841', date: 'Today, 09:12 AM', type: 'Cardiology', initials: 'AS', tone: 'blue' },
  { name: 'Ananya Patel', id: 'PT-2026-0840', date: 'Today, 08:45 AM', type: 'Neurology', initials: 'AP', tone: 'pink' },
  { name: 'Rohan Kumar', id: 'PT-2026-0839', date: 'Yesterday, 04:30 PM', type: 'Orthopedics', initials: 'RK', tone: 'green' },
]

const chartValues = [34, 48, 40, 63, 51, 72, 58, 78, 66, 88, 73, 95]
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

export default function DashboardPage() {
  const { search } = useOutletContext<{ search: string }>()
  const filteredAppointments = useMemo(() => {
    const query = search.trim().toLowerCase()
    return appointments.filter(appointment => [appointment.name, appointment.doctor, appointment.department, appointment.status]
      .some(value => value.toLowerCase().includes(query)))
  }, [search])
  return <>
    <PageHeader eyebrow="HOSPITAL OVERVIEW" title="Hospital dashboard" description="Illustrative hospital activity using synthetic data only."
      actions={<><LinkButton className="secondary-button" to="/reports"><FileText size={16} />Reports</LinkButton><LinkButton to="/appointments"><Plus size={17} />Appointments</LinkButton></>} />
              <section className="stats-grid" aria-label="Hospital statistics">
                {stats.map(({ label, value, change, description, icon: Icon, tone, trend }) => (
                  <article className="stat-card" key={label}>
                    <div className="stat-top">
                      <div className={`stat-icon ${tone}`}><Icon size={20} /></div>
                      <MoreHorizontal size={19} aria-hidden="true" />
                    </div>
                    <p className="stat-label">{label}</p>
                    <div className="stat-value">{value}</div>
                    <div className="stat-footer">
                      <span className={`trend ${trend}`}><ArrowUpRight size={14} /> {change}</span>
                      <span>{description}</span>
                    </div>
                  </article>
                ))}
              </section>

              <section className="content-grid">
                <article className="panel revenue-panel">
                  <div className="panel-heading">
                    <div><h2>Hospital overview</h2><p>Revenue performance throughout the year</p></div>
                    <span className="department-tag">Illustrative year</span>
                  </div>
                  <div className="revenue-summary">
                    <strong>₹8,42,500</strong>
                    <span className="trend up"><ArrowUpRight size={14} /> 10.6%</span>
                    <small>vs. previous period</small>
                  </div>
                  <div className="chart-area">
                    <div className="chart-y-labels"><span>10L</span><span>7.5L</span><span>5L</span><span>2.5L</span><span>0</span></div>
                    <div className="chart-main">
                      <div className="chart-grid-lines"><i /><i /><i /><i /><i /></div>
                      <div className="chart-bars">
                        {chartValues.map((height, index) => (
                          <div className="chart-column" key={months[index]}>
                            <div className="chart-bar-track"><div className={`chart-bar ${index === 10 ? 'highlight-bar' : ''}`} style={{ height: `${height}%` }} /></div>
                            <span>{months[index]}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                  <div className="chart-legend"><span className="legend-dot" /> Monthly revenue <span className="chart-note">Illustrative data</span></div>
                </article>

                <article className="panel schedule-panel">
                  <div className="panel-heading">
                    <div><h2>Today's schedule</h2><p>Your appointment overview</p></div>
                    <LinkButton className="text-link" to="/appointments">View all <ChevronRight size={15} /></LinkButton>
                  </div>
                  <div className="schedule-date"><CalendarDays size={16} /><strong>Monday, 02 November</strong><span>12 appointments</span></div>
                  {!filteredAppointments.length && <p role="status">No demo appointments match your search.</p>}
                  <div className="schedule-list">
                    {filteredAppointments.slice(0, 4).map((appointment) => (
                      <div className="schedule-item" key={appointment.name}>
                        <div className="schedule-time">{appointment.time}</div>
                        <div className={`schedule-line ${appointment.tone}`} />
                        <div className="schedule-detail">
                          <strong>{appointment.name}</strong>
                          <span>{appointment.doctor} · {appointment.department}</span>
                        </div>
                        <StatusBadge tone={appointment.tone}>{appointment.status}</StatusBadge>
                      </div>
                    ))}
                  </div>
                  <LinkButton className="schedule-footer" to="/appointments">Open appointment calendar <ArrowUpRight size={15} /></LinkButton>
                </article>
              </section>

              <section className="panel patients-panel">
                <div className="panel-heading">
                  <div><h2>Recent patients</h2><p>Latest patient registrations and visits</p></div>
                  <LinkButton className="text-link" to="/patients">View all patients <ChevronRight size={15} /></LinkButton>
                </div>
                <TableRegion label="Recent synthetic patients, scroll horizontally for more columns">
                  <table>
                    <caption className="sr-only">Recent synthetic patients</caption><thead><tr><th scope="col">Patient</th><th scope="col">Patient ID</th><th scope="col">Registration</th><th scope="col">Department</th><th scope="col">Action</th></tr></thead>
                    <tbody>
                      {patients.map((patient) => (
                        <tr key={patient.id}>
                          <td><div className="patient-cell"><div className={`patient-avatar ${patient.tone}`}>{patient.initials}</div><strong>{patient.name}</strong></div></td>
                          <td className="patient-id">{patient.id}</td>
                          <td>{patient.date}</td>
                          <td><span className="department-tag">{patient.type}</span></td>
                          <td><LinkButton className="row-action" aria-label={`View ${patient.name}`} to={`/patients/${patient.id}`}><MoreHorizontal size={19} /></LinkButton></td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </TableRegion>
              </section>

              <PageFooter label="MedTech HMS · Dashboard" />

  </>
}
