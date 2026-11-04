import { Navigate, Route, Routes } from 'react-router-dom'
import AppLayout from './app/AppLayout'
import DashboardPage from './dashboard/DashboardPage'
import PatientsModule from './patients/PatientsModule'
import DoctorsModule from './doctors/DoctorsModule'
import AppointmentsModule from './appointments/AppointmentsModule'
import DepartmentsModule from './departments/DepartmentsModule'
import BillingModule from './billing/BillingModule'
import ReportsModule from './reports/ReportsModule'
import SettingsModule from './settings/SettingsModule'
import PlaceholderPage from './components/PlaceholderPage'
import './App.css'

export default function App() {
  return <Routes>
    <Route element={<AppLayout />}>
      <Route index element={<Navigate to="/dashboard" replace />} />
      <Route path="dashboard" element={<DashboardPage />} />
      <Route path="patients" element={<PatientsModule />} />
      <Route path="patients/:patientId" element={<PatientsModule />} />
      <Route path="doctors" element={<DoctorsModule />} />
      <Route path="appointments" element={<AppointmentsModule />} />
      <Route path="departments" element={<DepartmentsModule />} />
      <Route path="billing" element={<BillingModule />} />
      <Route path="reports" element={<ReportsModule />} />
      <Route path="settings" element={<SettingsModule />} />
      <Route path="*" element={<PlaceholderPage title="Page not found" description="This address is not part of the MedTech preview. Use the navigation or return to the dashboard." />} />
    </Route>
  </Routes>
}
