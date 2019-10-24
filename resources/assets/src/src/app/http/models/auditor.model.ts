/*--------- ravihansa------------------*/

export interface IAuditorData {
  registeredUser: boolean; // to check applicant already user in roc...

  nic: string;
  passport: string;
  loggedInUser: string; // to get logged in user id, using his email...

  isExistAud: string;
  certificateNo: string;

  id: number;
  title: string;
  firstname: string;
  lastname: string;
  residentialLocalAddress1: string;
  residentialLocalAddress2: string;
  residentialProvince: any;
  residentialDistrict: any;
  residentialCity: any;
  rgnDivision: any;
  residentialPostCode: string;
  businessName?: string;
  businessLocalAddress1?: string;
  businessLocalAddress2?: string;
  businessProvince?: any;
  businessDistrict?: any;
  businessCity?: any;
  gnDivision?: any;
  businessPostCode?: string;
  birthDay: string;
  pQualification: string;
  nationality: string;
  race: string;

  sinFullName: string;
  tamFullName: string;

  sinAd: string;
  tamAd: string;

  whereDomiciled: string;
  dateTakeResidenceInSrilanka: string;
  dateConResidenceInSrilanka: string;
  ownedProperty: string;
  otherFacts: string;

  isUnsoundMind: string;
  isInsolventOrBankrupt: string;
  reason1: string;
  isCompetentCourt: string;
  reason2: string;
  otherDetails: string;
  subClauseQualified: string;
}
export interface IAuditorChangeData {

    registeredUser?: boolean;  // to check applicant already user in roc...

    nic?: string;
    passport?: string;
    loggedInUser?: string;  // to get logged in user id, using his email...

    isExistAud?: string;
    certificateNo?: string;

    id?: number;
    newid?: any;
    title?: string;
    firstname?: string;
    lastname?: string;
    residentialLocalAddress1?: string;
    residentialLocalAddress2?: string;
    residentialProvince?: any;
    residentialDistrict?: any;
    residentialCity?: any;
    rgnDivision?: any;
    residentialPostCode?: string;
    businessName?: string;
    businessLocalAddress1?: string;
    businessLocalAddress2?: string;
    businessProvince?: any;
    businessDistrict?: any;
    businessCity?: any;
    gnDivision?: any;
    businessPostCode?: string;
    birthDay?: string;
    pQualification?: string;
    nationality?: string;
    race?: string;

    sinFullName?: string;
    tamFullName?: string;

    sinAd?: string;
    tamAd?: string;

    whereDomiciled?: string;
    dateTakeResidenceInSrilanka?: string;
    dateConResidenceInSrilanka?: string;
    ownedProperty?: string;
    otherFacts?: string;

    isUnsoundMind?: string;
    isInsolventOrBankrupt?: string;
    reason1?: string;
    isCompetentCourt?: string;
    reason2?: string;
    otherDetails?: string;
    subClauseQualified?: string;

}

export interface IAuditorDataStrikeOff {
  id?: number;
  first_name?: string;
  last_name?: string;
  address1?: string;
  address2?: string;
  city?: string;
  district?: string;
  email?: string;
  mobile?: string;
  postcode?: string;
  certificate_no?: string;
  nic?: string;

}
export interface IAuditorLoadSL {
  nic: string;
}
export interface IAuditorLoadNonSL {
  passport: string;
}
export interface IDeletePdf {
  documentId: number;
}
export interface IAuditorPay {
  audType: string; // firm or individual...
  audId: number; // in firm also use auiId instead of firmId...
}
export interface IAuditorLoadProfile {
  loggedInUser: string;
}
export interface IAuditorFile {
  // to load auditor uploaded pdf and general comments...
  audId: number;
  type: string;
}
export interface IAuditorDataFirm {
  isExistAud: string;
  certificateNo: string;

  firmId: number;
  loggedInUser: String;
  firmName: string;
  businessLocalAddress1: string;
  businessLocalAddress2: string;
  businessProvince: any;
  businessDistrict: any;
  businessCity: any;
  gnDivision: any;
  businessPostCode: string;
  firmPartner: any;
  sinFirmName: string;
  tamFirmName: string;

  sinFirmAd: string;
  tamFirmAd: string;
  qualification: string;
}
export interface IAuditorDataFirmChange {
  isExistAud?: string;
  certificateNo?: string;

  firmId?: number;
  newid?: any;
  loggedInUser?: String;
  firmName?: string;
  businessLocalAddress1?: string;
  businessLocalAddress2?: string;
  businessProvince?: any;
  businessDistrict?: any;
  businessCity?: any;
  gnDivision?: any;
  businessPostCode?: string;
  firmPartner?: any;
  sinFirmName?: string;
  tamFirmName?: string;

  sinFirmAd?: string;
  tamFirmAd?: string;
  qualification?: string;
}
export interface IAuditorID {
  // to get auditor id using firm id...
  firmId: number;
}
export interface IAuditorDataFirmUpdate {
  firmId: number;
  firmName: string;
  businessLocalAddress1: string;
  businessLocalAddress2: string;
  businessProvince: any;
  businessDistrict: any;
  businessCity: any;
  gnDivision: any;
  businessPostCode: string;
}
export interface IAuditorDataLoad {
  // to get auditor data using auditor id...
  audId: number;
}

/*--------- ravihansa------------------*/
