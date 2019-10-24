export interface IDirector {
    type: string;
    title: string;
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
    nic: string;
    passport: string;
    country: string;
    share: number;
    date: string;
    occupation: string;
    phone: string;
    mobile: string;
    email: string;
    id: number;
    isSec?: boolean; // is secretory
    secRegDate?: string;
    isSecEdit?: boolean;
    isShareholder?: boolean;
    isShareholderEdit?: boolean;
    shareType?: string;
    noOfSingleShares?: number;
    coreGroupSelected?: number;
    coreShareGroupName?: string;
    coreShareValue?: number;

    shareTypeEdit?: string;
    noOfSingleSharesEdit?: number;
    coreGroupSelectedEdit?: number;
    coreShareGroupNameEdit?: string;
    coreShareValueEdit?: number;
    showEditPaneForDirector: number;
    directors_as_sec?: number;
    directors_as_sh?: number;
    screen1Provinces?: Array<IProvince>;
    screen1Districts?: Array<IDistrict>;
    screen1Cities?: Array<ICity>;
    can_director_as_sec?: boolean;
    existing_record_id?: number;

  }
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

  export interface IGnDivision {
    id: number;
    cityName: string;
    name: string;
  }
  export interface IfirmInfo {
    registration_no?: string;
    name?: string;
    address?: IformInfoAddress;
  }
  export interface IformInfoAddress {
    province?: string;
    district?: string;
    city?: string;
    address1?: string;
    address2?: string;
    postcode?: string;
  }
  export interface IShareHolderBenif {
    title: string;
   firstname: string;
   lastname: string;
   province: string;
   district: string;
   city: string;
   localAddress1: string;
   localAddress2: string;
   postcode: string;
   nic: string;
   passport: string;
   country: string;
   date: string;
   occupation: string;
   phone: string;
   mobile: string;
   email: string;
   id?: number;
   type: string;
   screen1Provinces?: Array<IProvince>;
   screen1Districts?: Array<IDistrict>;
   screen1Cities?: Array<ICity>;
 }

 export interface IShareHolderBenifList {
   ben: Array<IShareHolderBenif>;
 }
  export interface ISecretory {
    id: number;
    type: string;
    title: string;
    firstname: string;
    lastname: string;
    province: string;
    district: string;
    city: string;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    passport_issued_country?: string;
    nic: string;
    passport: string;
    country: string;
    share: number;
    date: string;
    occupation: string;
    isReg: boolean;
    regDate: string;
    phone: string;
    mobile: string;
    email: string;
    isShareholder?: boolean;
    isShareholderEdit?: boolean;
    sec_as_sh?: number;
    sec_sh_comes_from_director?: boolean;
    shareType?: string; // single or core
    noOfSingleShares?: number;
    coreGroupSelected?: number;
    coreShareGroupName?: string;
    coreShareValue?: number;
    shareTypeEdit?: string; // single or core
    noOfSingleSharesEdit?: number;
    coreGroupSelectedEdit?: number;
    coreShareGroupNameEdit?: string;
    coreShareValueEdit?: number;
    secType?: string; // natural or firm
    secCompanyFirmId?: string;
    pvNumber?: string;
    firm_name?: string;
    firm_country?: string;
    firm_province?: string;
    firm_district?: string;
    firm_city?: string;
    firm_localAddress1?: string;
    firm_localAddress2?: string;
    firm_postcode?: string;
    firm_email?: string;
    firm_mobile?: string;
    firm_phone?: string;
    firm_date?: string;
    savedSec?: string;
    firm_info?: IfirmInfo;
    showEditPaneForSec: number;
    forAddress1?: string;
    forAddress2?: string;
    forAddress3?: string;
    forProvince?: string;
    forCity?: string;
    forPostcode?: string;
    screen1Provinces?: Array<IProvince>;
    screen1Districts?: Array<IDistrict>;
    screen1Cities?: Array<ICity>;
    benifOwnerType?: string;
    secBenifList?: IShareHolderBenifList;
    validateSecShBenifInEdit ?: boolean;
    existing_record_id?: number;
  }
  export interface IShareRow {
    type?: string;
    no_of_shares?: number;
    name?: string;
    sharegroupId?: number;
  }
  export interface IShareHolder {
    type: string;
    title: string;
    firstname: string;
    lastname: string;
    province: string;
    district: string;
    city: string;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    forProvince?: string;
    forCity?: string;
    forAddress1?: string;
    forAddress2?: string;
    forPostcode?: string;
    nic: string;
    passport: string;
    country: string;
    passport_issued_country?: string;
    share: number;
    date: string;
    occupation: string;
    phone: string;
    mobile: string;
    email: string;
    id: number;
    shareholderType?: string;
    benifOwnerType?: string;
    shareholderFirmCompanyisForiegn?: boolean;
    pvNumber?: string;
    firm_name?: string;
    firm_province?: string;
    firm_district?: string;
    firm_city?: string;
    firm_localAddress1?: string;
    firm_localAddress2?: string;
    firm_postcode?: string;
    firm_email?: string;
    firm_mobile?: string;
    firm_phone?: string;
    firm_date?: string;
    shareType: string;
    noOfShares: number;
    coreGroupSelected?: number;
    coreShareGroupName?: string;
    noOfSharesGroup?: number;
    showEditPaneForSh: number;
    shareRow?: IShareRow;
    benifiList?: IShareHolderBenifList;
    validateAddBenif?: boolean;
    screen1Provinces?: Array<IProvince>;
    screen1Districts?: Array<IDistrict>;
    screen1Cities?: Array<ICity>;
  }

  export interface ISecretories {
    secs: Array<ISecretory>;
  }
  export interface IDirectors {
    directors: Array<IDirector>;
  }
  export interface IShareHolders {
    shs: Array<IShareHolder>;
  }

export interface ISubmitDirectors {
    companyId: string;
    directors: IDirectors;
    action?: string;
}

export interface ISubmitSecretories {
    companyId: string;
    secretories: ISecretories;
    action?: string;
}

export interface ISubmitShareholders {
  companyId: string;
  shareholders: IShareHolders;
  action?: string;
  set_operation: string;
}

export interface IShareRegister {
  id?: number;
  description: string;
  address_id?: number;
  foreign_address_id?: number;
  address_type: string;
  showEditPane: number;
  screen1Provinces?: Array<IProvince>;
  screen1Districts?: Array<IDistrict>;
  screen1Cities?: Array<ICity>;
  province: string;
  district: string;
  city: string;
  localAddress1: string;
  localAddress2?: string;
  postcode: string;
  forAddress1?: string;
  forAddress2?: string;
  forProvince?: string;
  forCity?: string;
  forPostcode?: string;
  country?: string;
}
export interface IShareRegisters {
  sr: Array<IShareRegister>;
}
export interface ISubmitShareRegisters {
  companyId: string;
  share_registers: IShareRegisters;
  action?: string;
}

export interface IAnnualRecord {
  id?: number;
  description: string;
  address_id?: number;
  foreign_address_id?: number;
  address_type: string;
  showEditPane: number;
  screen1Provinces?: Array<IProvince>;
  screen1Districts?: Array<IDistrict>;
  screen1Cities?: Array<ICity>;
  province: string;
  district: string;
  city: string;
  localAddress1: string;
  localAddress2?: string;
  postcode: string;
  forAddress1?: string;
  forAddress2?: string;
  forProvince?: string;
  forCity?: string;
  forPostcode?: string;
  country?: string;
}
export interface IAnnualRecords {
  rec: Array<IAnnualRecord>;
}
export interface ISubmitAnnualRecords {
  companyId: string;
  annual_records: IAnnualRecords;
  action?: string;
}

export interface IAnnualAuditor {
  id?: number;
  first_name: string;
  last_name: string;
  address_id?: number;
  foreign_address_id?: number;
  address_type: string;
  showEditPane: number;
  screen1Provinces?: Array<IProvince>;
  screen1Districts?: Array<IDistrict>;
  screen1Cities?: Array<ICity>;
  province: string;
  district: string;
  city: string;
  localAddress1: string;
  localAddress2?: string;
  postcode: string;
  forAddress1?: string;
  forAddress2?: string;
  forProvince?: string;
  forCity?: string;
  forPostcode?: string;
  country?: string;
}
export interface IAnnualAuditors {
  member: Array<IAnnualAuditor>;
}
export interface ISubmitAnnualAuditors {
  companyId: string;
  auditor_records: IAnnualAuditors;
  action?: string;
}

export interface IAnnualCharge {
  id?: number;
  name: string;
  date: string;
  description: string;
  amount: string;
  address_id?: number;
  foreign_address_id?: number;
  address_type: string;
  showEditPane: number;
  screen1Provinces?: Array<IProvince>;
  screen1Districts?: Array<IDistrict>;
  screen1Cities?: Array<ICity>;
  province: string;
  district: string;
  city: string;
  localAddress1: string;
  localAddress2?: string;
  postcode: string;
  forAddress1?: string;
  forAddress2?: string;
  forProvince?: string;
  forCity?: string;
  forPostcode?: string;
  country?: string;
}
export interface IAnnualCharges {
  ch: Array<IAnnualCharge>;
}

export interface IShareRecord {
  id?: number;
  share_class: string;
  no_of_shares: string;
  issue_type_as_cash: boolean;
  issue_type_as_non_cash: boolean;
  share_value?: string;
  share_consideration?: string;
  share_consideration_value_paid?: string;
  shares_issued_for_cash?: string;
  shares_issued_for_non_cash?: string;
  shares_called_on?: string;
  showEditPane: number;
}
export interface IShareRecords {
  share: Array<IShareRecord>;
}
export interface ISubmitShareRecords {
  companyId: string;
  share_records: IShareRecords;
  amount_calls_recieved?: string;
  amount_calls_unpaid?: string;
  amount_calls_forfeited?: string;
  amount_calls_purchased?: string;
  amount_calls_redeemed?: string;
  action?: string;
}


export interface ISubmitAnnualCharges {
  companyId: string;
  charges_records: IAnnualCharges;
  action?: string;
}
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

export interface ICompanyOldRecord {
  oldName?: string;
  old_postfix?: string;
  old_type_id?: number;
}

export interface Iresubmit {
  companyId: string;
}

export interface IbulkShareholderBulkInfo {
  screen1Provinces?: Array<IProvince>;
  screen1Districts?: Array<IDistrict>;
  screen1Cities?: Array<ICity>;
  province: string;
  district: string;
  city: string;
  country: string;
  title: string;

}
export interface IAnnualReturnDatesInfo {
  incorporation_date?: string;
  is_incorporation_date_as_last_annual_return?: boolean;
  last_year_annual_return_date?: string;
  this_year_annual_return_date?: string;
}
export interface IremoveUserInfo {
  reason: string;
  effective_date: string;
  other_option_reason?: string;
}
export interface IRemoveExistingDirector {
  director_id ?: number;
  company_id ?: string;
  reason_info?: IremoveUserInfo;

}
export interface IUpdateExistingDirector {
  director_id ?: number;
  company_id ?: string;
  director?: IDirector;
  type?: string;
}

export interface IAddNewDirector {
  company_id ?: string;
  director?: IDirector;
}
export interface IRemoveExistingSec {
  sec_id ?: number;
  company_id ?: string;
  type?: string;
  reason_info?: IremoveUserInfo;
}
export interface IUpdateExistingSec {
  sec_id ?: number;
  company_id ?: string;
  sec?: ISecretory;
  type?: string;
}

export interface IAddNewSec {
  company_id ?: string;
  director?: ISecretory;
}
export interface IUpdateOtherDocDate {
  company_id ?: string;
  charter_date?: string;
  memorandum_date?: string;
  article_date?: string;
  statute_date?: string;
}

/*export interface IUpdateOtherDocDate {
  company_id ?: string;
  charter_date?: string;
  memorandum_date?: string;
  article_date?: string;
  statute_date?: string;
}*/
export interface IAlterOptions {
  key: string;
  value: string;
  isSelected: boolean;
}
export interface IRemoveOtherDoc {
  file_token: string;
}
export interface IUpdateAlterType {
  company_id: string;
  alter_type: Array<string>;
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


