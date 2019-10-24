
export interface IDownloadDoc {
  name: string;
  file_name_key: string;
  download_link: string;
}

export interface IDownloadDocs {
  docs: Array<IDownloadDoc>;
}

export interface IUploadDoc {
 name: string;
 file_name_key: string;
 doc_comment: string;
 doc_status: string;
 is_required: boolean;
 file_name: string;
 file_type: string;
 dbid: number;
}
export interface IUploadDocs {
  docs: Array<IUploadDoc>;
}

export interface IUploadedDoc {
  name: string;
  file_name_key: string;
  doc_comment: string;
  doc_status: string;
  is_required: boolean;
  file_name: string;
  file_type: string;
  dbid: number;
}

export interface IUploadedDocs {
  docs: Array<IUploadedDoc>;
}

export interface IremoveAnnualDoc {
  companyId: string;
  fileTypeId: number;
}


/************** */

export interface IshareholderItem {
  id: number;
  name: string;
}
export interface IshareholderItems {
  sh: Array<IshareholderItem>;
}

export interface ICallShare {
  id?: number;
  shareholder_id: string;
  shareholder_type: string;
  share_prior_to_this_call?: string;
  value_respect_of_share?: string;
  name_of_shares?: string;
  value_respect_of_total_share?: string;
  showEditPane: number;

}
export interface ICallShares {
  share: Array<ICallShare>;
}

export interface ISubmitCallRecords {
  companyId: string;
  call_records: ICallShares;
  action?: string;
  stated_capital?: string;
  total_amount_of_call?: string;
  signing_party_designation?: string;
  singning_party_name?: string;
}

export interface IremoveCallShareDoc {
  companyId: string;
  fileTypeId: number;
}
export interface Iresubmit {
  companyId: string;
}

/**************** */
export interface IRegisterChargeRecord {
  id?: number;
  shareholder_name_list?: string;
  showEditPane: number;

}
export interface IRegisterChargeRecords {
  record: Array<IRegisterChargeRecord>;
}

export interface ISubmitRegisterOfChargesRecords {
  companyId: string;
  register_charges: IRegisterChargeRecords;
  action?: string;
  date_of_registration?: string;
  document_serial_no?: string;
  date_of_creation_of_charge?: string;
  date_of_acquisition_of_property?: string;
  amount_secured_by_charge?: string;
  short_particulars_of_charge?: string;
  person_name_entitled?: string;
}

/**************** */


export interface IChargeTypes {
  id: number;
  setting_type_id ?: number;
  key: string;
  value: string;
  value_si ?: string;
  value_ta ?: string;
  sort ?: number;
}

/************************* */

export interface INotice {
  id?: number;
  shareholder_name_list?: string;

}

/************************* */

export interface ISubmitAnnualAccount {
  companyId: string;
  annual: IAnnualAccount;
  loginUser: string;
}


export interface IAnnualAccount {
  id?: number;
  balance_sheet_date?: string;
  financial_year1?: string;
  financial_year2?: string;
  type?: string;
  amount?: string;
  no_of_employees?: string;

}

export interface ISignedStakeholder {
  id: number;
  name: string;
}

export interface IRemoveOtherDoc {
  file_token: string;
}

export interface ICheckPreviousFinancialYearRecord {
  company_id: string;
  submit_year: string;
}

export interface ICompleteForPrivate {
  company_id: string;
}
