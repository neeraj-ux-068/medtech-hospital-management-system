export type AppointmentStatus = 'Scheduled' | 'Confirmed' | 'Waiting' | 'Completed' | 'Cancelled'
export type Appointment = { id:string; patientId:string; doctorId:string; department:string; date:string; time:string; type:string; status:AppointmentStatus }
export const appointmentStatuses: AppointmentStatus[] = ['Scheduled','Confirmed','Waiting','Completed','Cancelled']
export const initialAppointments: Appointment[] = [
  {id:'AP-DEMO-001',patientId:'PT-2026-0841',doctorId:'DR-DEMO-001',department:'Cardiology',date:'2026-11-03',time:'09:00',type:'Consultation',status:'Confirmed'},
  {id:'AP-DEMO-002',patientId:'PT-2026-0840',doctorId:'DR-DEMO-002',department:'Neurology',date:'2026-11-03',time:'09:30',type:'Follow-up',status:'Waiting'},
  {id:'AP-DEMO-003',patientId:'PT-2026-0839',doctorId:'DR-DEMO-003',department:'Orthopedics',date:'2026-11-03',time:'10:00',type:'Consultation',status:'Scheduled'},
  {id:'AP-DEMO-004',patientId:'PT-2026-0838',doctorId:'DR-DEMO-004',department:'Pediatrics',date:'2026-11-04',time:'11:00',type:'Review',status:'Confirmed'},
  {id:'AP-DEMO-005',patientId:'PT-2026-0837',doctorId:'DR-DEMO-005',department:'General Medicine',date:'2026-11-05',time:'14:00',type:'Consultation',status:'Scheduled'},
  {id:'AP-DEMO-006',patientId:'PT-2026-0836',doctorId:'DR-DEMO-006',department:'Cardiology',date:'2026-11-10',time:'10:30',type:'Follow-up',status:'Completed'},
  {id:'AP-DEMO-007',patientId:'PT-2026-0835',doctorId:'DR-DEMO-007',department:'Neurology',date:'2026-11-17',time:'13:00',type:'Consultation',status:'Cancelled'},
  {id:'AP-DEMO-008',patientId:'PT-2026-0834',doctorId:'DR-DEMO-008',department:'Orthopedics',date:'2026-12-02',time:'09:00',type:'Review',status:'Scheduled'},
]
