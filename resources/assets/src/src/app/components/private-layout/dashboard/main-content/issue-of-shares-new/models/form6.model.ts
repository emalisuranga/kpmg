import { IShareHolders } from '../../../../../../http/models/stakeholder.model';
import { IShareHolder } from '../../annual-return/models/annualReturn.model';

export interface IDownloadDoc {
  name: string;
  file_name_key: string;
  download_link: string;
}

export interface IDownloadDocs {
  docs: Array<IDownloadDoc>;
}
export interface ISubmitShareholders {
  companyId: string;
  shareholders: IShareHolders;
  action?: string;
  set_operation: string;
}
export interface ISubmitNewShareholder {
  companyId: string;
  shareholder: IShareHolder;
}

export interface ISignedStakeholder {
  id: number;
  name: string;
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

export interface IissueShare {
  id?: number;
  showEditPane: number;
  share_class: string;
  share_class_other: string;
  no_of_shares?: string;
  date_of_issue?: string;
  is_issue_type_as_cash?: string;
  no_of_shares_as_cash?: string;
  consideration_of_shares_as_cash?: string;
  is_issue_type_as_non_cash?: string;
  no_of_shares_as_non_cash?: string;
  consideration_of_shares_as_non_cash?: string;
  selected_share_class_name?: string;


}
export interface IissueShares {
  share: Array<IissueShare>;
}

export interface ISubmitCallRecords {
  companyId: string;
  call_records: IissueShares;
  action?: string;
  stated_capital?: string;
  total_amount_of_call?: string;
  signing_party_designation?: string;
  singning_party_name?: string;
}
export interface IRemoveShareholder {
  companyId: string;
  shareholder_id: number;
  shareholder_type: string;
}

export interface IRemoveSharClassRecord {
  companyId: string;
  record_id: number;
}


export interface IremoveCallShareDoc {
  companyId: string;
  fileTypeId: number;
}
export interface Iresubmit {
  companyId: string;
}
export interface IRemoveOtherDoc {
  file_token: string;
}
export interface IupdateCourtDetails {
  companyId: string;
  court_status: string;
  court_date: string;
  court_case_no: string;
  court_penalty?: string;
  court_period?: string;
  court_discharged?: string;
}

