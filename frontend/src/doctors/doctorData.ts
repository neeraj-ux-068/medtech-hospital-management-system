export const doctorDepartments = ['Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics', 'General Medicine'] as const
export const specializations = ['Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics', 'Internal Medicine', 'Dermatology'] as const
export type Doctor = {
  id: string; name: string; specialization: string; department: string; email: string; phone: string
  qualifications: string; experience: number; availability: 'Available' | 'Limited' | 'Unavailable';
  schedule: string[]; appointmentsToday: number; status: 'Active' | 'Inactive'
}
const names = ['Dr. Priya Reddy', 'Dr. Rahul Mehta', 'Dr. Sneha Rao', 'Dr. Arjun Das', 'Dr. Kavita Nair', 'Dr. Vikram Shah', 'Dr. Neha Iyer', 'Dr. Amit Bose', 'Dr. Maya Sen', 'Dr. Kiran Joshi', 'Dr. Anil Verma', 'Dr. Tara Menon']
export const initialDoctors: Doctor[] = names.map((name, index) => ({
  id: `DR-DEMO-${String(index + 1).padStart(3, '0')}`, name,
  specialization: specializations[index % specializations.length], department: doctorDepartments[index % doctorDepartments.length],
  email: `doctor${index + 1}@example.test`, phone: `000-100-${String(index + 1).padStart(4, '0')}`,
  qualifications: ['MBBS, MD', 'MBBS, MS', 'MBBS, DNB'][index % 3], experience: 5 + index * 2,
  availability: (['Available', 'Limited', 'Available', 'Unavailable'] as const)[index % 4],
  schedule: ['Monday · 09:00–17:00', 'Tuesday · 09:00–17:00', 'Wednesday · 09:00–17:00', 'Thursday · 09:00–17:00', 'Friday · 09:00–15:00'],
  appointmentsToday: 3 + index % 9, status: index === 11 ? 'Inactive' : 'Active',
}))
