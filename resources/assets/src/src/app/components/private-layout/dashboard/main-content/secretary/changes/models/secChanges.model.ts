
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

  export interface IgetData {
    secretaryId: string;
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

export interface ISecIndividualAlterRecord {
  id?: number;
  old_address_address1: string;
  old_address_address2?: string;
  old_address_city: string;
  old_address_district: string;
  old_address_province: string;
  old_address_postcode: string;

  new_address_address1?: string;
  new_address_address2?: string;
  new_address_city?: string;
  new_address_district?: string;
  new_address_province?: string;
  new_address_postcode: string;

  old_first_name: string;
  old_last_name: string;
  new_first_name?: string;
  new_last_name?: string;

  old_name_si?: string;
  old_name_ta?: string;
  new_name_si?: string;
  new_name_ta?: string;

  old_email_address: string;
  new_email_address?: string;

  old_mobile_no: string;
  old_tel_no?: string;
  new_mobile_no?: string;
  new_tel_no?: string;

  certificate_no?: string;

}


export interface ISecFirmAlterRecord {
  id?: number;
  old_address_address1: string;
  old_address_address2?: string;
  old_address_city: string;
  old_address_district: string;
  old_address_province: string;
  old_address_postcode: string;

  new_address_address1?: string;
  new_address_address2?: string;
  new_address_city?: string;
  new_address_district?: string;
  new_address_province?: string;
  new_address_postcode: string;

  old_name: string;
  new_name?: string;
  old_name_si?: string;
  old_name_ta?: string;
  new_name_si?: string;
  new_name_ta?: string;

  old_email_address: string;
  new_email_address?: string;

  old_mobile_no: string;
  old_tel_no?: string;
  new_mobile_no?: string;
  new_tel_no?: string;
  certificate_no?: string;

}

export interface ISecFirmPartner {
  id?: number;
  name: string;
  nic: string;
  address: string;
  citizenship?: string;
  professional_qualifications?: string;
  which_qualified?: string;
  existing_patner: boolean;
  showEditPaneForPartner: number;
  registeredSec?: boolean;
}

export interface IUpdateSecFirmPartner {
  partner: ISecFirmPartner;
  secretaryId: string;
}
export interface IRemoveecFirmPartner {
  partner: ISecFirmPartner;
  secretaryId: string;
}
export interface IADDSecFirmPartner {
  partner: ISecFirmPartner;
  secretaryId: string;
}

export interface ISecFirmPartners {
  partner: Array<ISecFirmPartner>;
}


export interface IUpdateAlterType {
  secretaryId: string;
  alter_type: Array<string>;
}
export interface IUpdateName {
  secretaryId: string;
  new_first_name?: string;
  new_last_name?: string;
  new_name_si?: string;
  new_name_ta?: string;
}

export interface IUpdateFirmName {
  secretaryId: string;
  new_name?: string;
  new_name_si?: string;
  new_name_ta?: string;
}

export interface IUpdateAddress {
  secretaryId: string;
  new_address_address1: string;
  new_address_address2?: string;
  new_address_city: string;
  new_address_district: string;
  new_address_province: string;
  new_address_postcode: string;
}

export interface IUpdateEmail {
  secretaryId: string;
  new_email_address: string;
}

export interface IUpdateContact {
  secretaryId: string;
  new_tel_no?: string;
  new_mobile_no?: string;
}


export interface IUploadedDocs {
  docs: Array<IUploadedDoc>;
}

export interface IRemoveOtherDoc {
  file_token: string;
}

export interface IremoveAnnualDoc {
  companyId: string;
  fileTypeId: number;
  member_type?: string;
  member_id?: string;
}

export interface Iresubmit {
  secretaryId: string;
}

export interface IAlterOptions {
  key: string;
  value: string;
  isSelected: boolean;
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

export interface ICheckNICPartner {
  nic: string;
}

export interface  ISubmitRequest{
  secretaryId: string;
}
export interface  IResubmitRequest{
  secretaryId: string;
}
