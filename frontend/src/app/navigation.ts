import { Activity, CalendarDays, CreditCard, FileText, LayoutDashboard, Stethoscope, Users } from 'lucide-react'
import { matchPath } from 'react-router-dom'

export const navigation = [
  { label: 'Dashboard', path: '/dashboard', icon: LayoutDashboard },
  { label: 'Patients', path: '/patients', icon: Users },
  { label: 'Doctors', path: '/doctors', icon: Stethoscope },
  { label: 'Appointments', path: '/appointments', icon: CalendarDays },
  { label: 'Departments', path: '/departments', icon: Activity },
  { label: 'Billing', path: '/billing', icon: CreditCard },
  { label: 'Reports', path: '/reports', icon: FileText },
]

export function pageTitle(pathname: string): string {
  if (matchPath('/patients/:patientId', pathname)) return 'Patients'
  if (matchPath('/settings', pathname)) return 'Settings'
  return navigation.find(item => matchPath(item.path, pathname))?.label ?? 'Page not found'
}
