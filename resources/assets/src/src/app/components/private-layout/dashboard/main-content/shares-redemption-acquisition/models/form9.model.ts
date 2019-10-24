
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

/********************** */
export interface IForm9Record {
  id?: number;
  shareholder_id?: string;
  person_name?: string;
  aquire_or_redeemed?: string;
  norm_type?: string;
  person_type?: string;
  nic?: string;
  passno?: string;
  regno?: string;
  other_share_class?: string;
  aquire_or_redeemed_value?: string;
  date?: string;
  share_class?: string;
  showEditPane: number;

}
export interface IForm9Records {
  rec: Array<IForm9Record>;
}

export interface IRemoveOtherDoc {
  file_token: string;
}

export interface ISubmitForm9Records {
  companyId: string;
  call_records: IForm9Records;
  action?: string;
  signing_party_designation?: string;
  signing_party_name?: string;
  total_company_shares?: string;
}

