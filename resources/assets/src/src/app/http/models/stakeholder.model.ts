/* ---------------------- Udara Madushan -------------------------*/
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
  new_nic?: string;
  passport: string;
  country: string;
  share: number;
  date: string;
  changedate?: string;
  newid?: any;
  isdeleted?: any;
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
  validEdit?: boolean;
  existing_record_id?: number;
  other_relevent?: string;
  listed_on_declaration?: boolean;

}

/* ---------------------- Udara Madushan -------------------------*/
export interface IDirectors {
  directors: Array<IDirector>;
}

/* ---------------------- Udara Madushan -------------------------*/
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
  new_nic?: string;
  passport: string;
  country: string;
  share: number;
  date: string;
  changedate?: string;
  newid?: any;
  isdeleted?: any;
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
  cvNumber?: string;
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
  firm_date_change?: string;
  savedSec?: string;

  firm_info?: IfirmInfo;

  showEditPaneForSec: number;
  validEdit?: boolean;
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
  other_relevent?: string;
}

export interface IfirmInfo {
  registration_no?: string;
  name?: string;
  address?: IformInfoAddress;
  changedate?: string;
  newid?: any;
  isdeleted?: any;
}

export interface IformInfoAddress {
  province?: string;
  district?: string;
  city?: string;
  address1?: string;
  address2?: string;
  postcode?: string;
}

/* ---------------------- Udara Madushan -------------------------*/
export interface ISecretories {
  secs: Array<ISecretory>;
}

/* ---------------------- Udara Madushan -------------------------*/
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
  new_nic?: string;
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
  groupAddedValue?: string;
  shareIssueClass?: string;
  showEditPaneForSh: number;
  shareRow?: IShareRow;
  benifiList?: IShareHolderBenifList;
  validateAddBenif?: boolean;

  screen1Provinces?: Array<IProvince>;
  screen1Districts?: Array<IDistrict>;
  screen1Cities?: Array<ICity>;
  status?: string;

}
/* ---------------------- Udara Madushan -------------------------*/
export interface IShareHolders {
  shs: Array<IShareHolder>;
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


export interface IShareRow {
  type?: string;
  no_of_shares?: number;
  name?: string;
  sharegroupId?: number;
}



/* ---------------------- Udara Madushan -------------------------*/
export interface INICchecker {
  companyId: string;
  nic: string;
  memberType: number;
}

/* ---------------------- Udara Madushan -------------------------*/
export interface IStakeholderDelete {
  userId: number;
  companyId: string;
}

/* ---------------------- Udara Madushan -------------------------*/
export interface ISecForDirDelete {
  userId: number;
  companyId: string;
}
/* ---------------------- Udara Madushan -------------------------*/
export interface IShForDirDelete {
  userId: number;
  companyId: string;
}

/* ---------------------- Udara Madushan -------------------------*/
export interface IShForSecDelete {
  userId?: number;
  firmId?: number;
  companyId: string;
}

/* ---------------------- Udara Madushan -------------------------*/
export interface IFileRemove {
  companyId: number;
  docTypeId: number;
  userId?: number;
  multipleId?: number;
  isFirm?: string;
}
export interface IProvince {
  id: number;
  name: string;
}
export interface IIRDpurpose {
  code: number;
  description: string;
}
export interface IIRDbusinessActCodes {
  id: number;
  code: string;
}
export interface IIRDCompanySalutation {
  code: number;
  description: string;
}
export interface ISecretoryDivision {
  id: number;
  description: string;
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

export interface IObjective {
  id: number;
  code: string;
  name: string;
  parent_id: number;
}

export interface ILabourBusinessCode {
  id: number;
  name: string;
  parent_id?: number;
}


export interface IObjectiveRow {
  objective1: string;
  objective2: string;
  objective3: string;
  objective4: string;
  objective5: string;
  level1Objectives?:  Array<IObjective>;
  level2Objectives?:  Array<IObjective>;
  level3Objectives?:  Array<IObjective>;
  level4Objectives?:  Array<IObjective>;
  level5Objectives?:  Array<IObjective>;

}

export interface IObjectiveCollection {
  collection: Array<IObjectiveRow>;
}


