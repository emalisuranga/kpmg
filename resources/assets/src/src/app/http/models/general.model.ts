export interface ITitle {
  id: number;
  value: string;
}

export class IProvince {
  id: number;
  description_en: string;
  description_si: string;
  description_ta: string;
}

export class IDistrict {
  id: number;
  province_code: string;
  description_en: string;
  description_si: string;
  description_ta: string;
}

export class ICity {
  id: number;
  district_code: string;
  description_en: string;
  description_si: string;
  description_ta: string;
}

export class IGNdivision {
  id: number;
  city_code: string;
  description_en: string;
  description_si: string;
  description_ta: string;
}


export class IPayment {
  id: number;
  key: string;
  value: number;
  value_si: string;
  value_ta: string;
}


export class ICountry {
  id: string;
  name: string;
}
export class IAdminCompanies {
  id: string;
  name: string;
}
export class ISelectedAdminCompanies {
  regNumber: string;
  name: string;
  id: string;
}

export class ICheckAdminCompany {
  registration_no: string;
}
export class IMember {
  id: string;
  type: string;
  title: string;
  first_name: string;
  last_name: string;
  designation: string;
}


export interface ICapital {
  id: string;
  name: string;
  type: string;
  jobId: string;
  share_capital_amount: string;
  reduction_amount: string;
  reduction_capital_amount: string;
  created_at: string;
  request_id: string;
  value: string;
  key: string;
  comments?: Array<any>;
  doc_comments?:  Array<any>;
}
