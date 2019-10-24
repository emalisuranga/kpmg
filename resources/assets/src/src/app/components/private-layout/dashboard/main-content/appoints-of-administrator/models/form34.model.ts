
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
  member_type?: string;
  member_id?: string;
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


/**************************** */

export interface IProvince {
  id: number;
  name: string;
}
export interface IDistrict {
  id: number;
  provinceName: string;
  name: string;
}
export interface ICity {
  id: number;
  districtName: string;
  name: string;
}


export interface IAdministrator {
  id?: number;
  showEditPane: number;

  type: string;
  firstname: string;
  lastname: string;
  province: string;
  district: string;
  city: string;
  localAddress1: string;
  localAddress2: string;
  postcode: string;
  passport_issued_country?: string;

  forProvince?: string;
  forCity?: string;
  forAddress1?: string;
  forAddress2?: string;
  forPostcode?: string;

  officeProvince?: string;
  officeDistrict?: string;
  officeCity?: string;
  officeAddress1?: string;
  officeAddress2?: string;
  officePostcode?: string;

  nic: string;
  passport: string;
  country?: string;

  date: string;
  phone: string;
  mobile: string;
  email: string;

  screen1Provinces?: Array<IProvince>;
  screen1Districts?: Array<IDistrict>;
  screen1Cities?: Array<ICity>;
  screen1OfficeProvinces?: Array<IProvince>;
  screen1OfficeDistricts?: Array<IDistrict>;
  screen1OfficeCities?: Array<ICity>;

  court_date?: string;
  appointed_by?: string;
  resolution_date?: string;
  court_name?: string;
  court_case_no?: string;
  court_period?: string;
  court_penalty?: string;
  court_discharged?: string;


}
export interface IAdministrators {
  record: Array<IAdministrator>;
}

export interface ISubmitAdministrators {
  companyId: string;
  records: IAdministrators;
  action?: string;
  admin_office_address1?: string;
  admin_office_address2?: string;
  admin_office_address3?: string;
  court_date?: string;
  appointed_by?: string;
  resolution_date?: string;
  directors?: ISignedDirectors;
  court_name?: string;
  court_case_no?: string;
  court_period?: string;
  court_penalty?: string;
  court_discharged?: string;


}

export interface Icountry {
  id: number;
  name: string;
  status: number;
}


export interface ISignedDirector {
  id: number;
  first_name?: string;
  last_name?: string;
  saved?: boolean;
}
export interface ISignedDirectors {
  director: Array<ISignedDirector>;
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

export interface IcheckAdminCompanies {
  registration_no: string;
}

export class ISelectedAdminCompanies {
  regNumber: string;
  name: string;
  id: string;
}

export class IAddAdminCompanies {
  assignedCompanies: Array<string>;
}
