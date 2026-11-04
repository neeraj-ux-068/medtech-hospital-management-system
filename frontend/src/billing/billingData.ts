export type InvoiceStatus='Paid'|'Pending'|'Partially Paid'|'Overdue'|'Draft'|'Cancelled'
export type InvoiceItem={description:string;quantity:number;unitPrice:number;taxRate:number}
export type Payment={id:string;date:string;amount:number;method:string;reference:string}
export type Invoice={id:string;patientId:string;appointmentId:string|null;date:string;dueDate:string;items:InvoiceItem[];payments:Payment[];status:InvoiceStatus}
export const toPaise=(rupees:number)=>Math.round(rupees*100)
export const lineTotalPaise=(item:InvoiceItem)=>toPaise(item.quantity*item.unitPrice)+Math.round(toPaise(item.quantity*item.unitPrice)*item.taxRate/100)
export const invoiceTotalPaise=(items:InvoiceItem[])=>items.reduce((sum,item)=>sum+lineTotalPaise(item),0)
export const paidPaise=(payments:Payment[])=>payments.reduce((sum,payment)=>sum+toPaise(payment.amount),0)
export const outstandingPaise=(invoice:Invoice)=>Math.max(0,invoiceTotalPaise(invoice.items)-paidPaise(invoice.payments))
export function invoiceStatus(invoice:Invoice,today=new Date().toISOString().slice(0,10)):InvoiceStatus {if(invoice.status==='Draft'||invoice.status==='Cancelled')return invoice.status;const due=outstandingPaise(invoice);if(!due)return 'Paid';if(invoice.dueDate<today)return 'Overdue';return paidPaise(invoice.payments)>0?'Partially Paid':'Pending'}
export const initialInvoices:Invoice[]=[
 {id:'INV-DEMO-1001',patientId:'PT-2026-0841',appointmentId:'AP-DEMO-001',date:'2026-11-03',dueDate:'2026-11-17',items:[{description:'Cardiology consultation',quantity:1,unitPrice:1800,taxRate:5},{description:'ECG service',quantity:1,unitPrice:650,taxRate:5}],payments:[{id:'PAY-DEMO-01',date:'2026-11-03',amount:1000,method:'Mock card',reference:'DEMO-REF-1001'}],status:'Partially Paid'},
 {id:'INV-DEMO-1002',patientId:'PT-2026-0840',appointmentId:'AP-DEMO-002',date:'2026-10-15',dueDate:'2026-10-29',items:[{description:'Neurology follow-up',quantity:1,unitPrice:1500,taxRate:5}],payments:[],status:'Overdue'},
 {id:'INV-DEMO-1003',patientId:'PT-2026-0839',appointmentId:'AP-DEMO-003',date:'2026-11-02',dueDate:'2026-11-16',items:[{description:'Orthopedic consultation',quantity:1,unitPrice:2000,taxRate:5}],payments:[{id:'PAY-DEMO-02',date:'2026-11-02',amount:2100,method:'Mock UPI',reference:'DEMO-REF-1003'}],status:'Paid'},
 {id:'INV-DEMO-1004',patientId:'PT-2026-0838',appointmentId:'AP-DEMO-004',date:'2026-11-03',dueDate:'2026-11-17',items:[{description:'Paediatric consultation',quantity:1,unitPrice:1200,taxRate:5}],payments:[],status:'Pending'},
 {id:'INV-DEMO-1005',patientId:'PT-2026-0837',appointmentId:'AP-DEMO-005',date:'2026-11-03',dueDate:'2026-11-17',items:[{description:'General consultation',quantity:1,unitPrice:1000,taxRate:5},{description:'Laboratory service',quantity:2,unitPrice:400,taxRate:5}],payments:[],status:'Draft'},
]
