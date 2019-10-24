import { IAddress } from './register.model';
export interface IComdata {
  companies: INames;
  namechangedata: Array<INameChange>;
  has: boolean;
}

export interface INames {
  id: string;
  name: string;
  name_si: string;
  name_ta: string;
  type_id: string;
  postfix: string;
  abbreviation_desc: string;
  registration_no: string;
  email: string;
  is_name_change: number;
  created_at: string;
  incorporation_at: string;
  name_resavation_at: string;
  updated_at: string;
  name_renew_at: string;
  status: string;
  address1: string;
  address2: string;
  city: string;
  district: string;
  province: string;
  country: string;
  postCode: number;
  key: string;
  typeKey?: string;
  color: string;
  this_user_company: string;
  documents: Array<IDocuments>;
}

export interface IDocuments {
  company_id: string;
  created_at: string;
  document_id: string;
  file_token: string;
  id: string;
  no_of_pages: string;
  updated_at: string;
  document_group_id: string;
  name: string;
  description: string;
}

export interface INameChange {
  id: string;
  changeid: string;
  name: string;
  name_si: string;
  name_ta: string;
  type_id: string;
  postfix: string;
  abbreviation_desc: string;
  email: string;
  is_name_change: string;
  resolution_dates: string;
  resolution_date: string;
  created_at: string;
  updated_at: string;
  name_renew_at: string;
  name_resavation_at: string;
  status: string;
  key: string;
}


export interface IStatusCount {
  all: number;
  inProgress: number;
  resubmit: number;
  pending: number;
  approval: number;
  rejected: number;
  canceled: number;
  inpending: number;
  inapproval: number;
  inrejected: number;
}


export interface IReSubmit {
  refId: number;
  companyName: string;
  sinhalaName: string;
  tamileName: string;
  abbreviation_desc: string;
}

export interface IReSubmit1 {
  refId: number;
  companyName: string;
  sinhalaName: string;
  tamileName: string;
  abbreviation_desc: string;
  adsinhalaName: string;
  adtamileName: string;
  address: string;
  needApproval: boolean;
}


export interface ICompanyCommentWith {
  name: string;
  comments: string;
  updated_at: string;
}

export interface IUploadOtherDocs {
  docs: Array<IUploadedOtherDoc>;
}

export interface IUploadedOtherDoc {
  name: string;
  file_name_key: string;
  doc_comment: string;
  doc_status: string;
  is_required: boolean;
  file_name: string;
  file_type: string;
  dbid: number;
}

export interface IRemoveOtherDoc {
  file_token: string;
}
export interface ICompanyReq {
  company_id: string;
  type?: string;
  request_id?: number;
}

