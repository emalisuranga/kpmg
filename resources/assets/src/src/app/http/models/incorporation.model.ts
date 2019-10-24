/* ---------------------- Udara Madushan -------------------------*/
import { IDirectors, ISecretories, IShareHolders } from './stakeholder.model';

/* ---------------------- Udara Madushan -------------------------*/
export interface IIncorporationData {
    companyId: string;
    changeId?: string;
}
/* ---------------------- Udara Madushan -------------------------*/
export interface IIncorporationDataStep1Data {
    companyId: string;
    companyType: number;
    address1: string;
    address2: string;
    city: string;
    postcode: string;
    district: string;
    gn_division: string;
    province: string;
    email: string;
    objective1?: string;
    objective2?: string;
    objective3?: string;
    objective4?: string;
    objective5?: string;
    objectiveOther?: string;
    forAddress1?: string;
    forAddress2?: string;
    forCity?: string;
    forProvince?: string;
    forCountry?: string;
    forPostcode?: string;
    resolution_date?: string;
    resolution_inlieu_date?: string;
    meeting_type?: string;
    this_year_annual_return_date?: string;
    last_year_annual_return_date?: string;
    oversease_alteration_address_change_date?: string;
    oversease_alteration_for_address_change_date?: string;
}

export interface IsubmitObjectiveCollection {
    collection: Array<IsubmitObjective>;
}

export interface IsubmitObjective {
  objective1: string;
  objective2: string;
  objective3: string;
  objective4: string;
  objective5: string;
}
/* ---------------------- Udara Madushan -------------------------*/
export interface IIncorporationMembers {
    companyId: string;
    directors: IDirectors;
    secretories: ISecretories;
    shareholders: IShareHolders;
    action?: string;
}

/* ---------------------- Udara Madushan -------------------------*/

export interface IcompanyInfo {
    abbreviation_desc:  string;
    address_id: number;
    created_at: string;
    created_by: number;
    email: string;
    id: number;
    name: string;
    name_si: string;
    name_ta: string;
  //  objective: string;
    objective1: string;
    objective2?: string;
    objective3?: string;
    objective4?: string;
    objective5?: string;
    otherObjective?: string;
    postfix: string;
    status: number;
    type_id: number;
    updated_at: string;
    incorporation_at?: string;
}

export interface IcompanyAddress {
    address1: string;
    address2: string;
    city: string;
    gn_division: string;
    country: string;
    created_at: string;
    district: string;
    id: number;
    postcode: string;
    province: string;
    updated_at: string;

}

export interface IcompanyForAddress {
    address1?: string;
    address2?: string;
    city?: string;
    province?: string;
    district?: string;
    country?: string;
    updated_at?: string;
    created_at?: string;
    postcode?: string;

}

export interface IcompanyType {
    id: number;
    key: string;
    value: string;
    value_si: string;
    value_ta: string;
}
export interface IcompnayTypesItem {
    company_type_id: number;
    id: number;
    postfix: string;
}
export interface IcompanyObjective {
    id: number;
    value: string;
    value_si: string;
    value_ta: string;
}
export interface IloginUserAddress {
    address1: string;
    address2: string;
    city: string;
    country: string;
    created_at: string;
    district: string;
    id: number;
    postcode: string;
    province: string;
    updated_at: string;
}

export interface IloginUser {
    address_id: number;
    created_at: string;
    dob: string;
    email: string;
    first_name: string;
    last_name: string;
    foreign_address_id: number;
    id: number;
    is_srilankan: string;
    mobile: string;
    nic: string;
    occupation: string;
    other_name: string;
    passport_issued_country: string;
    passport_no: string;
    profile_pic: string;
    sex: string;
    status: number;
    telephone: string;
    title: string;
    updated_at: string;

}

export interface IcoreShareGroup {
    group_id: number;
    group_name: string;
}


export interface Icountry {
    id: number;
    name: string;
    status: number;
}

export interface IirdInfo {
    commencementdate: string;
    bac: string;
    preferredlanguage: number;
    preferredmodeofcommunication: number;
    isboireg?: boolean;
    boistartdate?: string;
    boienddate?: string;
    companysalutation?: string;
    purposeofregistration: string;
    otherpurposeofregistration?: string;
    isforiegncompany?: boolean;
    dateofincorporationforeign?: string;
    countryoforigin?: string;
    parentcompanyexists?: boolean;
    localparentcompany?: string;
    parentcompanyreference?: string;
    parentcompanyreferenceid?: string;
    parentcompanyname?: string;
    parentcompanyaddress?: string;
    countryofincorporation?: string;
    dateofincorporationparentcompany?: string;
    addresssecdiv?: number;
    fax?: string;
    email?: string;
    mobile?: string;
    office?: string;
    contactpersonname?: string;
    id?: number;
    taxpayer_identification_number?: string;
    rejected_resion?: string;
    status?: string;

}

export interface IgetCompnies {
    namePart?: string;
    registration_no?: string;
    page?: number;
}
export interface IgetCompany {
    id: number;
    name: string;
    regNo?: string;
    incorporation_at?: string;
    name_si?: string;
    name_ta?: string;
    postfix?: string;
    init_name_of_the_company?: string;
    init_name_of_the_company_id?: number;
    init_name_of_the_company_incorporation_at?: string;
    is_name_change_company_instant?: boolean;
    init_name_of_the_company_postfix?: string;
}

export interface IgetCompanies {
    list: Array<IgetCompany>;
}

export interface IverifyCompany {
    certificateNo: string;
}
export interface IUploadDocs {
    docs: Array<IUploadDoc>;
}
export interface IUploadDoc {
    name: string;
    file_name_key: string;
    doc_comment: string;
    doc_status: string;
    is_required: boolean;
    file_name: string;
    file_type: string;
    member_id?: string;
    member_name?: string;
    dbid: number;
}

export interface IlabourRecord {
    id?: number;
    nature_category: string;
    sub_nature_category?: string;
    total_no_emp?: string;
    total_no_cov_emp?: string;
    total_no_other_than_cov_emp?: string;
    recruited_date?: string;
}

export interface ISaveLabourRecord {
    companyId: string;
    labour: IlabourRecord;
}
