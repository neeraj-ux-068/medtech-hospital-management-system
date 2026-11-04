export const departments = ['Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics', 'General Medicine'] as const
export type Patient = {
  id: string
  name: string
  dob: string
  gender: string
  email: string
  phone: string
  address: string
  bloodGroup: string
  department: string
  status: 'Active' | 'Inactive'
  registered: string
  lastVisit: string | null
  allergies: string
  emergencyContact: string
}

// Entirely fictional fixtures. Never read from the PHP application or database.
const names = ['Aarav Sharma', 'Ananya Patel', 'Rohan Kumar', 'Diya Reddy', 'Kabir Shah', 'Meera Joshi', 'Arjun Nair', 'Isha Rao', 'Vivaan Das', 'Saanvi Mehta', 'Aditya Sen', 'Tara Kapoor', 'Dev Malhotra', 'Nisha Verma', 'Reyansh Bose', 'Avni Iyer', 'Vihaan Sethi', 'Kavya Menon']
export const initialPatients: Patient[] = names.map((name, index) => ({
  id: `PT-2026-${String(841 - index).padStart(4, '0')}`,
  name,
  dob: `${1980 + index * 2}-0${index % 9 + 1}-15`,
  gender: index % 2 ? 'Female' : 'Male',
  email: `${name.toLowerCase().replace(' ', '.')}@example.test`,
  phone: `000-000-${String(index + 1).padStart(4, '0')}`,
  address: `${index + 1} Example Lane, Demo City`,
  bloodGroup: ['O+', 'A+', 'B+', 'AB+', 'O-'][index % 5],
  department: departments[index % departments.length],
  status: index % 5 === 4 ? 'Inactive' : 'Active',
  registered: `2026-10-${String(28 - index).padStart(2, '0')}`,
  lastVisit: index % 4 === 0 ? null : `2026-11-0${index % 2 + 1}`,
  allergies: index % 3 === 0 ? 'Penicillin (synthetic example)' : 'None reported (demo)',
  emergencyContact: 'Demo contact • 000-000-0099',
}))

export const initials = (name: string) => name.split(' ').filter(Boolean).map(part => part[0]).slice(0, 2).join('').toUpperCase()
export const formatDate = (value: string) => new Intl.DateTimeFormat('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(`${value}T00:00:00`))
export function age(dob: string) {
  const today = new Date()
  const birth = new Date(`${dob}T00:00:00`)
  return today.getFullYear() - birth.getFullYear() - (today.getMonth() < birth.getMonth() || (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate()) ? 1 : 0)
}
