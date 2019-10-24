/*------------------ravihansa------------------*/

export interface ISecretaryData {

    registeredUser: boolean;  // to check applicant already user in roc...

    nic: string;
    loggedInUser: string;  // to get logged in user id, using his email...

    isExistSec: string;
    certificateNo: string;

    sinFullName: string;
    tamFullName: string;

    id: number;
    title: string;
    firstname: string;
    lastname: string;
    othername: string;
    residentialLocalAddress1: string;
    residentialLocalAddress2: string;
    residentialProvince: any;
    residentialDistrict: any;
    residentialCity: any;
    rgnDivision: any;
    residentialPostCode: string;
    businessName: string;
    businessLocalAddress1: string;
    businessLocalAddress2: string;
    businessProvince: any;
    businessDistrict: any;
    businessCity: any;
    bgnDivision: any;
    businessPostCode: string;
    subClauseQualified: string;

    pQualification: string;
    eQualification: string;
    wExperience: string;
    isUnsoundMind: string;
    isInsolventOrBankrupt: string;
    reason1: string;
    isCompetentCourt: string;
    reason2: string;
    otherDetails: string;
    workHis: any;
}
export interface ISecretaryLoad {
    nic: string;
}
export interface ISecretaryFile {  // to load secretary uploaded pdf and general comments and doc comments...
    secId: number;
    type: string;
}
export interface ISecretaryPay {
    secType: string; // firm or individual...
    secId: number;   // in firm also use secId instead of firmId...
}
export interface ISecretaryLoadProfile {
    loggedInUser: string;
}
export interface ISecretaryWorkHistoryData {
    id: number;
    companyName: string;
    position: string;
    from: string;
    to: string;

}

export interface ISecretaryDataDelisting {
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
export interface ISecretaryDataFirm {
    isExistSec: string;
    certificateNo: string;
    loggedInUser: string;
    id: number;
    name: string;
    sinName: string;
    tamName: string;
    registrationNumber: string;
    businessLocalAddress1: string;
    businessLocalAddress2: string;
    businessProvince: any;
    businessDistrict: any;
    businessCity: any;
    bgnDivision: any;
    businessPostCode: string;
    isUndertakeSecWork: string;

    isUnsoundMind: string;
    isInsolventOrBankrupt: string;
    reason1: string;
    isCompetentCourt: string;
    reason2: string;
    firmPartners: any;
    type: string;
}
export interface ISecretaryDataFirmUpdate {
    firmId: number;
    firmName: string;
    sinName: string;
    tamName: string;
    registrationNumber: string;
    businessLocalAddress1: string;
    businessLocalAddress2: string;
    businessProvince: any;
    businessDistrict: any;
    businessCity: any;
    bgnDivision: any;
    businessPostCode: string;
    isUndertakeSecWork: string;

    isUnsoundMind: string;
    isInsolventOrBankrupt: string;
    reason1: string;
    isCompetentCourt: string;
    reason2: string;
}
export interface ISecretaryFirmPartnerData {
    id: string;
    name: string;
    residentialAddress: string;
    citizenship: string;
    whichQualified: string;
    pQualification: string;

}
export interface IDeletePdf {
    documentId: number;
}
export interface IisSecretaryReg {
    id: any;
}

export interface ISecretaryResubmit {
    secId: number;
}

export interface ISecretaryFirmResubmit {
    firmId: number;
}

/*------------------ravihansa------------------*/
