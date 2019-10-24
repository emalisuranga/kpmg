export interface ItenderPublication {
    id: number;
    created_by: number;
    name: string;
}
export interface ItenderPublications {
    publications: Array<ItenderPublication>;
}
export interface IGetPublications {
    loginUser: string;
}

export interface ITender {
 // publisherType: String;
  tenderLimit: String;
  tenderType: String;
  tenderStatusCode?: string;
  tenderNo: String;
  tenderName: String;
  description: String;
 // dateFrom: String;
 // dateTo: String;
  tenderMembers?: ICloseTenderMembers;
  loginUser: String;
  tenderId?: number;
  newPublicationName?: string;
  publicationId?: number;
  tenderAmount?: number;
  appliedCount?: number;

  ministry?: string;
  department?: string;
  division?: string;
  authorized_person_name?: string;
  authorized_person_designation?: string;
  authorized_person_address?: string;
  authorized_person_phone?: string;
  authorized_person_email?: string;
  bid_data_sheet?: string;
  paper_advertisement?: string;
  paper_advertisement_file_name?: string;
  bid_data_ext?: string;
  paper_ad_ext?: string;
}

export interface ICloseTenderMember {
    name: string;
    address: string;
    contactNo: string;
    email: string;
    memberId?: number;
}
export interface ICloseTenderMembers {
    members: Array<ICloseTenderMember>;
}

export interface ICloseTenderItem {
    name: string;
    description: string;
    qty?: number;
    itemId?: number;
    dateFrom: any;
    dateTo: any;
    accepted_amount?: string;
    contract_nature?: string;
    incometax_fileno?: string;
    vat_fileno?: string;
    contract_tax_year3?: number;
    contract_tax_year2?: number;
    contract_tax_year1?: number;
    vat_year3?: number;
    vat_year2?: number;
    vat_year1?: number;
    certificateNo?: string;
    certificate_issued_at?: string;
    certificate_expires_at?: string;
    nic?: string;
    passport?: string;
    itemNo?: string;
    contract_awarded?: string;
    signing_party_designation?: string;
    signing_party_designation_other?: string;
    signing_party_name?: string;

}
export interface ISubmitAwardingSigningParty {
    token: string;
    signing_party_designation: string;
    signing_party_designation_other?: string;
    signing_party_name: string;
}
export interface ICloseTenderItems {
    items: Array<ICloseTenderItem>;
}



export interface ISubmitTenderItems {
    items: ICloseTenderItems;
    tenderId: number;
    action?: string; // publish or draft
}

export interface IGetTender {
    tenderId: number;
    tenderApplicantId?: number;
}

export interface IGetTenders {
    ref_no?: string;
    publisher?: number;
    tenderNamePart?: string;
    tenderNo?: string;
    page?: number;
    publisherDivision?: string;
}

export interface ItenderListItem {
    type: string;
    number: string;
    name: string;
    description: string;
   // from: string;
   //  to: string;
    id: number;
    publicationId: number;
    appliedCount?: number;
    publisherName?: string;
    publishedDate?: string;
    ministry?: string;
}

export interface ItenderListItems {
    items:  Array<ItenderListItem>;
}

export interface Icountry {
    id: number;
    name: string;
    status: number;
}


export interface ItenderPublicationList {
    id: number;
    created_by: number;
    name: string;
    tendersList: Array<ItenderListItems>;
    openList?: boolean;
}
export interface ItenderPublicationLists {
    list: Array <ItenderPublicationList>;
}


export interface IGetUserTenderList {
    loginUser: string;
}

export interface IapplyTender {
    applicant_type: string;
    applicant_sub_type?: string;
    tenderer_sub_type?: string;
    applicant_type_value?: string;
    applicant_type_sub_value?: string;
    is_srilankan?: string;
    apply_from?: string;
    tenderer_apply_from?: string;
    applicant_name: string;
    applicant_address: string;
    applicant_natianality: string;
    appliant_email: string;
    signing_party_name?: string;
    signing_party_designation?: string;
    signing_party_designation_other?: string;
    tenderer_name: string;
    tenderer_address: string;
    tenderer_natianality: string;
    tender_company_reg_no?: string;
    tender_tenderer_company_reg_no?: string;
    tender_directors?: IapplyTenderDirectors;
    tender_shareholders?: IapplyTenderShareHolders;
    tender_members?: IapplyTenderMembers;
    nic?: string;
    passport?: string;
    is_tenderer_srilankan?: string;
    tenderer_nic?: string;
    tenderer_passport?: string;
    id?: number;
    appliant_mobile?: string;

    total_contract_cost?: string;
    value_of_work_completed?: string;
    total_payment_received_for_work_completed?: string;
    nature_of_sub_contract?: string;
    name_of_sub_contract?: string;
    nationality_of_sub_contract?: string;
    total_cost_of_sub_contract?: string;
    amount_of_commission_paid?: string;
    address_of_sub_contract?: string;
    duration_of_sub_contract?: string;
}

export interface IapplyTenderDirector {

     name: string;
     address: string;
     natianality: string;
     natianality_origin: string;
     shares:  number;
     id?: number;
     nic?: string;
     passport?: string;
     passport_issued_country?: string;
     is_srilankan?: string;
     is_shareholder?: boolean;
     valid_director?: boolean;
     shareholderExistForDirector?: boolean;
}

export interface IapplyTenderDirectors {

    directors: Array<IapplyTenderDirector>;
}

export interface IapplyTenderShareHolder {

    name: string;
    address: string;
    natianality: string;
    natianality_origin: string;
    shares:  number;
    id?: number;
    nic?: string;
    passport?: string;
    passport_issued_country?: string;
    is_srilankan?: string;
    valid_shareholder?: boolean;
    is_firm?: boolean;
    firm_reg_no?: string;
}

export interface IapplyTenderShareHolders {

   shareholder: Array<IapplyTenderShareHolder>;
}


export interface IapplyTenderMember {

    name: string;
    address: string;
    natianality: string;
    natianality_origin: string;
    shares:  number;
    id?: number;
    nic?: string;
    passport?: string;
    passport_issued_country?: string;
    is_srilankan?: string;
    valid_member?: boolean;
}

export interface IapplyTenderMembers {

   member: Array<IapplyTenderMember>;
}


export interface IapplyTenderSubmit {
    tenderId: number;
    selectedItems: Array<any>;
    applicantType: string;
    tendererSubType?: string;
    applicantSubType?: string;
    applicntRecord: IapplyTender;
    jv_companies?: IJvCompanies;
 }

 export interface IDownloadDoc {
     name: string;
     file_name_key: string;
     download_link: string;
 }

 export interface IDownloadDocs {
     docs: Array<IDownloadDoc>;
 }

 export interface IJvCompany {
     id?: number;
     name: string;
 }
 export interface IJvCompanies {
     companies: Array<IJvCompany>;
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

export interface IremoveTenderDoc {

    tenderId: number;
    applicantId: number;
    fileTypeId: number;
    itemId?: number;
    memberId?: number;
    companyId?: number;
}

export interface IremoveTenderRenwalDoc {

    tenderId: number;
    applicantId: number;
    fileTypeId: number;
    itemId?: number;
    token?: string;
}

export interface IremoveTenderSpecificDoc {

    tenderId: number;
    docType: string;
}

export interface ItenderApplyPay {
    applicantId: number;
}


export interface IGetResubmitTender {
    tenderId: number;
    token: string;
    tenderApplicantId?: number;
}

export interface ItenderAwordByPublisher {
    tenderId: number;
    awordedList: Object;
    itemId?: string;
    applicationId?: string;
}

export interface IGetAwordingTender {
    tenderId: number;
    token: string;
    tenderApplicantId?: number;
}

export interface IremoveTenderAwardingDoc {

    tenderId: number;
    applicantId: number;
    fileTypeId: number;
    itemId: number;
}

export interface IAwordTender {
    item_token: string;
 }

 export interface IUpdateContractDetails {
    token: string;
    accepted_amount?: string;
    contract_nature?: string;
    incometax_fileno?: string;
    vat_fileno?: string;
    contract_tax_year3?: number;
    contract_tax_year2?: number;
    contract_tax_year1?: number;
    vat_year3?: number;
    vat_year2?: number;
    vat_year1?: number;
    contract_date_from?: string;
    contract_date_to?: string;
    contract_awarded?: string;

}


export interface IGetRenewalTender {
    tenderId: number;
    token: string;
    tenderApplicantId?: number;
}


export interface IupdatePCA7 {
    token: string;
    total_contract_cost?: string;
    value_of_work_completed?: string;
    total_payment_received_for_work_completed?: string;
    nature_of_sub_contract?: string;
    name_of_sub_contract?: string;
    nationality_of_sub_contract?: string;
    total_cost_of_sub_contract?: string;
    amount_of_commission_paid?: string;
    address_of_sub_contract?: string;
    duration_of_sub_contract?: string;
}

export interface IRenwalReRegResubmit {
    token: string;
 }

 export interface ICheckAlreadyAppliedSubmit {
    application_id?: number;
    applied_items: Array<number>;
    tenderer_nic_or_pass?: string;
    tenderer_nic_pass_val?: string;
    applicant_nic_or_pass?: string;
    applicant_nic_pass_val?: string;
    applicant_reg_no?: string;
    tenderer_reg_no?: string;
    applicant_type: string;
    applicant_sub_type?: string;
    tenderer_sub_type?: string;

 }


 export interface IchangeItemCloseDateByPublisher {
     itemId: string;
     dateTo: Date;
 }
 export interface IRemoveOtherDoc {
    file_token: string;
  }

  export interface ICreateNewRenewalReRegRecord {
    item_id: number;
    application_id: number;
    type: string;
    renewal_or_rereg: string;
  }
